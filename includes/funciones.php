<?php

function debuguear($variable) : string {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// Escapa / Sanitizar el HTML
function s($html) : string {
    $s = htmlspecialchars($html);
    return $s;
}

function esUltimo(string $actual, string $proximo): bool {
    if($actual !== $proximo){
        return true;
    }
    return false;
}

//Funcion que revisa que el usuario este autenticado

function isAuth() : void {
    if (!isset($_SESSION['login'])){  //Si no esta definida esa variable de sesion 
        header('Location: /');         //Manda al usuario redireccionarse
    }
}

function isAdmin() : void {
    if(!isset($_SESSION['admin'])){
        header('Location: /');
    }
}

function isSession() : void {    //Funcion para evitar error al iniciar sesion
    if(!isset($_SESSION)) {
        session_start();
    }
}