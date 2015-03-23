<?php

require_once "../base.php";

is_logged_in();

if (!has_user_role("CMSAdministrator")) {
    redirect(uri_for("/manage"));
}

$template = new PHPTAL(path_for("/manage/root/templates/global_objects/index.tmpl"));

$template->global_objects = DB::get_instance()->get_global_objects();

if (isset($_POST["submit_global_object"])) {
    $object = new CMSGlobalObject(array( 'name' => $_POST["name"], 'object_id' => $_POST["object_id"] ));

    $errors = $object->validate();

    if (count($errors) == 0) {
        if (DB::get_instance()->insert_global_object($object)) {
            redirect(uri_for("/manage/global_objects"));
        }
    }
    else {
        $template->message = get_error_message($errors);
    }
}
elseif (isset($_GET["delete"]) && $id = $_GET["delete"]) {
    if (DB::get_instance()->delete("cms_global_object", $id)) {
        redirect(uri_for("/manage/global_objects"));
    }
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