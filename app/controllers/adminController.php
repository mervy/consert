<?php

class Admin extends Controller {

    private $auth, $db, $nivel, $custo, $salt;

    public function init() {
        $this->auth = new authHelper();
        $this->auth->setLoginControllerAction('admin', 'login')
                ->checkLogin('redirect');

        $this->db = new adminModel();
    }

    public function showPage($page, $datas = null) {
        $this->view('templates/header');
        $this->view($page, $datas);
        $this->view('templates/footer');
    }

    public function Index_action() {
        $redirector = new RedirectorHelper();
        $redirector->goToAction('home');
    }

    public function login() {
        $this->custo = '13'; //2^13 custo de processamento
        $this->salt = hash('sha512', "O mundo é lindo");

        if ($this->getParam('acao')) {
            $user = (preg_match("/^[a-z]+?/i", $_POST['login'])) ? $_POST['login'] : "Usuário incorreto!!!"; // +? ->quantas quiser; /i ->case-insensitive
            $this->auth->setTableName('usuarios')
                    ->setUserColumn('login')
                    ->setPassColumn('senha')
                    ->setUser($user)
                    ->setPass(crypt($_POST['senha'], '$2a$' . $this->custo . '$' . $this->salt . '$'))
                    ->setLoginControllerAction('admin', 'index')
                    ->login();
        }

        self::showPage('index');
    }

    public function logout() {
        $this->auth->setLogoutControllerAction('index', 'index')
                ->logout();
    }

    public function verificaNivel(Array $permission) {
        $nivel = $_SESSION['userData']['nivel'];

        switch ($nivel) {
            case "1";
                if (in_array($nivel, $permission))
                    echo "<script type=\"text/JavaScript\">
      alert(\"Você só pode navegar pelo sistema!!!\")
      window.location.href = '/admin/home';
      </script>";
                break;
            case "2":
                if (in_array($nivel, $permission))
                    echo "<script type=\"text/JavaScript\">
      alert(\"Só usuários do nível '3' para fazer isso!!!\")
      window.location.href = '/admin/home';
      </script>";
        }
    }

    public function home() {
        $db = $this->db;
        $db->_tabela = "funcionarios";
        $sql = $db->dadosAtuais("afastamento = 'Sim'");
        $datas['sql'] = $sql;
        $db2 = $this->db;
        $db2->_tabela = "ordem_de_servicos";
        $ordens = $db->listaOrdemDeServicos('10');
        $datas['ordens'] = $ordens;

        self::showPage('home', $datas);
    }

    public function cadastros() {
        $this->verificaNivel(array('1')); //Bloqueia o acesso para nivel 1
        $secao = $this->getParam('secao');
        $datas['secao'] = $secao;

        self::showPage('indexGerenciar', $datas);
    }

    public function ordens() {
        $this->verificaNivel(array('1')); //Bloqueia o acesso para nivel 1
        $redirect = new RedirectorHelper();

        //Lógica para filiais
        $filialParam = $this->getParam('filial');
        $db1 = $this->db;
        $db1->_tabela = "filiais";
        $filiais = $db1->listaDados();
        if ($filialParam) {
            $filiais_id = $db1->dadosAtuais("filial='$filialParam'");
            $datas['filiais_id'] = $filiais_id[0];
        }
        $datas['filial'] = $filialParam;
        $datas['filiais'] = $filiais;

        //Lógica para setores
        $setorParam = $this->getParam('setor');
        $db2 = $this->db;
        $db2->_tabela = "setores";
        $setores = $db2->listaDados();
        if ($setorParam) {
            $setores_id = $db1->dadosAtuais("setor='$setorParam'");
            $datas['setores_id'] = $setores_id[0];
        }
        $datas['setor'] = $setorParam;
        $datas['setores'] = $setores;

        //Lógica para suportes
        $suporteParam = $this->getParam('suporte');
        $db2 = $this->db;
        $db2->_tabela = "suportes";
        $suportes = $db2->listaDados();
        if ($suporteParam) {
            $suportes_id = $db1->dadosAtuais("area='$suporteParam'");
            $datas['suportes_id'] = $suportes_id[0];
        }
        $datas['suporte'] = $suporteParam;
        $datas['suportes'] = $suportes;

        //Lógica para tipo de serviço a ser realizado    
        $servicoParam = $this->getParam('servico');
        $db3 = $this->db;
        $db3->_tabela = "servicos";
        $servicos = $db3->listaDados();
        if ($servicoParam) {
            $servicos_id = $db1->dadosAtuais("servico='$servicoParam'");
            $datas['servicos_id'] = $servicos_id[0];
        }
        $datas['servico'] = $servicoParam;
        $datas['servicos'] = $servicos;

        //Lógica para incremento do protocolo
        $db4 = $this->db;
        $db4->_tabela = "ordem_de_servicos";
        $ordem = $db4->mostraUltimoId();
        $datas['ordem'] = isset($ordem[0]) ? $ordem[0] : "0";
        ; //para usar como $view_ordem['id'] e incrementar
        if ($suporteParam && $filialParam) {
            $ultimo_funcionario = $db4->setQueryLastFunc($suportes_id[0]['id'], $filiais_id[0]['id']);
            $datas['ultimo_funcionario'] = @$ultimo_funcionario[0];
        }

        //Lógica para listar os funcionarios
        $db5 = $this->db;
        $db5->_tabela = "funcionarios";
        $func = $db5->dadosAtuais("find_in_set('" . $suporteParam . "', suportes) AND find_in_set('" . @$filiais_id[0]['id'] . "', filiais) AND afastamento='Não'");
        $datas['funcionarios'] = $func;

        if ($this->getParam('do')) {
            //Para exibir o nome, ao invés da id na ordem de serviço
            $nome_funcionario = $db5->dadosAtuais("id='" . $_POST['funcionarios_id'] . "'");
            $datas['nome_funcionario'] = isset($nome_funcionario[0]) ? $nome_funcionario[0] : "";

            $db = $this->db;
            $db->_tabela = "ordem_de_servicos";

            //Para evitar duplicar no banco
            $prot = $ordem[0]['id'];
            if ($prot != substr($_POST['ordem'], 0, -5)) { //Teste de inserção duplicada no banco               
                if ($_POST['funcionarios_id'] != @$ultimo_funcionario[0]['id_func'] && $_POST['funcionarios_id'] != "Selecione o funcionário") {//Teste de oficial repetido
                    $data_suporte = new DateTime($_POST['data_suporte']);
                    $db->emitirOrdem($_POST['resumo'], $data_suporte->format('Y-m-d'), $_POST['suportes_id'], $_POST['setores_id'], $_POST['servicos_id'], $_POST['funcionarios_id'], $_POST['filiais_id']);
                    $this->view('ordens_temp', $datas);
                } else {//Se há funcionário repetido
                    echo "<script type=\"text/JavaScript\">
                          alert(\"Funcionário não selecionado ou já selecionado com esses parâmetros!\")
                          window.history.back();
                          </script>";
                }
            } else {//Redireciona se ocorrer reload do protocolo gerado
                $redirect->goToControllerAction('admin', 'ordens');
            }
        } else {
            self::showPage('ordens', $datas);
        }
    }

    public function gerenciar() {
        $secao = $this->getParam('secao');
        $db = $this->db;
        $db->_tabela = $secao;
        $sql = $db->listaDados();
        $datas['sql'] = $sql;
        $datas['secao'] = $secao;

        if ($this->getParam('secao') == "usuarios") {
            $this->verificaNivel(array('2')); //Bloqueia o acesso para nivel 2       
        }

        self::showPage('adminGerenciar', $datas);
    }

    public function editar() {
        $redirect = new RedirectorHelper();
        $secao = $this->getParam('secao');
        $datas['secao'] = $secao;

        $id = $this->getParam('id');
        $datas['id'] = $id;

        $db = $this->db;
        $db->_tabela = $secao;

        $sql = $db->dadosAtuais("id=" . $id);
        $datas['dados'] = $sql[0];

        if ($secao == 'usuarios') {
            $this->verificaNivel(array('2')); //Bloqueia o acesso para nivel 2
        }

        if ($this->getParam('do')) {
            switch ($secao) {
                case "setores":
                    $db->alteraDadoSetores($id, $_POST['setor']);
                    $redirect->goToControllerAction('admin', 'cadastros');
                    break;
                case "filiais":
                    $db->alteraDadoFiliais($id, $_POST['filial'], $_POST['cnpj'], $_POST['endereco'], $_POST['telefone']);
                    $redirect->goToControllerAction('admin', 'cadastros');
                    break;
                case "suportes":
                    $db->alteraDadoSuportes($id, $_POST['area']);
                    $redirect->goToControllerAction('admin', 'cadastros');
                    break;
                case "servicos":
                    $db->alteraDadoServicos($id, $_POST['servico']);
                    $redirect->goToControllerAction('admin', 'cadastros');
                    break;
                case "funcionarios":
                    $dataAfast = new DateTime(str_replace('/', '-', $_POST['data_afastamento']));
                    $dataRet = new DateTime(str_replace('/', '-', $_POST['data_retorno']));

                    $db->alteraDadoFuncionarios($id, $_POST['nome'], $_POST['email'], $_POST['funcao'], $_POST['areas'], $_POST['filiais'], $_POST['afastamento'], $_POST['motivo_afastamento'], $dataAfast->format('Y-m-d'), $dataRet->format('Y-m-d'));
                    $redirect->goToControllerAction('admin', 'cadastros');
                    break;
                case "usuarios":
                    $this->verificaNivel(array('2')); //Bloqueia o acesso para nivel 2     
                    $this->custo = '13'; //2^13 custo de processamento
                    $this->salt = hash('sha512', "O mundo é lindo");
                    $senha = crypt($_POST['senha'], '$2a$' . $this->custo . '$' . $this->salt . '$');
                    $db->alteraDadoUsuarios($id, $_POST['login'], $_POST['nome'], $_POST['funcao'], $senha, $_POST['nivel'], $_POST['data'], $_POST['status']);
                    $redirect->goToControllerAction('admin', 'cadastros');
                    break;
            }
        }

        self::showPage('adminEditar', $datas);
    }

    public function cadastrar() {
        $redirect = new RedirectorHelper();
        $secao = $this->getParam('secao');
        $datas['secao'] = $secao;

        $db = $this->db;
        $db->_tabela = $secao;

        if ($secao == 'usuarios') {
            $this->verificaNivel(array('2')); //Bloqueia o acesso para nivel 2
        }

        if ($this->getParam('do')) {
            switch ($secao) {
                case "setores":
                    $db->insereDadosSetores($_POST['setor']);
                    $redirect->goToControllerAction('admin', 'cadastros');
                    break;
                case "filiais":
                    $db->insereDadosFiliais($_POST['filial'], $_POST['cnpj'], $_POST['endereco'], $_POST['telefone']);
                    $redirect->goToControllerAction('admin', 'cadastros');
                    break;
                case "suportes":
                    $db->insereDadosSuportes($_POST['area']);
                    $redirect->goToControllerAction('admin', 'cadastros');
                    break;
                case "servicos":
                    $db->insereDadosServicos($_POST['servico']);
                    $redirect->goToControllerAction('admin', 'cadastros');
                    break;
                case "funcionarios":
                    $dataAfast = new DateTime(str_replace('/', '-', $_POST['data_afastamento']));
                    $dataRet = new DateTime(str_replace('/', '-', $_POST['data_retorno']));
                    $db->insereDadosFuncionarios($_POST['nome'], $_POST['email'], $_POST['funcao'], $_POST['areas'], $_POST['filiais'], $_POST['afastamento'], $_POST['motivo_afastamento'], $dataAfast->format('Y-m-d'), $dataRet->format('Y-m-d'));
                    $redirect->goToControllerAction('admin', 'cadastros');
                    break;
                case "usuarios":
                    $this->verificaNivel(array('2')); //Bloqueia o acesso para nivel 2        
                    $this->custo = '13'; //2^13 custo de processamento
                    $this->salt = hash('sha512', "O mundo é lindo");
                    $senha = crypt($_POST['senha'], '$2a$' . $this->custo . '$' . $this->salt . '$');
                    $db->insereDadosUsuarios($_POST['login'], $_POST['nome'], $_POST['funcao'], $senha, $_POST['nivel'], $_POST['data'], $_POST['status']);
                    $redirect->goToControllerAction('admin', 'cadastros');
                    break;
            }
        }

        self::showPage('adminCadastrar', $datas);
    }

    public function deletar() {
        $this->verificaNivel(array('1', '2')); //Bloqueia o acesso para nivel 2
        $id = $this->getParam('id');
        $secao = $this->getParam('secao');

        $db = $this->db;
        $db->_tabela = $secao;
        $db->deletaDado($id);

        $redirector = new RedirectorHelper();
        $redirector->setUrlParameter('secao', $secao)
                ->goToAction('gerenciar');
    }

    public function relatorios() {
        $tipo = $this->getParam('tipo');
        @$pordia = new DateTime(str_replace('/', '-', $_POST['porDia']));
        @$porintInicial = new DateTime(str_replace('/', '-', $_POST['dataInicial']));
        @$porintFinal = new DateTime(str_replace('/', '-', $_POST['dataFinal']));
        @$pormesmes = $_POST['mes'];
        @$pormesano = $_POST['mesAno'];
        @$porano = $_POST['ano'];
        $mes = $this->getParam('mes');
        $ano = $this->getParam('ano');
        $relatorio = $this->getParam('relatorio');

        $db = $this->db;
        $db->_tabela = 'ordem_de_servicos';

        if ($this->getParam('tipo')) {
            switch ($tipo) {
                case 'pordia':
                    echo $pordia->format('Y-m-d');
                    $sql = $db->setRelatorios("data_suporte = '" . $pordia->format('Y-m-d') . "'");
                    break;
                case 'porintervalodedatas':
                    $sql = $db->setRelatorios("data_suporte >= '" . $porintInicial->format('Y-m-d') . "' AND 
                                             data_suporte <= '" . $porintFinal->format('Y-m-d') . "'");
                    break;
                case 'pormes':
                    $sql = $db->setRelatorios("extract(month FROM data_suporte) = '" . $pormesmes . "' AND 
                        extract(YEAR FROM data_suporte) = '" . $pormesano . "'");
                    break;
                case 'porano':
                    $sql = $db->setRelatorios("extract(YEAR FROM data_suporte) = '" . $porano . "'");
                    break;
            }
        }

        $datas['tipo'] = $tipo;
        $datas['sql'] = @$sql;

        self::showPage('relatorios', $datas);
    }

    public function livro_controle() {
        $tipo = $this->getParam('tipo');
        @$pordia = new DateTime(str_replace('/', '-', $_POST['porDia']));
        @$porintInicial = new DateTime(str_replace('/', '-', $_POST['dataInicial']));
        @$porintFinal = new DateTime(str_replace('/', '-', $_POST['dataFinal']));
        @$pormesmes = $_POST['mes'];
        @$pormesano = $_POST['mesAno'];
        @$porano = $_POST['ano'];
        $mes = $this->getParam('mes');
        $ano = $this->getParam('ano');

        $db = $this->db;
        $db->_tabela = 'ordem_de_servicos';

        if ($this->getParam('tipo')) {
            switch ($tipo) {
                case 'pordia':
                    $sql = $db->setRelatorios("data_suporte = '" . $pordia->format('Y-m-d') . "'");
                    break;
                case 'porintervalodedatas':
                    $sql = $db->setRelatorios("data_suporte >= '" . $porintInicial->format('Y-m-d') . "' AND 
                                             data_suporte <= '" . $porintFinal->format('Y-m-d') . "'");
                    break;
                case 'pormes':
                    $sql = $db->setRelatorios("extract(month FROM data_suporte) = '" . $pormesmes . "' AND 
                        extract(YEAR FROM data_suporte) = '" . $pormesano . "'");
                    break;
                case 'porano':
                    $sql = $db->setRelatorios("extract(YEAR FROM data_suporte) = '" . $porano . "'");
                    break;
            }
        }

        $datas['tipo'] = $tipo;
        $datas['sql'] = isset($sql) ? $sql : NULL;

        self::showPage('livro_controle', $datas);
    }

    public function livro_ordens() {
        $db = $this->db;
        $db->_tabela = 'ordem_de_servicos';

        $sql = $db->listaTudo();
        $datas['sql'] = isset($sql) ? $sql : NULL;

        self::showPage('livro_ordens', $datas);
    }

    public function livro_baixas() {
        $redirect = new RedirectorHelper();
        $id = $this->getParam('id');
        @$busca = $_POST['busca'];
        $db = $this->db;
        $db->_tabela = 'ordem_de_servicos';

        if ($busca) {
            if (is_numeric($busca)) {
                $sql = $db->setRelatorios("O.id=$busca AND baixa_suporte = 'Não'");
            } else {
                $busca = new DateTime(str_replace('/', '-', $busca));
                $sql = $db->setRelatorios("data_suporte = '" . $busca->format('Y-m-d') . "' AND baixa_suporte = 'Não'");
            }
            $datas['sql'] = $sql;
        }

        if ($this->getParam('do')) {
            $data = new DateTime(str_replace('/', '-', $_POST['data']));
            $db->baixarOrdem($id, $data->format('Y-m-d'), 'Sim');
            $redirect->goToControllerAction('admin', 'livro_baixas');
        }
        $datas['id'] = $this->getParam('id');

        self::showPage('livro_baixas', $datas);
    }

}
