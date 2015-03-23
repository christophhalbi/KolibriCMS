<?php

require_once "../base.php";

if (session_id() == '') {
    session_start();
}
session_destroy();

redirect(uri_for("/manage"));

?>