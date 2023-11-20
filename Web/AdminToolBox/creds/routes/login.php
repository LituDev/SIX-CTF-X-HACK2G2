<?php

if(isset($_GET["username"]) && isset($_GET["password"])) {
    $username = $_GET["username"];
    $password = $_GET["password"];

    if($username === "admin" && password_verify($_ENV["ADMIN_HASH_PASSWORD"], password_hash($password, PASSWORD_BCRYPT))) {
        $content = [
            "username" => $username,
            "admin" => true,
            "FLAG" => $_GET["FLAG"] ?? "fake"
        ];
    }else{
        $content = [
            "username" => $username
        ];
    }
    $token = \Firebase\JWT\JWT::encode($content, $_ENV["JWT_SECRET"], "HS256");

    http_response_code(200);
    echo json_encode(array("token" => $token));
} else {
    http_response_code(400);
    echo json_encode(array("error" => "Missing credentials, please provide username and password"));
}