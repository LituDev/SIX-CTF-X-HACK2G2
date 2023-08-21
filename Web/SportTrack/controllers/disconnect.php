<?php
require_once(__ROOT__ . '/controllers/Controller.php');

session_start();

class DisconnectUserController extends Controller {
    public function get($request) {
        unset($_SESSION['email']);
        header('Location: /');
        exit;
    }
}
