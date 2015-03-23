<?php

class CMSImage extends CMSObject {

    public $attributes = array(
        "title"         => array( 'name' => "title",         'db_type' => "varchar", 'description' => "Titel",           'obligatory' => false ),
        "image"         => array( 'name' => "image",         'db_type' => "varchar", 'description' => "Bild",            'obligatory' => true  ),
        "image_highres" => array( 'name' => "image_highres", 'db_type' => "varchar", 'description' => "Bild (high-res)", 'obligatory' => false ),
        "url"           => array( 'name' => "url",           'db_type' => "varchar", 'description' => "URL",             'obligatory' => false ),
        "url_target"    => array( 'name' => "url_target",    'db_type' => "varchar", 'description' => "URL-Ziel",        'obligatory' => true  ),
        "align"         => array( 'name' => "align",         'db_type' => "varchar", 'description' => "Ausrichtung",     'obligatory' => true  ),
        "text"          => array( 'name' => "text",          'db_type' => "text",    'description' => "Beschreibung",    'obligatory' => false )
    );

    private $image_attributes = array( "image", "image_highres" );

    function __construct($data=array(
        'type'          => "cms_image",
        'title'         => null,
        'image'         => "",
        'image_highres' => null, 
        'url'           => null,
        'url_target'    => "",
        'align'         => "",
        'text'          => null 
    )) {
        $this->set_standard_attributes($data);

        $this->data["title"]         = $data["title"];
        $this->data["image"]         = $data["image"];
        $this->data["image_highres"] = $data["image_highres"];
        $this->data["url"]           = $data["url"];
        $this->data["url_target"]    = $data["url_target"];
        $this->data["align"]         = $data["align"];
        $this->data["text"]          = $data["text"];
    }

    public function prepare() {
        parent::prepare();

        foreach ($this->image_attributes as $image_attribute) {
            if (!empty($_FILES[$image_attribute]["name"])) {
                $this->data["old_" . $image_attribute] = $this->data[$image_attribute];
                $this->data[$image_attribute]          = basename($_FILES[$image_attribute]["name"]);
            }
        }
    }

    public function get_title() {
        return ($this->data["title"]) ? $this->data["title"] : "Abbildung";
    }

    public function get_title_alt() {
        return ($this->data["title"]) ? $this->data["title"] : "";
    }

    public function get_description() {
        return "Abbildung";
    }

    public function get_text() {
        return $this->data["text"];
    }

    public function write_filesystem() {
        $path = parent::write_filesystem();

        foreach ($this->image_attributes as $image_attribute) {
            if (!empty($_FILES[$image_attribute]["name"])) {
                move_uploaded_file(
                    $_FILES[$image_attribute]["tmp_name"],
                    $path . "/" . basename($_FILES[$image_attribute]["name"])
                );
            }
        }

        return $path;
    }

    public function update_filesystem() {
        if (!empty($_FILES["image"]["name"]) || !empty($_FILES["image_highres"]["name"])) {
            $path = get_document_root() . "/" . $this->get_path();

            foreach ($this->image_attributes as $image_attribute) {
                if (!empty($_FILES[$image_attribute]["name"])) {
                    if (file_exists($path . "/" . $this->data["old_" . $image_attribute])) {
                        unlink($path . "/" . $this->data["old_" . $image_attribute]);
                    }
                    move_uploaded_file(
                        $_FILES[$image_attribute]["tmp_name"],
                        $path . "/" . basename($_FILES[$image_attribute]["name"])
                    );
                }
            }
        }
    }

    public function get_href() {
        return uri_for("/" . $this->get_path() . "/" . $this->data["image"]);
    }

    public function get_highres_href() {
        return uri_for("/" . $this->get_path() . "/" . $this->data["image_highres"]);
    }

    public function get_link() {
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

    public function get_image_dimension() {
        return $this->get_formated_image_dimension(get_document_root() . "/" . $this->get_path() . "/" . $this->data["image"]);
    }

    public function get_image_highres_dimension() {
        return $this->get_formated_image_dimension(get_document_root() . "/" . $this->get_path() . "/" . $this->data["image_highres"]);
    }

    private function get_formated_image_dimension($file) {
        $image_info = getimagesize($file);
        return $image_info[0] . " x " . $image_info[1];
    }
}

?>