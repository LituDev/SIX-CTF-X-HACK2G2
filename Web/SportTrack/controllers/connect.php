<?php
require_once(__ROOT__ . '/controllers/Controller.php');
require_once(__ROOT__ . '/model/database/UtilisateurDAO.php');

session_start();

class ConnectController extends Controller{

    public function get($request){
        if(isset($_SESSION["email"])){
            header("Location: /activities");
            exit;
        }
        $this->render('connect_form',[]);
    }

    public function post($request){
        if(!isset($request['email']) || !isset($request['password'])){
            $this->render('connect_form', ['error' => 'Veuiller remplir tout les champs']);
            return;
        }
        $user = UtilisateurDAO::getInstance()->get($request['email']);
        if($user === null) {
            $this->render('connect_form', ['error' => 'Utilisateur inconnu']);
        }else{
            if(!password_verify($request['password'], $user->getPasswordHash())){
                $this->render('connect_form', ['error' => 'Mot de passe incorrect']);
            }else{
                $_SESSION['email'] = $user->getEmail();
                header('Location: /activities');
            }
        }
    }
}

?>
