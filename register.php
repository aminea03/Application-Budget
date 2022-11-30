<?php
    $title="Inscription";
    session_start();
    //Accès à la page inscription interdit si utilisateur connecté
    if ($_SESSION['auth']){
        $_SESSION['flash']['error']="Vous déjà inscrit et connecté.\nVeuillez déconnecté l'utilisateur pour vous inscrire";
        header('location: index.php');
        exit();
    }

    //connexion à la BDD
    require_once 'loginBDD.php';
    $connexion = new mysqli($hn, $un, $pw, $db);
    if ($connexion->connect_error) {
        die ('Erreur Fatale');
    }
    require_once 'functions.php';

    if(!empty($_POST)){
        //création tableau pour stocker les erreurs
        $errors=array ();
        //Vérification de l'entrée d'un pseudo utilisateur valide
        if(empty($_POST['username']) || !preg_match('/^[a-zA-Z0-9]+$/', $_POST['username'])){
            $errors['username']="Votre pseudo n'est pas valide";
        }else{
            $username=$_POST['username'];
            $queryUsername="SELECT idUser FROM users WHERE username='$username'";
            $resultUsername=mysqli_query($connexion, $queryUsername);
            $user=mysqli_fetch_assoc($resultUsername);
            extract($user);
            if($user){
                $errors['username']="Ce pseudo est déjà utilisé";
            }
        }
        //Vérification de l'entrée d'un email utilisateur valide
        if(empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
            $errors['email']="Votre email n'est pas valide";
        }else{
            $email=$_POST['email'];
            $queryEmail="SELECT idUser FROM users WHERE email='$email'";
            $resultEmail=mysqli_query($connexion, $queryEmail);
            $userEmail=mysqli_fetch_assoc($resultEmail);
            extract($userEmail);
            if($userEmail){
                $errors['email']="Cet email est déjà utilisé";
            }
        }
        //Vérification concordance des deux mots de passe
        if(empty($_POST['password']) || $_POST['password']!=$_POST['password_confirm']){
            $errors['password']="Les deux mots de passe ne correspondent pas";
        }
        //S'il n'y a pas eu d'erreurs lors du remplissage du formulaire, insertion des données dans la table users
        if(empty($errors)){
            $idUser=0;
            $username=$_POST['username'];
            $email=$_POST['email'];
            $password=password_hash($_POST['password'], PASSWORD_BCRYPT);
            $token=str_random(60);
    
            $queryRegister="INSERT INTO users (`idUser`, `username`, `email`, `password`, `confirmation_token`) VALUE ($idUser, '$username', '$email', '$password', '$token')";
            $resultRegister=$connexion->query($queryRegister);
            if(!$resultRegister) {
                $errors['insertion']= "Les données n'ont pas été insérées";
                die('Erreur Fatale');
            } else {
                //récupération de l'id utilisateur pour insertion dans le lien URL envoyé par email à l'utilisateur
                $user_id = $connexion->insert_id;
            }
            
            //Envoie d'un email de confirmation à l'utilisateur
            if(mail($email,"Confirmation de votre compte", "Pour confirmer votre inscription, cliquez sur le lien ci-dessous : \n\n http://astoessel.alwaysdata.net/applicationBudget/confirm.php?id=$user_id&token=$token")){
                //Ce message flash ne s'affiche pas au moment de la redirection vers l'accueil
                $_SESSION['flash']['succes']="Un mail de confirmation vous a été envoyé";
                header ('location: index.php');
                exit();
            }else{
                if(!empty($_POST)){
                $errors['confirmationMail'] = "Le mail de confirmation n'a pas été envoyé";
                }
            }
            exit();
        }
    
    }
    

?>
<?php require_once 'header.php';?>
<?php if(!empty($errors)):?>
    <div id="errorRegister">
        <p>Vous n'avez pas compléter le formulaire correctement.<br>
        Les erreurs suivantes ont été signalées :</p>
        <ul>
            <?php foreach ($errors as $error):?>
                <li><?=$error?></li>
            <?php endforeach;?>
        </ul>
    </div>
<?php endif;?>    

<form action="" method="post" class="inscription-form">
    <div class="inscription">
        <label for="username" class="identifiant">Votre identifiant</label>
        <br>
        <input type="text" name="username" id="username" placeholder="Pseudo">
    </div>
    <div class="inscription">
        <label for="email" class="identifiant">Votre email</label>
        <br>
        <input type="email" name="email" id="email" placeholder="pseudo@domain.com">
    </div>
    <div class="inscription">
        <label for="password" class="motDePasse">Votre mot de passe</label>
        <br>
        <input type="password" name="password" id="password" placeholder="Mot de passe">
    </div>
    <div class="inscription">
        <label for="password_confirm" class="motDePasse">Confirmez votre mot de passe</label>
        <br>
        <input type="password" name="password_confirm" id="password_confirm" placeholder="Mot de passe">
    </div>
    <div class="divButton">
        <button class="button" type="submit" value="valider">Valider votre inscription</button>
    </div>
</form>



<?php require_once 'footer.php';
?>