<?php

class Model {

    protected $_db, $user, $pass, $host, $dbname;
    public $_tabela;

    private function setConfig($tipo) {
        switch ($tipo) {
            case "local":
                $this->user = 'root';
                $this->pass = '';
                $this->host = 'localhost';
                $this->dbname = 'conserti';
                return $this;
                break;
            case "remoto":
                $this->user = '';
                $this->pass = '';
                $this->host = '';
                $this->dbname = '';          
                break;
        }
    }

    public function __construct() {
    	$tipo = ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') ? "remoto" : "local";
        self::setConfig($tipo);
        try {
            $this->_db = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbname . '', $this->user, $this->pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        } catch (Exception $e) {
            echo "Houve um erro: <br> " . $e->getMessage();
        }
    }

    public function setQuery($campos, $apelido, Array $opcoes) {
        try {
            $op = implode(" ", array_values($opcoes));
            $q = $this->_db->query(" SELECT $campos FROM {$this->_tabela} {$apelido} {$op} ");
            $q->setFetchMode(PDO::FETCH_ASSOC);
            return $q->fetchAll();
        } catch (Exception $exc) {
            return $exc->getMessage();
        }
    }

    public function insert(Array $dados) {
        $campos = implode(", ", array_keys($dados));
        $valores = "'" . implode("','", array_values($dados)) . "'";
        return $this->_db->query(" INSERT INTO `{$this->_tabela}` ({$campos}) VALUES ({$valores}) ");
    }

    public function read($where = null, $limit = null, $offset = null, $orderby = null, $groupby = null) {
        $where = ($where != null ? "WHERE {$where}" : "");
        $limit = ($limit != null ? "LIMIT {$limit}" : "");
        $offset = ($offset != null ? "OFFSET {$offset}" : "");
        $orderby = ($orderby != null ? "ORDER BY {$orderby}" : "");
        $groupby = ($groupby != null ? "GROUP BY {$groupby}" : "");
        $q = $this->_db->query(" SELECT * FROM `{$this->_tabela}` {$where} {$groupby} {$orderby} {$limit} {$offset} ");
        $q->setFetchMode(PDO::FETCH_ASSOC);
        return $q->fetchAll();
    }

    public function update(Array $dados, $where) {
        foreach ($dados as $ind => $val) {
            $campos[] = "{$ind} = '{$val}'";
        }
        $campos = implode(", ", $campos);
        return $this->_db->query(" UPDATE `{$this->_tabela}` SET {$campos} WHERE {$where} ");
    }

    public function delete($where) {
        return $this->_db->query(" DELETE FROM `{$this->_tabela}` WHERE {$where} ");
    }

}
