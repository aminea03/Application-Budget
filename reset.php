<?php
    require_once 'header.php';

    //connexion à la BDD
    require_once 'loginBDD.php';
    $connexion = new mysqli($hn, $un, $pw, $db);
    if ($connexion->connect_error) {
        die ('Erreur Fatale');
    }

    //Vérification de la présence de données $_GET
    if(isset($_GET['id']) && isset($_GET['token'])){ 
        //récupération des données id et token depuis l'URL envoyé par mail
        $user_id = $_GET['id'];
        $token = $_GET['token'];

        //récupération des données utilisateur de la table users pour vérifier la similarité des token
        $queryRequete = "SELECT * FROM users WHERE idUser='$user_id' AND reset_token IS NOT NULL AND reset_token='$token' AND reset_at > DATE_SUB(NOW(), INTERVAL 60 MINUTE)";
        $resultRequete = mysqli_query($connexion, $queryRequete);
        $user=mysqli_fetch_assoc($resultRequete);
        extract($user);

        //Si un utilisateur correspond
        if($user){
            if(!empty($_POST)){ //Si des données ont été postées
                //Si le mot de passe est entré et que le deux mots de passe correspondent
                if(!empty($_POST['password']) && $_POST['password'] == $_POST['password_confirm']){
                    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                    //Mise à jour de la BDD
                    $queryPassword = "UPDATE users SET password = '$password', reset_at = NULL, reset_token = NULL";
                    $resultPassword = mysqli_query($connexion, $queryPassword);
                    session_start();
                    $_SESSION['flash']['success'] = 'Votre mot de passe a bien été modifié';
                    $_SESSION['auth'] = $user;
                    header('Location: index.php');
                    exit();
                }
            }
        } else {
            session_start();
            $_SESSION['flash']['error']="Ce token n'est plus valide";
            header('location: index.php');
            exit();    
        }
    }  else {
        header('location: index.php');
        exit();
    } 
?>

<h2>Bonjour <?=$_SESSION['auth']['username']?></h2>
<h3>Réinitialiser votre mot de passe</h3>

<form action="" method="POST" class="reset-form">
    <div class="reset"> 
        <label for="Votre nouveau mot de passe">Nouveau mot de passe</label><br>
        <input type="password" class="input-item" name="password" placeholder="Mot de passe">
    </div>
    <div class="reset">
        <label for="Votre nouveau mot de passe">Confirmation de votre mot de passe</label><br>
        <input type="password" class="input-item" name="password_confirm" placeholder="Confirmation">
    </div>
    <div class="divButton">
        <button class="button" type="submit" value="valider">Valider</button>
    </div>
</form>

    
<?php require_once 'footer.php';?>