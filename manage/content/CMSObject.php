<?php

class CMSObject {

    protected $data = array();

    public $standard_attributes = array(
        "id"           => array( 'name' => "id",           'db_type' => "int",       'obligatory' => true  ),
        "parent"       => array( 'name' => "parent",       'db_type' => "int",       'obligatory' => false ),
        "type"         => array( 'name' => "type",         'db_type' => "varchar",   'obligatory' => true  ),
        "changed_date" => array( 'name' => "changed_date", 'db_type' => "timestamp", 'obligatory' => true  ),
        "changed_by"   => array( 'name' => "changed_by",   'db_type' => "varchar",   'obligatory' => true  ),
    );

    public function __get($param) {
        if ( isset($this->data[$param])) {
            return $this->data[$param];
        } else {
            return "";
        }
    }

    public function __set($param, $value) {
        $this->data[$param] = $value;
    }

    public function set_standard_attributes($data) {
        foreach (array_keys($this->standard_attributes)  as $standard_attribute) {
            $this->data[$standard_attribute] = (isset($data[$standard_attribute]))
                ? $data[$standard_attribute]
                : null;
        }
    }

    public function set_attributes($data) {
        while (list($attribute_key, $attribute_value) = each($data)) {
            $this->data[$attribute_key] = $attribute_value;
        }
    }

    public function is_page() {
        return false;
    }

    public function is_container() {
        return false;
    }

    public function is_content_root() {
        return ($this->data["id"] == 1) ? 1 : 0;
    }

    public function is_object_type($object_type) {
        return ($this->data["type"] == $object_type) ? true : false;
    }

    public function is_restriction_node() {
        foreach ($_SESSION["cms_user"]->nodes as $user_node) {
            if ($this->data["id"] == $user_node["object"]->id) {
                return true;
            }
        }
        return false;
    }

    public function prepare() {
        while (list($attribute_key, $attribute_value) = each($this->data)) {
            $attribute = isset($this->attributes[$attribute_key])
                ? $this->attributes[$attribute_key]
                : null;

            if ($attribute) {
                if ($attribute["db_type"] == "tinyint" && !isset($_POST[$attribute_key])) {
                    $this->data[$attribute_key] = 0;
                }
            }
        }
        reset($this->data);
    }

    public function validate() {
        $errors = array();

        while (list($attribute_key, $attribute_value) = each($this->data)) {
            $attribute = isset($this->attributes[$attribute_key])
                ? $this->attributes[$attribute_key]
                : null;

            if ($attribute) {
                if ($attribute["obligatory"] && !$attribute_value) {
                    array_push($errors, "Feld '". $attribute["description"] . "' darf nicht leer sein.");
                }
            }
        }
        reset($this->data);

        return $errors;
    }

    public function get_title() {
        return "Objekt";
    }

    public function get_description() {
        return "Objekt";
    }

    public function get_changed() {
        return ($this->data["id"]) ? $this->data["changed_date"] . ", " . $this->data["changed_by"] : "";
    }

    public function get_dcid() {
        return "e" . $this->data["id"];
    }

    public function get_path() {
        return DB::get_instance()->get_object_path($this, false);
    }

    public function get_href_to_index() {
        $parent = DB::get_instance()->get_object_by_id($this->data["parent"], true);

        $parent = ($parent->is_container())
            ? DB::get_instance()->get_object_by_id($parent->data["parent"], true)
            : $parent;

        if ($beautiful_paths) {
            $uri = uri_for("/" . DB::get_instance()->get_object_path($parent) . "/index.html");
        }
        else {
            $uri = uri_for("/", array( id => $this->id ));
        }

        return $uri . "#" . $this->get_dcid();
    }

    public function write_filesystem() {
        $path = get_document_root() . "/" . $this->get_path();

        if (!mkdir($path)) {
            die("Verzeichnis konnte nicht erstellt werden");
        }

        return $path;
    }

    public function update_filesystem() {

    }

    public function delete_filesystem() {
        $path = get_document_root() . "/" . $this->get_path();

        remove_dir($path);

        if ($this->has_custom_body_content()) {
            unlink(path_for("/manage/root/templates/custom/" . $this->data["id"] . ".tmpl"));
        }
    }

    public function get_global_object($name) {
        $global_object = DB::get_instance()->get_global_object_by_name($name);

        if ($global_object) {
            return $global_object->get_object();
        }
        else {
            return null;
        }
    }

    public function get_parents() {
        $parents = array();
        $paths   = preg_split("/\//", $this->get_path());
        array_pop($paths); // remove object itself
        $paths   = array_reverse($paths);
        array_pop($paths); // remove content-part

        while ($paths) {
           $id = array_pop($paths);

            if (preg_match('/^e(\d+)/', $id, $matches)) {
                array_push($parents, DB::get_instance()->get_object_by_id($matches[1]));
            }
        }

        return $parents;
    }

    public function get_parent() {
        return DB::get_instance()->get_object_by_id($this->data["parent"]);
    }

    public function get_page_elements($types=null) {
        return DB::get_instance()->get_page_elements_by_parent($this->data["id"], $types);
    }

    public function get_object_by_id($id) {
        return DB::get_instance()->get_object_by_id($id, true);
    }

    public function get_management_interface() {
        $template = new PHPTAL(path_for("/manage/root/templates/content/" . $this->data["type"] . "/properties.tmpl"));

        $object_types = array();
        foreach (get_object_types() as $object_type) {
            if ($this->is_page() || ($this->is_container() && $object_type["page_element"])) {
                array_push($object_types, $object_type);
            }
        }

        $template->self = $this;
        $template->object_types   = $object_types;
        $template->uri_static     = uri_for("/manage/root/static");
        $template->uri_static_cms = uri_for("/static");
        $template->uri_content    = uri_for("/manage/content");
        $template->uri_filesystem = uri_for("/manage/filesystem");
        $template->user           = get_current_cms_user();

        try {
            return $template->execute();
        }
        catch (Exception $e){
            return $e;
        }
    }

    public function get_management_url() {
        return uri_for("/manage/content", array( 'id' => $this->data["id"] ));
    }

    public function get_management_delete_url() {
        return uri_for("/manage/content", array( 'delete' => $this->data["id"] ));
    }

    public function has_custom_body_content() {
        return file_exists(path_for("/manage/root/templates/custom/"  . $this->data["id"]   . ".tmpl"));
    }

    public function get_body_content() {
        if ($this->data["template"]) {

            $template = new PHPTAL(path_for("/manage/root/templates/custom/"  . $this->data["template"]));
        }
        else {
            $template = ($this->has_custom_body_content())
                ? new PHPTAL(path_for("/manage/root/templates/custom/"  . $this->data["id"]   . ".tmpl"))
                : new PHPTAL(path_for("/manage/root/templates/content/" . $this->data["type"] . "/body_content.tmpl"));
        }

        $template->self = $this;

        try {
            return $template->execute();
        }
        catch (Exception $e){
            return $e;
        }
    }

    public function get_custom_body_content_url() {
        return uri_for("/manage/filesystem", array( file => "/manage/root/templates/custom/"  . $this->data["id"]   . ".tmpl" )); 
    }
}

?>