<?php

require_once "../base.php";

is_logged_in();

if (!has_user_role("CMSAdministrator")) {
    redirect(uri_for("/manage"));
}

$template = new PHPTAL(path_for("/manage/root/templates/filesystem/index.tmpl"));

function file_info($file, $usage=null) {
    $changed          = date("Y-m-d H:i:s", filemtime($file));
    $shorten_filename = str_replace(get_document_root(), "", $file);
    $mode             = "application/xml";

    if ($usage == null) {
        if (preg_match('/.*\.css$/', $file)) {
            $usage = "Style";
            $mode  = "css";
        }
        elseif (preg_match('/.*\.js$/', $file)) {
            $usage = "Javascript";
            $mode  = "javascript";
        }
        elseif (preg_match('/\/(cms_.*)\/body_content.tmpl$/', $file, $matches)) {
            $usage = "Standardausgabe von " . get_classname_by_type($matches[1]);
        }
        elseif (preg_match('/\/custom\/(.*).tmpl$/', $file, $matches)) {
            $usage = "Indivduelle Ausgabe";
        }
    }

    return array(
        'name'       => $shorten_filename,
        'usage'      => $usage,
        'mode'       => $mode,
        'changed'    => $changed,
        'uri_manage' => uri_for("/manage/filesystem", array( 'file' => $shorten_filename ))
    );
}

if (isset($_POST["submit_file"])) {
    $file         = $_POST["file"];
    $file_content = $_POST["file_content"];

    file_put_contents(get_document_root() . $file, stripslashes($file_content));

    redirect(uri_for("/manage/filesystem"));
}
elseif (isset($_GET["file"]) && $file = $_GET["file"]) {

    $file_info = file_info(get_document_root() . $file);

    $template->file         = $file;
    $template->mode         = $file_info["mode"];
    $template->file_content = file_get_contents(get_document_root() . $file);
}
elseif (isset($_GET["create_template"]) && $id = $_GET["create_template"] and $type = $_GET["type"]) {
    $source      = path_for("/manage/root/templates/content/$type/body_content.tmpl"); 
    $destination = path_for("/manage/root/templates/custom/$id.tmpl");

    if (copy($source, $destination)) {
        redirect(uri_for("/manage/content", array( 'id' => $id )));
    }
}
elseif (isset($_GET["delete_template"]) && $id = $_GET["delete_template"]) {
    if (unlink(path_for("/manage/root/templates/custom/$id.tmpl"))) {
        redirect(uri_for("/manage/content", array( 'id' => $id )));
    }
}
else {
    $template->files = array_merge(
        array_map("file_info", find_files(path_for("/static/css"), "*.css")),
        array_map("file_info", array( path_for("/index.tmpl") ), array( "Standardausgabe" )),
        array_map("file_info", find_files(path_for("/manage/root/templates/custom"), "*.tmpl")),
        array_map("file_info", find_files(path_for("/manage/root/templates"), "body_content.tmpl")),
        array_map("file_info", find_files(path_for("/static/scripts"), "*.js"))
    );
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