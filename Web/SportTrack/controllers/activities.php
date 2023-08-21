<?php

require __ROOT__ . "/controllers/Controller.php";
require __ROOT__ . "/model/database/ActivityDAO.php";

session_start();

class ListActivityController extends Controller {
    public function get($request) {
        if(!isset($_SESSION["email"])){
            header("Location: /connect");
            exit;
        }
        $activities = ActivityDAO::getInstance()->getAllActivity($_SESSION['email']);
        $this->render("activities", ["activities" => $activities]);
    }

}
