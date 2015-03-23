<?php

class CMSUser {

    public $id       = "";
    public $name     = "";
    public $password = "";
    public $role     = "";
    public $nodes    = array();

    function __construct($data=array(
        'id'       => "",
        'name'     => "",
        'password' => "",
        'role'     => ""
    )) {
        $this->id       = isset($data["id"])
            ? $data["id"]
            : "";
        $this->name     = $data["name"];
        $this->password = $data["password"];
        $this->role     = $data["role"];
    }

    public function validate() {
        $errors = array();

        if (!$this->name) 
            array_push($errors, "Feld 'Name' darf nicht leer sein.");
        if (!$this->password)
            array_push($errors, "Feld 'Passwort' darf nicht leer sein.");
        if (!$this->role)
            array_push($errors, "Feld 'Rolle' darf nicht leer sein.");

        return $errors;
    }

    public function is_editor() {
        return (strcmp($this->role, "CMSEditor") == 0) ? 1 : 0;
    }
}