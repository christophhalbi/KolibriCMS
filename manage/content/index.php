<?php

require_once "../base.php";

is_logged_in();

$template = new PHPTAL(path_for("/manage/root/templates/content/index.tmpl"));

$is_editor = has_user_role("CMSEditor");

if ($is_editor) {
    $tree                = DB::get_instance()->get_object_by_id(1, true);
    $tree->page_children = DB::get_instance()->get_pages_tree  ();
}
else {
    $tree = DB::get_instance()->get_pages_tree();
}

$template->content = "";
$template->tree    = ($is_editor)
    ? restrict_tree($tree)
    : $tree;

if (isset($_GET["insert_object"]) && $page_element_type = $_GET["insert_object"]) {
    $classname = get_classname_by_type($page_element_type);
    $object    = new $classname();
    $template->content = $object->get_management_interface();

    if (isset($_POST["submit"])) {
        unset($_POST["submit"]);

        $object->set_attributes($_POST);
        $object->parent = $_GET["parent"];
        $object->type   = $_GET["insert_object"];

        $object->prepare();
        $errors = $object->validate();

        if (count($errors) == 0) {
           if (DB::get_instance()->insert_object($object)) {
                $object->write_filesystem();
                if ($object->is_page() || $object->is_container()) {
                    redirect(uri_for("/manage/content", array( 'id' => $object->id )));
                }
                else {
                    redirect(uri_for("/manage/content", array( 'id' => $object->parent )));
                }
            }
        }
        else {
            $template->message = get_error_message($errors);
        }
    }
}
elseif (isset($_GET["id"]) && $id = $_GET["id"]) {
    $object                = DB::get_instance()->get_object_by_id   ($id);
    $object->page_children = DB::get_instance()->get_pages_by_parent($id);

    $has_node_access = (has_user_role("CMSEditor"))
        ? has_node_access($object)
        : true;

    if ($has_node_access) {
        $template->content = $object->get_management_interface();
    }
    else {
        $template->message = get_error_message(array( "keine Berechtigung" ));
    }

    if (isset($_POST["submit"])) {
        unset($_POST["submit"]);
        $object->set_attributes($_POST);

        $object->prepare();
        $errors = $object->validate();

        if (count($errors) == 0) {
            if (DB::get_instance()->update_object($object)) {
                $object->update_filesystem();
                if ($object->is_page() || $object->is_container()) {
                    redirect(uri_for("/manage/content", array( 'id' => $object->id )));
                }
                else {
                    redirect(uri_for("/manage/content", array( 'id' => $object->parent )));
                }
            }
        }
        else {
            $template->message = get_error_message($errors);
        }
    }
}
elseif (isset($_GET["delete"]) && $id = $_GET["delete"]) {
    $object = DB::get_instance()->get_object_by_id($id);

    $object->delete_filesystem();

    DB::get_instance()->delete("cms_object", $object->id);

    redirect(uri_for("/manage/content", array( 'id' => $object->parent )));
}
elseif (isset($_POST["manage_tree"])) {
    $parent_id = ($_POST["parent"]) ? $_POST["parent"] : 1;

    $template_tree = new PHPTAL(path_for("/manage/root/templates/content/tree.tmpl"));

    $template_tree->pages         = DB::get_instance()->get_pages_by_parent($parent_id);
    $template_tree->page_elements = (isset($_POST["only_pages"]))
        ? array()
        : DB::get_instance()->get_page_elements_by_parent($parent_id);

    try {
        header("Content-Type:text/xml;charset=utf-8");
        echo $template_tree->execute();
    }
    catch (Exception $e){
        echo $e;
    }
    return;
}
elseif (isset($_POST["manage_sort"])) {

    DB::get_instance()->sort_objects(explode(";", $_POST["objects"]));

    header("Content-Type:text/xml;charset=utf-8");
    echo "<html><head></head><body></body></html>";

    return;
}

$template->uri_static = uri_for("/manage/root/static");
$template->uri_base   = uri_for("");
$template->navigation = get_navigation();
$template->user       = get_current_cms_user();
$template->name       = $name;

try {
    header("Content-Type:text/html;charset=utf-8");
    echo $template->execute();
}
catch (Exception $e){
    echo $e;
}

?>