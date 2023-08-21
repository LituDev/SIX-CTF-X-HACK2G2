<?php

require_once dirname(__FILE__, 2).'/controllers/Controller.php';
require_once __ROOT__.'/model/database/UtilisateurDAO.php';
require_once __ROOT__.'/model/database/ActivityDAO.php';

session_start();

class UploadActivityController extends Controller {

    public function get($request) {
        $this->render("upload", []);
    }

    public function post($request) {
        if(count($_FILES) == 0){
            $this->render("upload", ["error" => "No file selected"]);
            return;
        }
        $file = $_FILES["file"]["tmp_name"];
        $content = json_decode(file_get_contents($file), true);
        if($content === null){
            $this->render("upload", ["error" => "Invalid file"]);
            return;
        }
        echo "<pre>";
        if(isset($content["activity"], $content["activity"]["date"], $content["activity"]["description"], $content["data"]) && is_array($content["data"])){
            $rawActivity = $content["activity"];
            $user = UtilisateurDAO::getInstance()->get($_SESSION["email"]);
            if($user === null){
                $this->render("upload", ["error" => "User not found"]);
                return;
            }
            $mesures = [];
            foreach ($content["data"] as $rawMesure) {
                if(isset($rawMesure["time"], $rawMesure["cardio_frequency"], $rawMesure["latitude"], $rawMesure["longitude"], $rawMesure["altitude"])){
                    $mesure = new Mesure();
                    $mesure->init(0, $rawMesure["time"], $rawMesure["cardio_frequency"], $rawMesure["longitude"], $rawMesure["latitude"], $rawMesure["altitude"], null);
                    $mesures[] = $mesure;
                }
            }
            $activity = new Activity();
            $activity->init(0, $rawActivity["description"], $user, $rawActivity["date"], $mesures);
            if(ActivityDAO::getInstance()->insert($activity)){
                header("Location: /activities");
                exit;
            }else{
                $this->render("upload", ["error" => "Error while inserting activity"]);
                return;
            }
        } else {
            $this->render("upload", ["error" => "Invalid file"]);
        }
        echo "</pre>";
    }

}
