<?php

$document_root = $_SERVER["DOCUMENT_ROOT"];
$dir           = "";
$uri_base      = "http://" . $_SERVER["SERVER_NAME"];
$name          = "foo.at";

$beautiful_paths = false;

require_once $document_root . $dir . "/manage/lib/View/PHPTAL.php";
require_once $document_root . $dir . "/manage/lib/Model/DB.php";

require_once $document_root . $dir . "/manage/lib/Model/CMSGlobalObject.php";
require_once $document_root . $dir . "/manage/lib/Model/CMSUser.php";
require_once $document_root . $dir . "/manage/lib/Model/CMSUserNode.php";

require_once $document_root . $dir . "/manage/content/CMSObject.php";
require_once $document_root . $dir . "/manage/content/CMSPage.php";
require_once $document_root . $dir . "/manage/content/CMSContainer.php";
require_once $document_root . $dir . "/manage/content/CMSFile.php";
require_once $document_root . $dir . "/manage/content/CMSImage.php";
require_once $document_root . $dir . "/manage/content/CMSLink.php";
require_once $document_root . $dir . "/manage/content/CMSText.php";
require_once $document_root . $dir . "/manage/content/CMSNews.php";
require_once $document_root . $dir . "/manage/content/CMSMultimedia.php";
require_once $document_root . $dir . "/manage/content/CMSForm.php";

date_default_timezone_set("Europe/Berlin");

function get_classname_by_type($type) {
    $object_types = array(
        'cms_page'       => "CMSPage",
        'cms_container'  => "CMSContainer",
        'cms_file'       => "CMSFile",
        'cms_image'      => "CMSImage",
        'cms_link'       => "CMSLink",
        'cms_text'       => "CMSText",
        'cms_news'       => "CMSNews",
        'cms_multimedia' => "CMSMultimedia",
        'cms_form'       => "CMSForm",
    );
    return $object_types[$type];
}

function get_object_types() {
    return array(
        array( 'value' => "cms_text",       'name' => "Text",       'page_element' => true  ),
        array( 'value' => "cms_link",       'name' => "Link",       'page_element' => true  ),
        array( 'value' => "cms_image",      'name' => "Abbildung",  'page_element' => true  ),
        array( 'value' => "cms_file",       'name' => "Datei",      'page_element' => true  ),
        array( 'value' => "cms_news",       'name' => "News",       'page_element' => true  ),
        array( 'value' => "cms_multimedia", 'name' => "Multimedia", 'page_element' => true  ),
        array( 'value' => "cms_form",       'name' => "Formular",   'page_element' => true  ),
        array( 'value' => "cms_page",       'name' => "Seite",      'page_element' => false ),
        array( 'value' => "cms_container",  'name' => "Container",  'page_element' => false )
    );
}

function get_user_roles() {
    return array(
        "CMSAdministrator",
        "CMSEditor"
    );
}

function get_message($type="error", $head="Fehler", $notifications=array()) {

    return array(
        'type'          => $type,
        'head'          => $head,
        'notifications' => $notifications
    );
}

function get_error_message($errors) {
    return get_message("error", "Fehler", $errors);
}

function get_navigation() {
    $navigation       = array();
    $is_administrator = has_user_role("CMSAdministrator");

    array_push($navigation, array( 'href' => uri_for("/manage"),                             'name' => "Start",   'class_name' => "" ));
    array_push($navigation, array( 'href' => uri_for("/manage/content", array( 'id' => 1 )), 'name' => "Inhalte", 'class_name' => "" ));

    if ($is_administrator) {
        array_push($navigation, array( 'href' => uri_for("/manage/global_objects"), 'name' => "Globale Objekte", 'class_name' => "" ));
        array_push($navigation, array( 'href' => uri_for("/manage/filesystem"),     'name' => "Templates",       'class_name' => "" ));
        array_push($navigation, array( 'href' => uri_for("/manage/users"),          'name' => "Benutzer",        'class_name' => "" ));
        array_push($navigation, array( 'href' => uri_for("/manage/system"),         'name' => "System",          'class_name' => "" ));
    }

    array_push($navigation, array( 'href' => uri_for("/"),              'name' => "Vorschau", 'class_name' => "" ));
    array_push($navigation, array( 'href' => uri_for("/manage/logout"), 'name' => "Logout",   'class_name' => "logout" ));

    return $navigation;
}

function get_current_cms_user() {
    if (session_id() == '') {
        session_start();
    }
    return isset($_SESSION["cms_user"]) ? $_SESSION["cms_user"] : null;
}

function is_logged_in() {
    if (session_id() == '') {
        session_start();
    }
    if (!isset($_SESSION["cms_user"])) {
        redirect(uri_for("/manage/login"));

        return false;
    }
    return true;
}

function authenticate($user) {
    if (session_id() == '') {
        session_start();
    }

    if (crypt($_POST["password"], $user->password) == $user->password) {

        $_SESSION["cms_user"] = $user;

        return true;
    }
    else {
        return false;
    }
}

function has_user_role($role) {
    if (is_logged_in()) {
        if (strcmp($_SESSION["cms_user"]->role, $role) == 0) {
            return true;
        }
        else {
            return false;
        }
    }
}

function has_node_access($node) {
    $allowed_nodes = array();
    $node_paths    = explode("/", $node->get_path());

    foreach ($_SESSION["cms_user"]->nodes as $user_node) {
        array_push($allowed_nodes, $user_node["object"]->get_dcid());
    }

    foreach ($allowed_nodes as $allowed_node) {
        if (preg_grep('/' . $allowed_node . '$/', $node_paths)) {
            return true;
        }
    }
    return false;
}

function restrict_tree($tree) {
    if (has_node_access($tree))
        return array( $tree );

    $tree_nodes = array();

    foreach ($tree->page_children as $tree_node) {
        $tree_nodes = array_merge($tree_nodes, restrict_tree($tree_node));
    }
    return $tree_nodes;
}

function uri_for($path, $args=array()) {
    $uri = $GLOBALS["uri_base"] . $GLOBALS["dir"] . $path;

    for ($i = 0; $i < count($args); $i++) {
        $key = key($args);
        $uri .= (($i == 0) ? "?" : "&") . $key . "=" . $args[$key];
        next($args);
    }
    return $uri;
}

function path_for($path) {
    return get_document_root() . $path;
}

function get_document_root() {
    return $GLOBALS["document_root"] . $GLOBALS["dir"];
}

function redirect($url) {
    header("Location:$url");
    exit;
}

function find_files($dir, $pattern) {
    // get a list of all matching files in the current directory
    $files = glob("$dir/$pattern");
    if ($files == false)
        $files = array();
    // find a list of all directories in the current directory
    // directories beginning with a dot are also included
    $sub_dirs = glob("$dir/{.[^.]*,*}", GLOB_BRACE|GLOB_ONLYDIR);
    if ($sub_dirs) {
        foreach ($sub_dirs as $sub_dir){
            $arr = find_files($sub_dir, $pattern);  // resursive call
            if (count($arr) > 0) {
                $files = array_merge($files, $arr); // merge array with files from subdirectory
            }
        }
    }
    // return all found files
    return ($files) ? $files : array();
}

function clear_dir($dir) {
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != "..") {
                    unlink($dir . "/" . $file);
                }
            }
            closedir($dh);
        }
    }
}

function remove_dir($dir, $empty=false) {
    if (substr($dir, -1) == "/") {
        $dir = substr($dir, 0, -1);
    }
    if (!file_exists($dir) || !is_dir($dir)) {
        return false;
    } 
    elseif (is_readable($dir)) {
        $handle = opendir($dir);
        while (false != ($item = readdir($handle))) {
            if ($item != "." && $item != "..") {
                $path = $dir. "/" . $item;
                if (is_dir($path)) {
                    remove_dir($path);
                }
                else {
                    unlink($path);
                }
            }
        }
        closedir($handle);
        if ($empty == false) {
            if (!rmdir($dir)) {
                return false;
            }
        }
    }
    return true;
}

?>