<?php

require_once "../../base.php";

is_logged_in();

$template = new PHPTAL(path_for("/manage/root/templates/content/plugins/pic_uploader/index.tmpl"));

$tmp_dir  = path_for("/manage/content/pic_uploader/tmp/");

$parent = $_GET["parent"];

function make_thumbnail($dir, $file_name, $percentage=null, $width=null, $height=null) {

    $image_details = getimagesize($dir . $file_name);

    $org_width  = $image_details[0];
    $org_height = $image_details[1];

    if ($percentage != null) {
        $width  = ($org_width  * $percentage) / 100;
        $height = ($org_height * $percentage) / 100;
    }

    if ($org_width > $org_height) {
        $new_width  = $width;
        $new_height = intval($org_height * $width  / $org_width );
    } else {
        $new_height = $height;
        $new_width  = intval($org_width  * $height / $org_height);
    }
    $dest_x = intval(($width  - $new_width ) / 2);
    $dest_y = intval(($height - $new_height) / 2);

    if ($image_details[2] == 1) {
        $imgt          = "ImageGIF";
        $imgcreatefrom = "ImageCreateFromGIF";
    }
    if ($image_details[2] == 2) {
        $imgt          = "ImageJPEG";
        $imgcreatefrom = "ImageCreateFromJPEG";
    }
    if ($image_details[2] == 3) {
        $imgt          = "ImagePNG";
        $imgcreatefrom = "ImageCreateFromPNG";
    }

    if ($imgt) {
        $old_image = $imgcreatefrom($dir . $file_name);
        $new_image = imagecreatetruecolor($width, $height);

        $new_image_path = $dir . "thumb_" .$file_name;

        imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $org_width, $org_height);
        $imgt($new_image,$new_image_path);

        return "thumb_" . $file_name;
    }

    return;
}

if (isset($_FILES["files"])) {

    clear_dir($tmp_dir);

    $count = 0;
    foreach ($_FILES["files"]["name"] as $file_name) {

        $ext = explode(".", $file_name);
        $ext = strtolower(end($ext));

        if (
               in_array($_FILES["files"]["type"][$count], array("image/gif", "image/jpeg", "image/jpg", "image/pjpeg", "image/x-png", "image/png"))
            && in_array($ext, array("gif", "jpeg", "jpg", "png"))
        ) {

            $thumbnail = false;

            move_uploaded_file($_FILES["files"]["tmp_name"][$count], $tmp_dir . $file_name);

            if ($_POST["scale_percentage"]
                || (
                       $_POST["scale_width"]
                    && $_POST["scale_height"]
                )
            ) {
                $thumbnail = make_thumbnail(
                    $tmp_dir,
                    $file_name,
                    $_POST["scale_percentage"],
                    $_POST["scale_width"],
                    $_POST["scale_height"]
                );
            }

            $object = new CMSImage();
            $object->parent     = $parent;
            $object->type       = "cms_image";
            $object->url_target = "_self";
            $object->align      = "left";
            $object->title      = $_POST["names"][$count];

            if ($thumbnail) {
                $object->image         = $thumbnail;
                $object->image_highres = $file_name;
            }
            else {
                $object->image = $file_name;
            }

            if (DB::get_instance()->insert_object($object)) {
                // create dir
                $path = $object->write_filesystem();

                if ($thumbnail) {
                    copy($tmp_dir . $thumbnail, $path . "/" . $thumbnail);
                    copy($tmp_dir . $file_name, $path . "/" . $file_name);
                }
                else {
                    copy($tmp_dir . $file_name, $path . "/" . $file_name);
                }
            }
        }

        $count++;
    }

    redirect(uri_for("/manage/content", array( 'id' => $parent )));
}

$template->uri_static = uri_for("/manage/root/static");
$template->name       = $name;

try {
    header("Content-Type:text/html;charset=utf-8");
    echo $template->execute();
}
catch (Exception $e) {
    echo $e;
}

?>