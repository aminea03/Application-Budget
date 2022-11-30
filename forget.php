<?php 
require_once 'functions.php';
$title="Mot de passe oublié";
require_once 'header.php';

//Accès à la page "Mot de passe oublié interdit si utilisateur connecté
if ($_SESSION['auth']){
    $_SESSION['flash']['error']="Vous êtes connecté.\nVous ne pouvez pas accéder à cette page";
    header('location: index.php');
    exit();
}


//connexion bdd
require_once 'loginBDD.php';
$connexion = new mysqli($hn, $un, $pw, $db);
if ($connexion->connect_error) {
    die ('Erreur Fatale');
}

//Vérification de l'entrée d'un email pas l'utilisateur
if(!empty($_POST) && !empty($_POST['email'])){
    $email=$_POST['email'];
    //Sélection des données de l'utilisateur dans table users
    $queryForget = "SELECT * FROM users WHERE email='$email' AND confirmed_at IS NOT NULL";
    $resultForget=mysqli_query($connexion, $queryForget);
    $user=mysqli_fetch_assoc($resultForget);
    extract($user);
    //Si un email utilisateur est trouvé, on envoie un email avec lien avec idUser et reset_token
    if($user){
        session_start();
        $reset_token=str_random(60);
        $idUser = $user['idUser'];
        $queryReset = "UPDATE users SET reset_token = '$reset_token', reset_at = NOW() WHERE idUser= '$idUser'";
        $resultReset = mysqli_query($connexion, $queryReset);
        if($resultReset){
            if (mail($email, "Réinitiatilisation de votre mot de passe", "Afin de réinitialiser votre mot de passe merci de cliquer sur ce lien \n\n http://astoessel.alwaysdata.net/applicationBudget/reset.php?id=$idUser&token=$reset_token")) {
                $mail="http://astoessel.alwaysdata.net/applicationBudget/reset.php?id=$idUser&token=$reset_token";
                $_SESSION['flash']['succes']="Un mail de confirmation vous a été envoyé";
                header ('location: index.php');
                exit();
            }else{
                $_SESSION['flash']['error']="Une erreur s'est produite. L'email n'a pu être envoyé";
                header ('location: index.php');
                exit();
            }
        }else{
            $_SESSION['flash']['error'] = 'La requête n\'est pas passée';
            header ('location: index.php');
            exit();
        }
    }else{
        $_SESSION['flash']['error'] = 'Aucun compte ne correspond à cet email';
        header ('location: index.php');
        exit();
    }
} elseif (empty($_POST)) {
    $_SESSION['flash']['error']="Le champs ne peut pas être vide";
}


?>

<h3>Mot de passe oublié</h3>
<p>Pour réinitialiser votre mot de passe, merci d'entrer l'email utilisé pour votre compte</p>
<form action="" method="POST" class="forget-form">
    <div class="forget"> 
        <label for="email">Email</label><br>
        <input type="email" class="input-item" name="email" placeholder="pseudo@domain.com">
    </div>
    <div class="divButton">
        <button class="button" type="submit" value="valider">Valider</button>
    </div>
</form>

<?php require_once 'footer.php';?>