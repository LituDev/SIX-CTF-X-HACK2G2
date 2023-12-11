<?php 

$pdo = new PDO('mysql:host=mysql;dbname=test;charset=utf8', 'test', 'test');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->query("CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    password VARCHAR(30) NOT NULL
)");
$pdo->query("CREATE TABLE IF NOT EXISTS messages (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255) NOT NULL,
    user_id INT(6) NOT NULL
)");

$pdo->query("INSERT INTO users (username, password) VALUES ('admin', 'admin')");
$pdo->query("INSERT INTO messages (message, user_id) VALUES ('IUT{L0g_C4N_bE_Us3FuL_sOmE_T1MEs}', 1)");