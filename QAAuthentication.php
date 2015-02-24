<?php
namespace exertis\experianqas;

class QAAuthentication{
    private $Username;
    private $Password;

    public function __construct($username,$password) {
      $this->Username=$username;
      $this->Password=$password;
    }
}


