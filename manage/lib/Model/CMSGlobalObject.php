<?php

class CMSGlobalObject {

    public $id        = "";
    public $name      = "";
    public $object_id = "";

    function __construct($data=array(
        'id'        => "",
        'name'      => "",
        'object_id' => ""
    )) {
        $this->id       = isset($data["id"])
            ? $data["id"]
            : "";
        $this->name      = $data["name"];
        $this->object_id = $data["object_id"];
    }

    public function validate() {
        $errors = array();

        if (!$this->name) 
            array_push($errors, "Feld 'Name' darf nicht leer sein.");
        if (!$this->object_id)
            array_push($errors, "Feld 'Objekt' darf nicht leer sein.");

        return $errors;
    }

    public function get_object() {
        return DB::get_instance()->get_object_by_id($this->object_id);
    }
}