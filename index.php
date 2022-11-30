<?php 
$title="Page d'accueil";
require_once 'header.php';

if(!empty($_POST) && !empty($_POST['username']) && !empty($_POST['password'])){
    //Connexion à la BDD
    require_once 'loginBDD.php';
    $connexion = new mysqli($hn, $un, $pw, $db);
    if ($connexion->connect_error) {
        die ('Erreur Fatale');
    }

    //Sélection de l'utilisateur correspondant au pseudo ou email
    $username=$_POST['username'];
    $password=$_POST['password'];
    $queryUser="SELECT * FROM users WHERE (username='$username' OR email='$username') AND confirmed_at IS NOT NULL";
    $resultUser=mysqli_query($connexion, $queryUser);
    $user=mysqli_fetch_assoc($resultUser);
    extract($user);

    //Vérification du mot de passe et connexion de l'utilisateur
    if(password_verify($_POST['password'], $user['password'])){
        session_start();
        $_SESSION['auth']=$user;
        $_SESSION['flash']['succes']="Vous êtes maintenant connecté";
        header('location: index.php');
        exit();
    } else {
        session_start();
        $_SESSION['flash']['error']="Identifiant ou mot de passe incorrect";
        header('location: index.php');
        exit();
    }
}?>

<p>Bienvenue sur votre application personnelle de gestion de budget.</p>
<p>Veuillez entrer votre identifiant et votre mot de passe</p>
<form action="" method="post" class="index-form">
    <div class="identification">
        <label for="username" class="identifiant">Votre identifiant</label>
        <br>
        <input type="text" name="username" id="username" placeholder="Pseudo ou email" >
    </div>
    <div class="identification">
        <label for="password" class="motDePasse">Votre mot de passe</label>
        <br>
        <input type="password" name="password" id="password" placeholder="Mot de passe" >
    </div>
    <div class="divButton">
        <button class="button" type="submit" value="valider">Valider</button>
    </div>
</form>
<p>Si vous n'avez pas encore de compte, cliquez sur le lien ci-dessous pour créer un compte.</p>
<a href="register.php">S'inscrire</a>
<br>
<p>Pour modifier votre mot de passe, cliquez sur le lien ci-dessous</p>
<a href="passwordChange.php">Modifier le mot de passe</a>
<br>
<p>Si vous avez oublié votre mot de passe, cliquez sur le lien ci-dessous pour le réinitialiser</p>
<a href="forget.php">Mot de passe oublié</a>

<?php require_once 'footer.php';?>
