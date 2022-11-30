<?php
    //connexion à la BDD
    require_once 'loginBDD.php';
    $connexion = new mysqli($hn, $un, $pw, $db);
    if ($connexion->connect_error) {
        die ('Erreur Fatale');
    }

    //récupération des données id et token depuis l'URL envoyé par mail
    $user_id = $_GET['id'];
    $token = $_GET['token'];

    //récupération des données utilisateur de la table users pour vérifier la similarité des token
    $queryRequete = "SELECT * FROM users WHERE idUser='$user_id'";
    $resultRequete = mysqli_query($connexion, $queryRequete);
    $user=mysqli_fetch_assoc($resultRequete);
    extract($user);

    session_start();

    //vérification de la concordance des données BDD et utilisateur
    if($user && $user['confirmation_token']=$token){
        $queryToken = "UPDATE users SET confirmation_token=NULL, confirmed_at=NOW() WHERE idUser='$user_id'";
        $resultToken = $connexion->query($queryToken);
        if(!$resultToken){
            die('Erreur Fatale');
        }
        $_SESSION['flash']['succes']="Votre compte a bien été validé";
        $_SESSION['auth']=$user;
        header ('location: index.php');
    }else{
        $_SESSION['flash']['error']="Ce lien n'est plus valide";
        header ('location: index.php');
    }
    