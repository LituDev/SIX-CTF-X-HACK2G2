<?php

namespace App;

use App\Repository\contracts\DatabaseTypes;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\Request;

class AuthManager {
    public function isConnected(Request $request) : bool {
        $puntoToken = $request->cookies->get("punto_token");

        if ($puntoToken === null) {
            return false;
        }

        return $this->getId($puntoToken) !== null;
    }

    public function connected(Request $request) : ?string {
        $puntoToken = $request->cookies->get("punto_token");

        if ($puntoToken === null) {
            return null;
        }

        return $this->getId($puntoToken);
    }

    public function store(string $id, DatabaseTypes $types, ?string $partyId = null, bool $granted = false) : string {
        return JWT::encode([
            "id" => $id,
            "partyId" => $partyId,
            "database" => $types->value,
            "granted" => $granted
        ], $_ENV["JWT_SECRET"], "HS256", "punto");
    }

    public function getId(string $token) : ?string {
        return $this->getContent($token)?->id;
    }

    public function getPartyId(string $token) : ?string {
        return $this->getContent($token)?->partyId;
    }

    public function getDatabaseType(string $token) : ?DatabaseTypes {
        $database = $this->getContent($token)?->database;
        if ($database === null) {
            return null;
        }
        return DatabaseTypes::tryFrom($database);
    }

    public function getContent(string $token) : ?\stdClass{
        $headers = $this->headers();
        try {
            return JWT::decode($token, $this->key(), $headers);
        } catch (\Exception $e){
            return null;
        }
    }

    public function content(Request $request) : ?\stdClass{
        $puntoToken = $request->cookies->get("punto_token");
        if ($puntoToken === null) {
            return null;
        }
        return $this->getContent($puntoToken);
    }

    private function key() : Key{
        return new Key($_ENV["JWT_SECRET"], "HS256");
    }

    private function headers() : \stdClass {
        $class = new \stdClass();
        $class->typ = "JWT";
        $class->alg = "HS256";
        $class->kid = "punto";
        return $class;
    }
}
