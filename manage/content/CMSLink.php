<?php

class CMSLink extends CMSObject {

    public $attributes = array(
        "title"  => array( 'name' => "title",  'db_type' => "varchar", 'description' => "Titel", 'obligatory' => true ),
        "url"    => array( 'name' => "url",    'db_type' => "varchar", 'description' => "URL",   'obligatory' => true ),
        "target" => array( 'name' => "target", 'db_type' => "varchar", 'description' => "Ziel",  'obligatory' => true ) 
    );

    function __construct($data=array( 'type' => "cms_link", 'title' => "", 'url' => "", 'target' => "" )) {
        $this->set_standard_attributes($data);

        $this->data["url"]    = $data["url"];
        $this->data["title"]  = $data["title"];
        $this->data["target"] = $data["target"];
    }

    public function get_title() {
        return ($this->data["title"]) ? $this->data["title"] : "Link";
    }

    public function get_description() {
        return "Link";
    }

    public function get_href() {
        if (preg_match('/^\${e(\d+)}/', $this->data["url"], $matches)) {
            return DB::get_instance()->get_object_by_id($matches[1], true)->get_href_to_index();
        }
        elseif (preg_match('/^\&{e(\d+)}/', $this->data["url"], $matches)) {
            return DB::get_instance()->get_object_by_id($matches[1], true)->get_href();
        }
        else {
            return $this->data["url"];
        }
    }

    public function get_linked_object_id() {
        if (preg_match('/^[\$ \&]{e(\d+)}/', $this->data["url"], $matches)) {
            return $matches[1];
        }
        return null;
    }
}

?>