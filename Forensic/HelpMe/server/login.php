<?php

// ini_set('display_errors', 0);


$pdo = new PDO('mysql:host=mysql;dbname=test;charset=utf8', 'test', 'test');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// init tables

try {
    if(isset($_GET['username']) && isset($_GET['password'])){
        $username = $_GET['username'];
        $password = $_GET['password'];
    
        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $query = $pdo->query($query);
        $user = $query->fetch(PDO::FETCH_ASSOC);
    
        if($user){
            echo true;
        }else{
            echo false;
        }
    } else {
        echo "Error";
    }
} catch (Exception $e) {
    echo "Error";
}