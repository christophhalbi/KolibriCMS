<?php

class CMSContainer extends CMSObject {

    public $attributes = array(
        "title"    => array( 'name' => "title",    'db_type' => "varchar", 'description' => "Titel",    'obligatory' => true  ),
        "classes"  => array( 'name' => "classes",  'db_type' => "varchar", 'description' => "Klassen",  'obligatory' => false ),
        "template" => array( 'name' => "template", 'db_type' => "varchar", 'description' => "Template", 'obligatory' => false )
    );

    function __construct($data=array(
        'type'     => "cms_container",
        'title'    => "",
        'classes'  => null,
        'template' => null
    )) {
        $this->set_standard_attributes($data);

        $this->data["title"]         = $data["title"];
        $this->data["classes"]       = $data["classes"];
        $this->data["template"]      = $data["template"];
        $this->data["page_elements"] = array();
    }

    public function is_container() {
        return true;
    }

    public function get_title() {
        return ($this->data["title"]) ? $this->data["title"] : "Container";
    }

    public function get_description() {
        return "Container";
    }
}

?>