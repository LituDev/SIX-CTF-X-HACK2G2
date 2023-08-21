<?php

require_once(__ROOT__ . '/model/database/object/Utilisateur.php');
require_once(__ROOT__ . "/model/database/SqliteConnection.php");

class UtilisateurDAO {
    private static UtilisateurDAO $dao;

    private function __construct() { }

    public static function getInstance(): UtilisateurDAO {
        if (!isset(self::$dao)) {
            self::$dao = new UtilisateurDAO();
        }
        return self::$dao;
    }

    public final function get(string $email): ?Utilisateur{
        $db = SqliteConnection::getInstance()->getConnection();
        if ($db === null) {
            return null;
        }
        try {
            $query = $db->prepare("SELECT * FROM utilisateurs WHERE email = :email");
            if(is_bool($query)){
                return null;
            }
            $query->bindValue(':email', $email);
            if(!$query->execute()){
                return null;
            }
            $result = $query->fetch();
            if($result === false){
                return null;
            }
            $user = new Utilisateur();
            $user->init($result['nom'], $result['prenom'], $result['email'], $result['sexe'], $result['taille'], $result['poids'], $result['password_hash'], $result['birthDate']);
            return $user;
        }catch(\PDOException $e){
            var_dump($e->getMessage());
            return null;
        }
    }

    /** @return Utilisateur[] */
    public final function findAll(): array {
        $db = SqliteConnection::getInstance()->getConnection();
        if($db === null){
            return [];
        }
        try {
            $query = $db->prepare("SELECT * FROM utilisateurs ORDER BY nom, prenom");
            if(is_bool($query)){
                return [];
            }
            if($query->execute()){
                $result = $query->fetchAll(\PDO::FETCH_CLASS, Utilisateur::class);
                if(is_array($result)){
                    return $result;
                }else{
                    return [];
                }
            }else{
                return [];
            }
        }catch(\PDOException $e){
            var_dump($e);
            return [];
        }
    }

    public final function insert(Utilisateur $user) : bool {
        $db = SqliteConnection::getInstance()->getConnection();
        if($db === null){
            return false;
        }
        try {
            $query = $db->prepare("INSERT INTO utilisateurs(nom, prenom, email, sexe, taille, poids, password_hash, birthDate) VALUES (:nom, :prenom, :email, :sexe, :taille, :poids, :password_hash, :birthDate)");
            if(is_bool($query)){
                return false;
            }
            $query->bindValue(':nom', $user->getNom());
            $query->bindValue(':prenom', $user->getPrenom());
            $query->bindValue(':email', $user->getEmail());
            $query->bindValue(':sexe', $user->getSexe());
            $query->bindValue(':taille', $user->getTaille());
            $query->bindValue(':poids', $user->getPoids());
            $query->bindValue(':password_hash', $user->getPasswordHash());
            $query->bindValue(':birthDate', $user->getBirthDate());
            return $query->execute();
        }catch(\PDOException $e){
            var_dump($e->getMessage());
            return false;
        }
    }

    public function delete(Utilisateur $user) : bool {
        $db = SqliteConnection::getInstance()->getConnection();
        if($db === null){
            return false;
        }
        try {
            $query = $db->prepare("DELETE FROM utilisateurs WHERE email = :email");
            if(is_bool($query)){
                return false;
            }
            $query->bindValue(':email', $user->getEmail());
            return $query->execute();
        }catch (\PDOException $e){
            return false;
        }
    }

    public function update(Utilisateur $user) : bool {
        $db = SqliteConnection::getInstance()->getConnection();
        if($db === null){
            return false;
        }
        try {
            $query = $db->prepare("UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, sexe = :sexe, taille = :taille, poids = :poids, password_hash = :password_hash, birthDate = :birthDate WHERE email = :email");
            if(is_bool($query)){
                return false;
            }
            $query->bindValue(':nom', $user->getNom());
            $query->bindValue(':prenom', $user->getPrenom());
            $query->bindValue(':email', $user->getEmail());
            $query->bindValue(':sexe', $user->getSexe());
            $query->bindValue(':taille', $user->getTaille());
            $query->bindValue(':poids', $user->getPoids());
            $query->bindValue(':password_hash', $user->getPasswordHash());
            $query->bindValue(':birthDate', $user->getBirthDate());
            return $query->execute();
        }catch (\PDOException $e){
            return false;
        }
    }
}