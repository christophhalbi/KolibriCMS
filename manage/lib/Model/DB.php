<?php

class DB {

    private static $instance;

    private $dbh;

    private function __construct() {

        try {

            $this->dbh = new PDO("mysql:host=localhost;dbname=", "", "", array( PDO::ATTR_PERSISTENT => true ));

            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e) {

            die($e->getMessage());
        }
    }

    public static function get_instance() {
        if (null == self::$instance) {
            self::$instance = new DB();
        }

        return self::$instance;
    }

    public function get_pages_tree($check_display = false) {

        $sth = $this->dbh->prepare($this->prepare_pages_query($check_display) . " ORDER BY parent, sort_id");

        $sth->execute();

        $page_objects = $this->to_page_objects($sth);

        return $this->build_tree($page_objects, 1);
    }

    public function get_pages_by_parent($parent, $check_display = false) {

        $sth = $this->dbh->prepare($this->prepare_pages_query($check_display) . " AND parent = ? ORDER BY sort_id, id");

        $sth->bindValue(1, $parent, PDO::PARAM_INT);

        $sth->execute();

        return $this->to_page_objects($sth);
    }

    private function prepare_pages_query($check_display = false) {

        $extra_query = ($check_display) ? " AND display_in_navi = true " : "";

        return "
            SELECT cms_object.parent, cms_object.type, cms_object.changed_date, cms_object.changed_by, cms_object.sort_id, cms_page.*
            FROM   cms_object
            INNER JOIN cms_page ON cms_object.id = cms_page.id
            WHERE type = \"cms_page\" $extra_query";
    }

    private function to_page_objects($sth) {

        $page_objects = array();

        $pages = $sth->fetchAll();

        foreach ($pages as $page) {

            array_push($page_objects, new CMSPage($page));
        }

        return $page_objects;
    }

    private function build_tree(array &$pages, $parent = 0) {

        $branch = array();

        foreach ($pages as $page) {

            if  ($page->parent == $parent) {

                $children = $this->build_tree($pages, $page->id);

                if ($children) {
                    $page->page_children = $children;
                }

                $branch[] = $page;
            }
        }

        return $branch;
    }

    public function get_object_by_id($id, $be_lazy=false) {

        $sth = $this->dbh->prepare("SELECT id, type FROM cms_object WHERE id = ?");

        $sth->bindValue(1, $id, PDO::PARAM_INT);

        $sth->execute();

        $row = $sth->fetch();

        if ($row) {

            $type = $row["type"];

            $sth_object = $this->dbh->prepare("
                SELECT $type.*, cms_object.parent, cms_object.type, cms_object.changed_date, cms_object.changed_by
                FROM   $type
                INNER JOIN cms_object ON $type.id = cms_object.id
                WHERE $type.id = ?
            ");

            $sth_object->bindValue(1, $id, PDO::PARAM_INT);

            $sth_object->execute();

            $row_object = $sth_object->fetch();

            if ($row_object) {

                $classname = get_classname_by_type($type);

                $object = new $classname($row_object);

                // get page-elements if we are not lazy
                if ($be_lazy == false) {
                    $object->page_elements = $this->get_page_elements_by_parent($object->id);
                }

                return $object;
            }
        }

        return null;
    }

    /*
    public function get_object_by_path($object_path) {
        $paths = split("/", $object_path);
        array_pop($paths); // remove last part (index.html or filename)
        $paths = array_reverse($paths);
        array_pop($paths); // remove content-part
        array_pop($paths); // remove path of content-root (cms_object with id = 1)

        $level       = 1;
        $last_parent = 1; // start with content-root (cms_object with id = 1)
        $object_id   = null;

        if (count($paths) == 0) { // all paths removed, get content-root
            $object_id = 1;
        }

        while ($paths) {
            $path = array_pop($paths);

            if (preg_match("/^e(\d+)/", $path, $matches)) {
                $result = mysql_query("SELECT * FROM cms_object WHERE id = " . $matches[1], $this->connection);
            }
            else {
                $result = mysql_query("SELECT cms_page.*, cms_object.parent
                    FROM cms_page
                    INNER JOIN cms_object ON cms_page.id = cms_object.id
                    WHERE title_sef = \"$path\" AND level = $level AND parent = $last_parent", $this->connection);
            }

            if ($data = mysql_fetch_assoc($result)) {
                $object_id   = $data["id"];
                $last_parent = $data["id"];
                $level++;
            }
            else {
                $object_id = null;
                break;
            }
        }

        // object resolved by path, get it
        if ($object_id) {
            return $this->get_object_by_id($object_id);
        }
        return null;
    }*/

    public function get_object_path($object, $beautiful_paths=true) {

        $paths = array("content");

        $sth = $this->dbh->prepare("
            SELECT T2.id, cms_page.title_sef
            FROM (
                SELECT
                    @r AS _id,
                    (SELECT @r := parent FROM cms_object WHERE id = _id) AS parent,
                    @l := @l + 1 AS lvl
                FROM
                    (SELECT @r := ?, @l := 0) vars,
                    cms_object h
                WHERE @r <> 0) T1
            JOIN      cms_object T2 ON T1._id = T2.id
            LEFT JOIN cms_page      ON cms_page.id = T2.id
            ORDER BY T1.lvl DESC
        ");

        $sth->bindValue(1, $object->id, PDO::PARAM_INT);

        $sth->execute();

        $tree = $sth->fetchAll();

        foreach ($tree as $tree_part) {

            array_push($paths, ($beautiful_paths && $tree_part["title_sef"]) ? $tree_part["title_sef"] : "e" . $tree_part["id"]);
        }

        return join("/", $paths);
    }

    public function get_page_elements_by_parent($id, $types=null) {

        $page_elements_array = array();

        if ($types) {
            $query = "SELECT id, type FROM cms_object
                WHERE type != \"cms_page\" AND type IN (" . join(",", $types) . ") AND parent = ? ORDER BY sort_id, id";
        }
        else {
            $query = "SELECT id, type FROM cms_object
                WHERE type != \"cms_page\" AND parent = ? ORDER BY sort_id, id";
        }

        $sth = $this->dbh->prepare($query);

        $sth->bindValue(1, $id, PDO::PARAM_INT);

        $sth->execute();

        $page_elements = $sth->fetchAll();

        foreach ($page_elements as $page_element) {

            $page_element_object = $this->get_page_element_by_id($page_element["id"], $page_element["type"]);

            if ($page_element_object) {

                if ($page_element_object->is_container()) { // if cms_container, get its page-elements
                    $page_element_object->page_elements = $this->get_page_elements_by_parent($page_element_object->id);
                }

                array_push($page_elements_array, $page_element_object);
            }
        }

        return $page_elements_array;
    }

    public function get_page_element_by_id($id, $type) {

        $sth = $this->dbh->prepare("
            SELECT $type.*,  cms_object.parent, cms_object.type, cms_object.changed_date, cms_object.changed_by
            FROM   $type
            INNER JOIN cms_object ON $type.id = cms_object.id
            WHERE $type.id = ?
        ");

        $sth->bindValue(1, $id, PDO::PARAM_INT);

        $sth->execute();

        $row = $sth->fetch();

        if ($row) {

            $classname = get_classname_by_type($type);

            return new $classname($row);
        }

        return null;
    }

    private function attribute_type_to_db_type($attribute_type) {

        switch ($attribute_type) {
            case "varchar":
                return PDO::PARAM_STR;
                break;
            case "text":
                return PDO::PARAM_STR;
                break;
            case "int":
                return PDO::PARAM_INT;
                break;
        }

        return null;
    }

    public function insert_object($object) {
        $object->changed_date = date("Y-m-d H:i:s");
        $object->changed_by   = $_SESSION["cms_user"]->name;

        try {

            $this->dbh->beginTransaction();

            $sth = $this->dbh->prepare("INSERT INTO cms_object (parent, type, changed_date, changed_by) VALUES (?, ?, ?, ?)");

            $sth->bindValue(1, $object->parent, PDO::PARAM_INT);
            $sth->bindValue(2, $object->type);
            $sth->bindValue(3, $object->changed_date);
            $sth->bindValue(4, $object->changed_by);

            if (!$sth->execute()) {
                throw new Exception("no insert");
            }

            $object->id = $this->dbh->lastInsertId();

            $func_map_parameter_names = function($parameter) { return $parameter["name"]; };

            $column_names = array_map($func_map_parameter_names, $object->attributes);

            array_push($column_names, "id");

            $param_list   = "(" . join(",", $column_names) . ")";
            $param_values = "(" . rtrim(str_repeat("?,", sizeof($column_names)), ",") . ")";

            $sth_data = $this->dbh->prepare("INSERT INTO " . $object->type . " " . $param_list . " VALUES " . $param_values);

            $sth_data = $this->bind_object_params($sth_data, $object);

            if (!$sth_data->execute()) {
                throw new Exception("no insert");
            }

            return $this->dbh->commit();
        }
        catch (Exception $e) {
            $this->dbh->rollBack();
        }

        return false;
    }

    public function update_object($object) {
        $object->changed_date = date("Y-m-d H:i:s");
        $object->changed_by   = $_SESSION["cms_user"]->name;

        try {

            $this->dbh->beginTransaction();

            $sth = $this->dbh->prepare("UPDATE cms_object SET changed_date = ?, changed_by = ? WHERE id = ?");

            $sth->bindValue(1, $object->changed_date);
            $sth->bindValue(2, $object->changed_by);
            $sth->bindValue(3, $object->id, PDO::PARAM_INT);

            if (!$sth->execute()) {
                throw new Exception("no update");
            }

            $func_map_parameter_names = function($parameter) { return $parameter["name"] . " = ?"; };

            $column_names = array_map($func_map_parameter_names, $object->attributes);

            $update_query = join(",", $column_names);

            $sth_data = $this->dbh->prepare("UPDATE " . $object->type . " SET " . $update_query . " WHERE id = ?");

            $sth_data = $this->bind_object_params($sth_data, $object);

            if (!$sth_data->execute()) {
                throw new Exception("no update");
            }

            return $this->dbh->commit();
        }
        catch (Exception $e) {
            $this->dbh->rollBack();
        }

        return false;
    }

    private function bind_object_params($sth, $object) {

        $i = 1;

        foreach ($object->attributes as $attribute) {

            $value = $object->$attribute["name"];

            if ($attribute["db_type"] == "text") $value = stripslashes($value);

            $sth->bindValue($i, $value, $this->attribute_type_to_db_type($attribute["db_type"]));

            $i++;
        }

        $sth->bindValue($i, $object->id, PDO::PARAM_INT);

        return $sth;
    }

    public function sort_objects($object_ids) {

        try {

            $this->dbh->beginTransaction();

            $sth = $this->dbh->prepare("UPDATE cms_object SET sort_id = :sort_id WHERE id = :id");

            $sth->bindParam(":sort_id", $sort_id, PDO::PARAM_INT);
            $sth->bindParam(":id",      $id,      PDO::PARAM_INT);

            $sort_id = 1;

            foreach ($object_ids as $id) {

                $sth->execute();

                $sort_id++;
            }

            return $this->dbh->commit();
        }
        catch (Exception $e) {
            $this->dbh->rollBack();
        }

        return false;
    }

    public function delete($source, $id) {

        $sth = $this->dbh->prepare("DELETE FROM $source WHERE id = ?");

        $sth->bindValue(1, $id, PDO::PARAM_INT);

        return $sth->execute();
    }

    public function get_global_objects() {

        $global_objects_array = array();

        $sth = $this->dbh->prepare("SELECT * FROM cms_global_object");

        $sth->execute();

        $global_objects = $sth->fetchAll();

        foreach ($global_objects as $global_object) {

            array_push($global_objects_array, new CMSGlobalObject($global_object));
        }

        return $global_objects_array;
    }

    public function get_global_object_by_name($name) {

        $sth = $this->dbh->prepare("SELECT * FROM cms_global_object WHERE name = ?");

        $sth->execute(array( $name ));

        $row = $sth->fetch();

        if ($row) {

            return new CMSGlobalObject($row);
        }

        return null;
    }

    public function insert_global_object($object) {

        $sth = $this->dbh->prepare("INSERT INTO cms_global_object (name, object_id) VALUES (?, ?)");

        $sth->bindValue(1, $object->name,      PDO::PARAM_STR);
        $sth->bindValue(2, $object->object_id, PDO::PARAM_INT);

        return $sth->execute();
    }

    public function get_users() {

        $users_array = array();

        $sth = $this->dbh->prepare("SELECT * FROM cms_user");

        $sth->execute();

        $users = $sth->fetchAll();

        foreach ($users as $user) {

            $user_obj = new CMSUser($user);

            $this->get_user_nodes($user_obj);

            array_push($users_array, $user_obj);
        }

        return $users_array;
    }

    public function get_user_by_name($name) {

        $sth = $this->dbh->prepare("SELECT * FROM cms_user WHERE name = ?");

        $sth->execute(array( $name ));

        $row = $sth->fetch();

        if ($row) {

            $user = new CMSUser($row);

            $this->get_user_nodes($user);

            return $user;
        }

        return null;
    }

    public function get_user_nodes($user) {

        $sth = $this->dbh->prepare("SELECT id, node FROM cms_user_node WHERE user = ?");

        $sth->execute(array( $user->id ));

        $nodes = $sth->fetchAll();

        foreach ($nodes as $node) {

            array_push($user->nodes, array(
                "id"     => $node["id"],
                "object" => $this->get_object_by_id($node["node"], true)
            ));
        }
    }

    public function insert_user($object) {

        $sth = $this->dbh->prepare("INSERT INTO cms_user (name, password, role) VALUES (?, ?, ?)");

        $sth->bindValue(1, $object->name);
        $sth->bindValue(2, $object->password);
        $sth->bindValue(3, $object->role);

        return $sth->execute();
    }

    public function insert_user_node($object) {

        $sth = $this->dbh->prepare("INSERT INTO cms_user_node (user, node) VALUES (?, ?)");

        $sth->bindValue(1, $object->user);
        $sth->bindValue(2, $object->node);

        return $sth->execute();
    }

    /*
    public function get_search_results($search_phrase) {
        $search         = htmlentities($search_phrase);
        $search_results = array();
        $result         = mysql_query("SELECT DISTINCT id FROM cms_text WHERE text LIKE '%$search%'", $this->connection);

        if ($result) {
            while ($data = mysql_fetch_array($result, MYSQL_ASSOC)) {
                array_push($search_results, $this->get_object_by_id($data["id"], true));
            }
        }
        return $search_results;
    }*/

    public function get_dump() {

        return '';
    }
}

?>