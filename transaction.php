<?php
require_once 'functions.php';
connected();
$title="Transaction";
require_once 'header.php';

//connexion bdd
require_once 'loginBDD.php';
$connexion = new mysqli($hn, $un, $pw, $db);
if ($connexion->connect_error) {
    die ('Erreur Fatale');
}

//forcer affichage utf8
$queryUtf8="SET NAMES utf8";
$resultUtf8=$connexion->query($queryUtf8);
if(!$resultUtf8) {
    die('Erreur Fatale');
}

//Sélection des données de la table "transaction"
$queryTransaction="SELECT * FROM transaction";
$resultTransaction=$connexion->query($queryTransaction);
if(!$resultTransaction){
    die('Erreur Fatale');
}

//Sélection des données de la table "typeTransaction"
$queryType="SELECT * FROM typeTransaction";
$resultType=$connexion->query($queryType);
if(!$resultType){
    die('Erreur Fatale');
}

//Transfert des données de la table "typeTransaction" dans un tableau
//TO DO : passer requête en SOO
$varTypeResult=array();
while($varType=mysqli_fetch_assoc($resultType)){
    $varTypeResult[]=$varType;
}

//Sélection des données de la table "category"
$queryCategory="SELECT * FROM category";
$resultCategory=$connexion->query($queryCategory);
if(!$resultCategory){
    die('Erreur Fatale');
}

//Transfert des données de la table "category" dans un tableau
//TO DO : passer requête en SOO
$varCategoryResult=array();
while($varCategory=mysqli_fetch_assoc($resultCategory)){
    $varCategoryResult[]=$varCategory;
}

//Sélection des données de la table "modePaiement"
$queryMode="SELECT * FROM modePaiement";
$resultMode=$connexion->query($queryMode);
if(!$resultMode){
    die('Erreur Fatale');
}

//Vérification des variables entrées à partir du formulaire 

if (isset($_POST['amount']) && !preg_match('#^[0-9.,]+$#', $_POST['amount'])){
    $_SESSION['flash']['error']="Pour le montant, vous ne pouvez entrer que des chiffres de 0 à 9 ainsi que les caractères \".\" et \",\"";
    header ('location: transaction.php');
}

else if(isset($_SESSION['auth']) &&
        isset($_POST['idTransaction']) &&
        isset($_POST['amount']) &&
        isset($_POST['date']) &&
        isset($_POST['Category']) &&
        isset($_POST['idModePaiement'])) {
            $idTransaction=0;
            $amount=round(floatval(str_replace(",", ".", $_POST['amount'])), 2);
            $date=date('Y-m-d', strtotime($_POST['date']));
            $Category=explode("_", $_POST['Category']);
            $idCategory=intval($Category[1]);
            $idTypeTransaction=intval($Category[0]);
            $idModePaiement=intval($_POST['idModePaiement']);
            $idUser=$_SESSION['auth']['idUser'];
}


//Insertion transaction dans BDD : TABLE transaction
if($idTransaction===0){
    $queryInsert = "INSERT INTO transaction (`idTransaction`, `amount`, `date`, `idCategory`, `idModePaiement`, `idTypeTransaction`, `idUser`) VALUES (null, '$amount', '$date', '$idCategory', '$idModePaiement', '$idTypeTransaction', '$idUser')";
    $resultInsert=$connexion->query($queryInsert);
    if (!$resultInsert) {
        echo "Echec de l'insertion";
    }
}

?>

<h2>Bonjour <?=$_SESSION['auth']['username']?></h2>
<h3>Entrez votre transaction</h3>

<!--Formulaire de création d'une nouvelle transaction pour insertion dans la BDD-->
<form action="" method="post" class="transaction-form">
    <input type="hidden" name="idTransaction" value="<?=$idTransaction?>"> <!-- Modification en value="" ? -->
    <div class="divForm">
        <label for="amount">Montant</label>
        <input type="text" class="input-item" name="amount" id="amount" placeholder="0,00 €">
    </div>
    <div class="divForm">
        <label for="date">Date</label>
        <input type="date" class="date input-item" name="date" id="date">
    </div>
    <div class="divForm">
        <label for="Category">Catégorie</label>
        <select id="Category" name="Category" class="input-item">
        <option value="" selected >--Choix catégorie--</option>
        <?php foreach ($varTypeResult as $typeResult):?>
            <optgroup label="<?=$typeResult['typeTransaction']?>">
                <?php foreach ($varCategoryResult as $categoryResult):?>
                    <?php if ($categoryResult['idTypeTransaction']=== $typeResult['idTypeTransaction']):?>
                        <option value="<?=$typeResult['idTypeTransaction']."_".$categoryResult['idCategory']?>"><?=$categoryResult['Category']?></option>
                        <!-- "value" dans option récupère "idTypeTransaction"_"idCategory"
                        Faire un explode() pour récupérer les id pour BDD-->
                    <?php endif ?>
                <?php endforeach ?>
            </optgroup>
        <?php endforeach ?>
        </select>
    </div>
    <div class="divForm">
        <label for="modePaiement">Mode de Paiement</label>
        <select id="modePaiement" name="idModePaiement" class="input-item" required>
            <option value="" selected >--Choix mode de paiement--</option>
            <?php while($varMode=mysqli_fetch_assoc($resultMode)):?>
                <option value="<?=$varMode['idModePaiement']?>"><?=$varMode['modePaiement']?></option>
            <?php endwhile ?>
        </select>
    </div>
    <div class="divButton">
        <button class="button" type="submit" value="valider">Valider</button>
    </div>
</form>
<?php require_once 'footer.php';?>
