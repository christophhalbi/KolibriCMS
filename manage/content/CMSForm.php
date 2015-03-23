<?php

class CMSForm extends CMSObject {

    public $attributes = array(
        "config" => array( 'name' => "config", 'db_type' => "text", 'description' => "Konfiguration", 'obligatory' => true )
    );

    function __construct($data=array( 'type' => "cms_form", 'config' => "" )) {
        $this->set_standard_attributes($data);

        $this->data["config"]      = $data["config"];
        $this->data["config_json"] = json_decode($this->config, true);
    }

    public function get_title() {
        return "Formular";
    }

    public function get_description() {
        return "Formular";
    }

    public function get_body_content() {

        $template = new PHPTAL(path_for("/manage/root/templates/content/cms_form/body_content.tmpl"));

        $template->security_error = false;

        $fields = $this->config_json["fields"];

        $fields_array = array();
        $errors       = array();

        foreach (array_keys($fields) as $key) {

            $fields[$key]["name"]  = $key;
            $fields[$key]["value"] = $_POST[$key];
            $fields[$key]["error"] = false;
        }

        if ($_POST["submit_form_" . $this->id]) {

            foreach (array_keys($fields) as $key) {

                $validation_result = $this->validate_field($fields[$key]);

                if ($validation_result) {

                    array_push($errors, $validation_result);

                    $fields[$key]["error"] = true;
                }
            }

            $validation_result = $this->validate_security();

            if ($validation_result) {

                array_push($errors, $validation_result);

                $template->security_error = true;
            }

            if (!count($errors)) {

                return $this->perform_action($fields);
            }
        }

        if ($this->config_json["security"]["type"] == 'number_fun') {

            $template->security_type = 'number_fun';

            $security_number_1 = rand(1, 10);
            $security_number_2 = rand(1, 10);

            $template->number_1 = $security_number_1;
            $template->number_2 = $security_number_2;

            $template->security_result = $security_number_1 + $security_number_2;
        }

        foreach (array_keys($fields) as $key) {

            array_push($fields_array, $fields[$key]);
        }

        $template->self = $this;

        $template->fields = $fields_array;
        $template->errors = $errors;

        try {
            return $template->execute();
        }
        catch (Exception $e){
            return $e;
        }
    }

    private function validate_field($field) {

        if (!$field["required"] || ($field["required"] && $field["value"]))
            return;

        return 'Feld "' . $field["label"] .'" muss ausgefüllt werden.';
    }

    private function validate_security() {

        if ($_POST["security_expected_result"] == $_POST["security_result"])
            return;

        return 'Das Ergebnis von Rechenaufgabe muss angegeben werden.';
    }

    private function perform_action($fields) {

        $content = '<html><body><table border="0">';

        foreach (array_keys($fields) as $key) {

            $content .= "<tr><td>" . $fields[$key]["label"] . '</td><td>' . $fields[$key]["value"] . '</td></tr>';
        }

        $content .= '</table></body></html>';

        $header  = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";

        mail(
            $this->config_json["action"]["receiver"],
            $this->config_json["action"]["subject"],
            $content,
            $header
        );

        redirect(DB::get_instance()->get_object_by_id($this->config_json["action"]["redirect"])->get_href_to_index());
    }
}

?>