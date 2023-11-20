<?php

require_once __DIR__ . '/vendor/autoload.php';

header("Access-Control-Allow-Origin: *");

$_ENV["JWT_SECRET"] = "the_jwt_secret_from_env";
$_ENV['ADMIN_PASSWORD'] = '$2y$10$F4.bFZTB7oD2IvHLCjFwC.qSlKfvk5T3gSoqHP8A1z9eWfGT8tzg2';

function request_path(){
    return $_GET["route"] ?? null;
}

$availableRoutes = glob(__DIR__ . "/routes/*.php");
$availableRoutes = array_map(function($route) {
    return str_replace([__DIR__ . "/routes/",".php"], "", $route);
}, $availableRoutes);

$path = request_path();
if($path == null) {
    http_response_code(404);
    echo json_encode(array("error" => "Invalid request", "routes" => $availableRoutes));
    exit();
}
$routePath = __DIR__ . "/routes/" . $path;
$globRet = glob($routePath . "*");
if(count($globRet) === 0){
    http_response_code(404);
    echo json_encode(array("error" => "Invalid request", "routes" => $availableRoutes));
    exit();
}
$routePath = $globRet[0];
if(file_exists($routePath)) {
    require_once $routePath;
    exit();
} else {
    http_response_code(404);
    echo json_encode(array("error" => "Invalid request", "routes" => $availableRoutes));
    exit();
}