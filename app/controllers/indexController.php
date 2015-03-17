<?php

class Index extends Controller {

    public function Index_action() {
        $this->view('templates/header');
        $this->view('index');
        $this->view('templates/footer');
    }

}