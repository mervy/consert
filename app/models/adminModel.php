<?php

class AdminModel extends Model {

    public function listaDados() {
        return $this->read(null, null, null, "id ASC", null);
    }

    public function setQueryLastFunc($sup_id, $fil_id) {
        return $this->setQuery("ord.funcionarios_id AS id_func, func.nome AS nome_func, sup.area, fil.filial", "ord", array(
                    "INNER JOIN funcionarios func ON funcionarios_id = func.id",
                    "INNER JOIN suportes sup ON suportes_id ='$sup_id'",
                    "INNER JOIN filiais fil ON filiais_id ='$fil_id'",
                    'order by ord.id DESC limit 1'
        ));
    }

    public function setRelatorios($where) {
        return $this->setQuery('O.*, O.funcionarios_id AS id_func, FU.nome AS nome_func, SU.area, FI.filial, SE.setor, SV.servico', 'O', array('INNER JOIN funcionarios FU ON funcionarios_id = FU.id',
                    'INNER JOIN suportes SU ON suportes_id = SU.id',
                    'INNER JOIN filiais FI ON filiais_id = FI.id',
                    'INNER JOIN setores SE ON setores_id = SE.id',
                    'INNER JOIN servicos SV ON servicos_id = SV.id',
                    "WHERE $where"
        ));
    }

    public function listaTudo() {
        return $this->setQuery('O.*, O.funcionarios_id AS id_func, FU.nome AS nome_func, SU.area, FI.filial, SE.setor, SV.servico', 'O', array('INNER JOIN funcionarios FU ON funcionarios_id = FU.id',
                    'INNER JOIN suportes SU ON suportes_id = SU.id',
                    'INNER JOIN filiais FI ON filiais_id = FI.id',
                    'INNER JOIN setores SE ON setores_id = SE.id',
                    'INNER JOIN servicos SV ON servicos_id = SV.id',
                    'ORDER BY O.id'));
    }

    public function listaOrdemDeServicos($limit = null) {
        $limit = ($limit != null ? "LIMIT {$limit}" : "");
        return $this->setQuery("O.id, O.resumo, O.data_suporte, SU.area, ST.setor, SE.servico, FU.nome, FI.filial", "O", array(
                    "INNER JOIN suportes SU ON ( O.suportes_id = SU.id )",
                    "INNER JOIN setores ST ON ( O.setores_id = ST.id )",
                    "INNER JOIN servicos SE ON ( O.servicos_id = SE.id )",
                    "INNER JOIN funcionarios FU ON ( O.funcionarios_id = FU.id )",
                    "INNER JOIN filiais FI ON ( O.filiais_id = FI.id )",
                    " ORDER BY O.id DESC $limit"
        ));
    }

    public function insereDadosFuncionarios($nome, $email, $funcao, $areas, $filiais, $afastamento, $motivo_afastamento, $data_afastamento, $data_retorno) {
        return $this->insert(array(
                    "id" => NULL,
                    "nome" => $nome,
                    "email" => $email,
                    "funcao" => $funcao,
                    "suportes" => $areas,
                    "filiais" => $filiais,
                    "afastamento" => $afastamento,
                    "motivo_afastamento" => $motivo_afastamento,
                    "data_afastamento" => $data_afastamento,
                    "data_retorno" => $data_retorno
        ));
    }

    public function insereDadosSetores($setor) {
        return $this->insert(array(
                    "id" => NULL,
                    "setor" => $setor
        ));
    }

    //filial'], $_POST['cnpj'], $_POST['endereco'], $_POST['telefone'
    public function insereDadosFiliais($filial, $cnpj, $endereco, $telefone) {
        return $this->insert(array(
                    "id" => NULL,
                    "filial" => $filial,
                    "cnpj" => $cnpj,
                    "endereco" => $endereco,
                    "telefone" => $telefone
        ));
    }

    public function insereDadosSuportes($area) {
        return $this->insert(array(
                    "id" => NULL,
                    "area" => $area
        ));
    }

    public function insereDadosServicos($servico) {
        return $this->insert(array(
                    "id" => NULL,
                    "servico" => $servico
        ));
    }

    public function insereDadosUsuarios($login, $nome, $funcao, $senha, $nivel, $data, $status) {
        return $this->insert(array(
                    "id" => NULL,
                    "login" => $login,
                    "nome" => $nome,
                    "funcao" => $funcao,
                    "senha" => $senha,
                    "nivel" => $nivel,
                    "data" => $data,
                    "status" => $status
        ));
    }

    public function emitirOrdem($resumo, $data_suporte, $suportes_id, $setores_id, $servicos_id, $funcionarios_id, $filiais_id) {
        return $this->insert(array(
                    "id" => NULL,
                    "resumo" => $resumo,
                    "data_suporte" => $data_suporte,
                    "suportes_id" => $suportes_id,
                    "setores_id" => $setores_id,
                    "servicos_id" => $servicos_id,
                    "funcionarios_id" => $funcionarios_id,
                    "filiais_id" => $filiais_id
        ));
    }

    public function deletaDado($id) {
        return $this->delete("id=" . $id);
    }

    public function dadosAtuais($where) {
        return $this->read($where, null, null, "id DESC", null);
    }

    public function dadosAtuaisOficial($where) {//Usado no metodo da action oficiais
        return $this->read($where, null, null, "id ASC", null);
    }

    public function alteraDadoFuncionarios($id, $nome, $email, $funcao, $suportes, $filiais, $afastamento, $motivo_afastamento, $data_afastamento, $data_retorno) {
        return $this->update(array(
                    "id" => $id,
                    "nome" => $nome,
                    "email" => $email,
                    "funcao" => $funcao,
                    "suportes" => $suportes,
                    "filiais" => $filiais,
                    "afastamento" => $afastamento,
                    "motivo_afastamento" => $motivo_afastamento,
                    "data_afastamento" => $data_afastamento,
                    "data_retorno" => $data_retorno), 'id=' . $id
        );
    }

    public function alteraDadoSetores($id, $setor) {
        return $this->update(array(
                    "id" => $id,
                    "setor" => $setor
                        ), 'id=' . $id);
    }

    public function alteraDadoFiliais($id, $filial, $cnpj, $endereco, $telefone) {
        return $this->update(array(
                    "id" => $id,
                    "filial" => $filial,
                    "cnpj" => $cnpj,
                    "endereco" => $endereco,
                    "telefone" => $telefone
                        ), 'id=' . $id);
    }

    public function alteraDadoSuportes($id, $area) {
        return $this->update(array(
                    "id" => $id,
                    "area" => $area
                        ), 'id=' . $id);
    }

    public function alteraDadoServicos($id, $servico) {
        return $this->update(array(
                    "id" => $id,
                    "servico" => $servico
                        ), 'id=' . $id);
    }

    public function alteraDadoUsuarios($id, $login, $nome, $funcao, $senha, $nivel, $data, $status) {
        return $this->update(array(
                    "id" => $id,
                    "login" => $login,
                    "nome" => $nome,
                    "funcao" => $funcao,
                    "senha" => $senha,
                    "nivel" => $nivel,
                    "data" => $data,
                    "status" => $status
                        ), 'id=' . $id);
    }

    public function baixarOrdem($id, $data, $baixa) {
        return $this->update(array(
                    "id" => $id,
                    "data_realizacao" => $data,
                    "baixa_suporte" => $baixa
                        ), 'id=' . $id);
    }

    public function mostraUltimoId() {
        return $this->read(NULL, "1", NULL, "id desc", NULL);
    }

}
