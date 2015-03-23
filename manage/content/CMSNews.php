<?php

class CMSNews extends CMSObject {

    public $attributes = array(
        "title"  => array( 'name' => "title",  'db_type' => "varchar", 'description' => "Title", 'obligatory' => true  ),
        "active" => array( 'name' => "active", 'db_type' => "tinyint", 'description' => "aktiv", 'obligatory' => false ),
        "text"   => array( 'name' => "text",   'db_type' => "text",    'description' => "Text",  'obligatory' => true  )
    );

    function __construct($data=array( 'type' => "cms_news", 'title' => "", 'active' => null, 'text' => "" )) {
        $this->set_standard_attributes($data);

        $this->data["title"]  = $data["title"];
        $this->data["active"] = $data["active"];
        $this->data["text"]   = $data["text"];
    }

    public function get_title() {
        return ($this->data["title"]) ? $this->data["title"] : "News";
    }

    public function get_description() {
        return "News";
    }

    public function get_text() {
        return $this->data["text"];
    }
}

?>