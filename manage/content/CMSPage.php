<?php

class CMSPage extends CMSObject {

    public $attributes = array(
        "title"           => array( 'name' => "title",           'db_type' => "varchar", 'description' => "Titel",                  'obligatory' => true  ),
        "title_sef"       => array( 'name' => "title_sef",       'db_type' => "varchar", 'description' => "Titel (SEO)",            'obligatory' => true  ),
        "title_alt"       => array( 'name' => "title_alt",       'db_type' => "varchar", 'description' => "alternativer Titel",     'obligatory' => false ),
        "level"           => array( 'name' => "level",           'db_type' => "int",     'description' => "Level",                  'obligatory' => false ),
        "display_in_navi" => array( 'name' => "display_in_navi", 'db_type' => "tinyint", 'description' => "In Navigation anzeigen", 'obligatory' => false ),
        "seo_keywords"    => array( 'name' => "seo_keywords",    'db_type' => "text",    'description' => "Schlagwörter (SEO)",     'obligatory' => false ),
        "seo_description" => array( 'name' => "seo_description", 'db_type' => "text",    'description' => "Beschreibung (SEO)",     'obligatory' => false )
    );

    function __construct($data=array(
        'type'            => "cms_page", 
        'title'           => "",
        'title_sef'       => "",
        'title_alt'       => null,
        'level'           => null,
        'display_in_navi' => null,
        'seo_keywords'    => null,
        'seo_description' => null
    )) {
        $this->set_standard_attributes($data);

        $this->data["title"]           = $data["title"];
        $this->data["title_sef"]       = $data["title_sef"];
        $this->data["title_alt"]       = $data["title_alt"];
        $this->data["level"]           = $data["level"];
        $this->data["display_in_navi"] = $data["display_in_navi"];
        $this->data["seo_keywords"]    = $data["seo_keywords"];
        $this->data["seo_description"] = $data["seo_description"];
        $this->data["page_children"]   = array();
        $this->data["page_elements"]   = array();
    }

    public function is_page() {
        return true;
    }

    public function prepare() {
        parent::prepare();

        if ($this->data["parent"] && !isset($this->data["level"])) {
            $parent = DB::get_instance()->get_object_by_id($this->data["parent"], true);

            $this->data["level"] = $parent->level + 1;
        }

        if ($this->data["title"]) {
            $title_sef = $this->data["title"];
            $title_sef = strtolower($title_sef);
            $title_sef = str_replace("ä", "ae", $title_sef);
            $title_sef = str_replace("ü", "ue", $title_sef);
            $title_sef = str_replace("ö", "oe", $title_sef);
            $title_sef = str_replace("ß", "sz", $title_sef);
            $title_sef = preg_replace('/[^a-z0-9_-]/', '_', $title_sef);
            $title_sef = preg_replace('/_+/', '_', $title_sef);

            $this->data["title_sef"] = $title_sef;
        }
    }

    public function get_title() {
        return ($this->data["title"]) ? $this->data["title"] : "Seite";
    }

    public function get_title_alt() {
        return ($this->data["title_alt"]) ? $this->data["title_alt"] : $this->data["title"];
    }

    public function get_description() {
        return "Seite";
    }

    public function get_href_to_index() {

        if ($beautiful_paths) {
            return uri_for("/" . DB::get_instance()->get_object_path($this) . "/index.html");
        }
        else {
            return uri_for("/", array( id => $this->id ));
        }
    }

    public function get_page_children($check_display=true) {
        return DB::get_instance()->get_pages_by_parent($this->data["id"], $check_display);
    }
}

?>