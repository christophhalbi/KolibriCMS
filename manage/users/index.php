<?php

require_once "../base.php";

is_logged_in();

if (!has_user_role("CMSAdministrator")) {
    redirect(uri_for("/manage"));
}

$template = new PHPTAL(path_for("/manage/root/templates/users/index.tmpl"));

$template->users = DB::get_instance()->get_users();;
$template->roles = get_user_roles();

if (isset($_POST["submit_user"])) {
    $object = new CMSUser(array( 'name' => $_POST["new_name"], 'password' => $_POST["new_password"], 'role' => $_POST["new_role"] ));

    $errors = $object->validate();

    if (count($errors) == 0) {
        if (DB::get_instance()->insert_user($object)) {
            redirect(uri_for("/manage/users"));
        }
    }
    else {
        $template->message = get_error_message($errors);
    }
}
elseif (isset($_GET["delete"]) && $id = $_GET["delete"]) {
    if (DB::get_instance()->delete("cms_user", $id)) {
        redirect(uri_for("/manage/users"));
    }
}
elseif (isset($_POST["submit_user_node"])) {
    $object = new CMSUserNode(array( 'user' => $_POST["user"], 'node' => $_POST["node"] ));

    $errors = $object->validate();

    if (count($errors) == 0) {
        if (DB::get_instance()->insert_user_node($object)) {
            redirect(uri_for("/manage/users"));
        }
    }
    else {
        $template->message = get_error_message($errors);
    }
}
elseif (isset($_GET["delete_user_node"]) && $id = $_GET["delete_user_node"]) {
    if (DB::get_instance()->delete("cms_user_node", $id)) {
        redirect(uri_for("/manage/users"));
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