<?php

require (__ROOT__ . '/controllers/Controller.php');
require (__ROOT__ . "/model/database/UtilisateurDAO.php");

session_start();

class AddUserController extends Controller {
    public function get($request){
        $this->render('user_add',[]);
    }

    public function post($request){
        $labels = [
            "email" => "Email",
            "name" => "Nom",
            "prenom" => "Prénom",
            "sexe" => "Sexe",
            "height" => "Taille",
            "weight" => "Poids",
            "password" => "Mot de passe",
            "born" => "Date de naissance",
            "password_confirm" => "Confirmation du mot de passe"
        ];
        foreach (array_keys($labels) as $label) {
            if(!isset($request[$label])){
                $this->render('user_add', ['error' => "Veuillez remplir le champ $labels[$label]"]);
                return;
            }
        }
        try{
            $born = new DateTime($request["born"]);
            if($born->format("U") > (new DateTime())->format("U")){
                $this->render('user_add', ['error' => 'Date de naissance invalide']);
                return;
            }
        }catch(Exception $e){
            $this->render('user_add', ['error' => 'Date de naissance invalide']);
            return;
        }
        if($request["password"] !== $request["password_confirm"]){
            $this->render('user_add', ['error' => 'Les mots de passe ne correspondent pas']);
            return;
        }
        $user = UtilisateurDAO::getInstance()->get($request["email"]);
        if($user !== null){
            $this->render('user_add', ['error' => 'Email déjà utilisé']);
            return;
        }
        $user = new Utilisateur();
        $user->init(
            $request["name"],
            $request["prenom"],
            $request["email"],
            $request["sexe"],
            $request["height"],
            $request["weight"],
            password_hash($request["password"], PASSWORD_BCRYPT),
            $born->format("Y-m-d H:i:s")
        );
        if(!UtilisateurDAO::getInstance()->insert($user)){
            $this->render('user_add', ['error' => 'Erreur inconnue']);
            return;
        }else{
            $_SESSION["email"] = $user->getEmail();
            header("Location: /activities");
            exit;
        }
    }
}