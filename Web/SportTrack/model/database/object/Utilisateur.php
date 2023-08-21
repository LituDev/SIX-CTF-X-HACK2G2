<?php

class Utilisateur {
    private string $nom;
    private string $prenom;
    private string $email;
    private string $sexe;
    private int $taille;
    private int $poids;
    private string $password_hash;
    private string $birthDate;

    public function __construct(){ }

    public function init(string $nom, string $prenom, string $email, string $sexe, int $taille, int $poids, string $password_hash, string $birthDate): void{
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->sexe = $sexe;
        $this->taille = $taille;
        $this->poids = $poids;
        $this->password_hash = $password_hash;
        $this->birthDate = $birthDate;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getSexe(): string {
        return $this->sexe;
    }

    public function getTaille(): int {
        return $this->taille;
    }

    public function getPoids(): int {
        return $this->poids;
    }

    public function getPasswordHash(): string {
        return $this->password_hash;
    }

    public function getBirthDate(): string {
        return $this->birthDate;
    }

    public function __toString() : string{
        return $this->nom . " " . $this->prenom;
    }
}