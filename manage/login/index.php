<?php

require_once "../base.php";

//error_reporting(-1);

$template = new PHPTAL(path_for("/manage/root/templates/login.tmpl"));

if (isset($_POST["submit"])) {
    $user = DB::get_instance()->get_user_by_name($_POST["user"]);
    if ($user) {
        if (authenticate($user)) {
            redirect(uri_for("/manage"));
        }
        else {
             $template->message = get_error_message(array( "Authentifizierung fehlgeschlagen" ));
        }
    }
    else {
        $template->message = get_error_message(array( "Benutzer nicht gefunden" ));
    }
}

$template->uri_static = uri_for("/manage/root/static");
$template->name       = $name;

try {
    header("Content-Type:text/html;charset=utf-8");
    echo $template->execute();
}
catch (Exception $e){
    echo $e;
}

?>