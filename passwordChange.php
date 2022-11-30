<?php 
require_once 'functions.php';
connected();
$title="Modifier le mot de passe";

//connexion bdd
require_once 'loginBDD.php';
$connexion = new mysqli($hn, $un, $pw, $db);
if ($connexion->connect_error) {
    die ('Erreur Fatale');
}

//Si des données sont postées
if(!empty($_POST)){
    //Si le mot de passe est vide ou si les deux mots de passe ne correspondent pas
    if(empty($_POST['password']) || $_POST['password'] != $_POST['password_confirm']){
        $_SESSION['flash']['error']="Les mots de passe ne correspondent pas";
    } else {
        //Sinon modifier le mot de passe de l'utilisateur
        $user_id=$_SESSION['auth']['idUser'];
        $password= password_hash($_POST['password'], PASSWORD_BCRYPT);

        $queryChange = "UPDATE users SET password = '$password' WHERE idUser = '$user_id'" ;
        $resultChange=$connexion->query($queryChange);
        if (!$resultChange) {
            echo "Echec de l'insertion";
        }
    }
}
require_once 'header.php';
?>

<h2>Bonjour <?=$_SESSION['auth']['username']?></h2>
<h3>Changer votre mot de passe</h3>
<form action="" method="POST" class="change-form">
    <div class="change"> 
        <label for="Votre nouveau mot de passe">Nouveau mot de passe</label><br>
        <input type="password" class="input-item" name="password" placeholder="Mot de passe">
    </div>
    <div class="change">
        <label for="Votre nouveau mot de passe">Confirmation de votre mot de passe</label><br>
        <input type="password" class="input-item" name="password_confirm" placeholder="Confirmation">
    </div>
    <div class="divButton">
        <button class="button" type="submit" value="valider">Valider</button>
    </div>
</form>

<?php require_once 'footer.php';?>

