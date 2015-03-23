<?php

class CMSMultimedia extends CMSObject {

    public $attributes = array(
        "title"          => array( 'name' => "title",         'db_type' => "varchar", 'description' => "Titel",              'obligatory' => false ),
        "embedded_code"  => array( 'name' => "embedded_code", 'db_type' => "text",    'description' => "Eingebetteter Code", 'obligatory' => true  )
    );

    function __construct($data=array( 'type' => "cms_multimedia", 'title' => null, 'embedded_code' => "" )) {
        $this->set_standard_attributes($data);

        $this->data["title"]         = $data["title"];
        $this->data["embedded_code"] = $data["embedded_code"];
    }

    public function get_title() {
        return ($this->data["title"]) ? $this->data["title"] : "Multimedia";
    }

    public function get_description() {
        return "Multimedia";
    }

    public function get_embedded_code() {
        return $this->data["embedded_code"];
    }
}

?>