<?php

class CMSUserNode {

    public $id   = "";
    public $user = "";
    public $node = "";

    function __construct($data=array(
        'id'   => "",
        'user' => "",
        'node' => ""
    )) {
        $this->id   = $data["id"];
        $this->user = $data["user"];
        $this->node = $data["node"];
    }

    public function validate() {
        $errors = array();

        if (!$this->user) 
            array_push($errors, "Feld 'Benutzer' darf nicht leer sein.");
        if (!$this->node)
            array_push($errors, "Feld 'Knoten' darf nicht leer sein.");

        return $errors;
    }
}