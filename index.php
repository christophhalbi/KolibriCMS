<?php

require_once "manage/base.php";

$template = new PHPTAL(path_for("/index.tmpl"));

$path    = isset($_GET["path"]) ? $_GET["path"] : null;
$page_id = isset($_GET["id"])   ? $_GET["id"]   : 2;

if ($path) {
    $page = DB::get_instance()->get_object_by_path($path);
}
else if ($page_id) {
    $page = DB::get_instance()->get_object_by_id($page_id);
}

if ($page == null) { // HTTP 404
    $page = DB::get_instance()->get_object_by_id(1);

    header('HTTP/1.0 404 Not Found');

    $template_404 = new PHPTAL(path_for("/404.tmpl"));

    try {
        $template->spezialized_content = $template_404->execute();
    }
    catch (Exception $e){
        echo $e;
    }
}

$template->page       = $page;
$template->tree       = DB::get_instance()->get_pages_tree(true);
$template->uri_static = uri_for("/static");

try {
    header("Content-Type:text/html;charset=utf-8");
    echo $template->execute();
}
catch (Exception $e){
    echo $e;
}

?>