<?php 
// Si aucune session démarrée, en démarrer une
if(session_status()==PHP_SESSION_NONE){
    session_start();
}

//Fonction pour affichage dynamique des liens de navigation
function nav_li($lien, $nav_title){
    $class = 'nav-link ';
    if($_SERVER['SCRIPT_NAME']===$lien){
        $class=$class.'activer';
    }
    return '<li class="nav-li"><a class="'.$class.'" href="'.$lien.'">'.$nav_title."</a></li>\r\n";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--Affichage dynamique du titre de la page-->
    <title>
        <?php if(isset ($title)) {
        echo $title;
        } else {
            echo "Application Budget";
        } ?>
    </title>
    <meta name="Application budget" content="Cette page représente l'application budget de Audrey Stoessel">

    <link href="CSS/stylesheet_XL.css" media="screen and (min-width: 521px)" rel="stylesheet">
    <link href="CSS/stylesheet_S.css" media="screen and (max-width: 521px)" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <script type="text/javascript" src="https://canvasjs.com/assets/script/canvasjs.min.js" defer></script>
</head>
<body>
    <header>
        <h1>Application personnelle de gestion du budget</h1>
        <!--Affichage de l'état de connexion de l'utilisteur-->
        <?php if(isset($_SESSION['auth'])):?>
            <div class="connect">
                <div class="etat">
                    <p>Connecté</p>
                    <img src="img/diode-verte.png" class="diode" alt="diode verte">
                </div>
                <a href="logout.php" class="deconnect">Déconnexion</a>
            </div>
        <?php else:?>
            <div class="connect">
                <div class="etat">
                    <p>Déconnecté</p>
                    <img src="img/diode-rouge.png" class="diode" alt="diode rouge">
                </div>
                <a href="index.php" class="deconnect">Connexion</a>
            </div>
        <?php endif?>
    <!--Affichage dynamique de la barre de navigation-->
        <nav>
        <!--Pour affichage du menu burger sur mobile-->
            <label for="toggle" id="burger">☰</label>
            <input type="checkbox" id="toggle">
        <!--Affichage des liens de navigation-->
            <ul class="nav-list">
                <?=nav_li('index.php', 'ACCUEIL')?>
                <?=nav_li('transaction.php', 'TRANSACTION')?>
                <?=nav_li('synthese.php', 'SYNTHESE')?>
                <?=nav_li('register.php', 'INSCRIPTION')?>
            </ul>
        </nav>
        <!--Affichage de messages flash si définis-->
        <?php if(isset($_SESSION['flash'])):?>
            <?php foreach ($_SESSION['flash'] as $type=>$message):?>
                <div class="alert alert-<?=$type?>">
                    <?=$message?>
                </div>
            <?php endforeach ?>
            <?php unset($_SESSION['flash']);?>
        <?php endif?>

    </header>
    <main>
