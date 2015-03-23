<?php

class CMSFile extends CMSObject {

    public $attributes = array(
        "title" => array( 'name' => "title", 'db_type' => "varchar", 'description' => "Titel", 'obligatory' => true ),
        "file"  => array( 'name' => "file",  'db_type' => "varchar", 'description' => "Datei", 'obligatory' => true )
    );

    function __construct($data=array( 'type' => "cms_file", 'title' => "", 'file' => "" )) {
        $this->set_standard_attributes($data);

        $this->data["title"] = $data["title"];
        $this->data["file"]  = $data["file"];
    }

    public function prepare() {
        parent::prepare();

        if ($_FILES["file"]["name"]) {
            $this->data["file"] = basename($_FILES["file"]["name"]);
        }
    }

    public function get_title() {
        return ($this->data["title"]) ? $this->data["title"] : "Datei";
    }

    public function get_description() {
        return "Datei";
    }

    public function write_filesystem() {
        $path = parent::write_filesystem();

        if (!empty($_FILES["file"]["name"])) {
            move_uploaded_file($_FILES["file"]["tmp_name"], $path . "/" . basename($_FILES["file"]["name"]));
        }
    }

    public function update_filesystem() {
        if (!empty($_FILES["file"]["name"])) {
            $path = get_document_root() . "/" . $this->get_path();

            clear_dir($path);

            move_uploaded_file($_FILES["file"]["tmp_name"], $path . "/" . basename($_FILES["file"]["name"]));
        }
    }

    public function get_href() {
         return uri_for("/" . $this->get_path() . "/" . $this->data["file"]);
    }
}

?>