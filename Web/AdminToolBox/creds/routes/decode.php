<?php

if(isset($_GET["token"])) {
    $token = $_GET["token"];
    $key = new \Firebase\JWT\Key($_ENV["JWT_SECRET2"], "HS256");
    try{
        $payload = \Firebase\JWT\JWT::decode($token, $key);
        http_response_code(200);
        echo json_encode(array("payload" => $payload));
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array("error" => "Invalid token"));
        exit();
    }
} else {
    http_response_code(400);
    echo json_encode(array("error" => "Missing token parameter"));
}
