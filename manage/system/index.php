<?php

require_once "../base.php";

$make_backup = (isset($_GET["make_backup"])) ? true : false;

if (!$make_backup) {
    is_logged_in();
}

if (!$make_backup && !has_user_role("CMSAdministrator")) {
    redirect(uri_for("/manage"));
}

$template = new PHPTAL(path_for("/manage/root/templates/system/index.tmpl"));

function backup_files($dir, $zip) {
    $nodes = glob("$dir/{.[^.]*,*}", GLOB_BRACE);

    if ($nodes) {
        foreach ($nodes as $node) {
            // remove absolute part to get local name
            $local_name = str_replace(get_document_root() . "/", "", $node);

            if (is_dir($node)) {
                if (basename($node) != "manage") {
                    $zip->addEmptyDir($local_name);
                    backup_files($node, $zip);
                }
            }
            else {
                $zip->addFile($node, $local_name);
            }
        }
    }
}

function backup_info($file) {
    return array(
        'name'       => basename($file),
        'uri_delete' => uri_for("/manage/system", array( 'delete' => basename($file) )),
        'uri_backup' => uri_for("/manage/system/backups/" . basename($file))
    );
}

if ($make_backup) {
    $filename = "backup_" . date("Y-m-d_H-i-s") . ".zip";

    $zip = new ZipArchive();

    if ($zip->open(path_for("/manage/system/backups/") . $filename, ZIPARCHIVE::CREATE)!= true) {
        exit("cannot open <$filename>\n");
    }

    backup_files(get_document_root(), $zip);

    $zip->addFromString("data.sql", DB::get_instance()->get_dump());

    $zip->close();

    header("Content-Type:text/html;charset=utf-8");
    //echo "Backup $filename abgeschlossen";
    exit;
}
elseif (isset($_GET["delete"]) && $backup = $_GET["delete"]) {
    $success = unlink(path_for("/manage/system/backups/$backup"));
    if ($success) {
        redirect(uri_for("/manage/system"));
    }
}
else {
    $template->backups    = array_map("backup_info", find_files(path_for("/manage/system/backups"), "*.zip"));
    $template->uri_backup = uri_for("/manage/system/index.php", array( 'make_backup' => 1 ));
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