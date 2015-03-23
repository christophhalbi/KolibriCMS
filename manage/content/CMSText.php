<?php

class CMSText extends CMSObject {

    public $attributes = array(
        "text" => array( 'name' => "text", 'db_type' => "text", 'description' => "Text", 'obligatory' => true )
    );

    function __construct($data=array( 'type' => "cms_text", 'text' => "" )) {
        $this->set_standard_attributes($data);

        $this->data["text"] = $data["text"];
    }

    public function get_title() {
        return "Text";
    }

    public function get_description() {
        return "Text";
    }

    public function get_text() {
        return $this->data["text"];
    }

    public function get_text_preview() {
        $stripped_text = strip_tags($this->data["text"]);

        if (strlen($stripped_text) > 250) {
            return substr($stripped_text, 0, 250) . "...";
        }
        else {
            return $stripped_text;
        }
    }
}

?>