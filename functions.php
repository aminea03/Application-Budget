<?php
//Fonction pour debug des variables
function debug($variable){
    echo '<pre>'.print_r($variable, true).'</pre>';
}
//Fonction de génération d'un token
function str_random($length){
    $alphabet="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    return substr(str_shuffle(str_repeat($alphabet, $length)), 0, $length);
}

//Fonction de vérification de connexion pour accès à la page demandée
function connected(){
    if(session_status()==PHP_SESSION_NONE){
        session_start();
    }
    if (!isset($_SESSION['auth'])){
        $_SESSION['flash']['error']="Vous ne pouvez pas accéder à cette page sans authentification";
        header('location: index.php');
        exit();
    }
}