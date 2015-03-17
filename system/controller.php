<?php

class Controller extends System {
/**
 * 
 * @param type $nome
 * @param type $vars
 */
    protected function view($nome, $vars = null) {
        if (is_array($vars) && count($vars) > 0)
            extract($vars, EXTR_PREFIX_ALL, 'view');

        $file = VIEWS . $nome . '.phtml';

        if (!file_exists($file))
            die("Houve um erro. View nao existe.");

        require_once( $file );
    }
    public function init(){}

}