<?php

require_once "base.php";

is_logged_in();

$template = new PHPTAL(path_for("/manage/root/templates/index.tmpl"));

$template->uri_static         = uri_for("/manage/root/static");
$template->uri_base           = uri_for("");
$template->uri_content        = uri_for("/manage/content", array( 'id' => 1 ));
$template->uri_global_objects = uri_for("/manage/global_objects");
$template->uri_filesystem     = uri_for("/manage/filesystem");
$template->uri_users          = uri_for("/manage/users");
$template->uri_system         = uri_for("/manage/system");
$template->navigation         = get_navigation();
$template->user               = get_current_cms_user();
$template->name               = $name;

try {
    header("Content-Type:text/html;charset=utf-8");
    echo $template->execute();
}
catch (Exception $e){
    echo $e;
}

?>