<?php
require_once 'functions.php';
connected();
$title="Synthèse";
$moisDeAnnee=[
  "01"=>"Janvier", 
  "02"=>"Février", 
  "03"=>"Mars", 
  "04"=>"Avril", 
  "05"=>"Mai", 
  "06"=>"Juin", 
  "07"=>"Juillet", 
  "08"=>"Août", 
  "09"=>"Septembre", 
  "10"=>"Octobre", 
  "11"=>"Novembre", 
  "12"=>"Décembre", 
];
require_once 'header.php';

$idUser = $_SESSION['auth']['idUser'];

//connexion bdd
require_once 'loginBDD.php';
$connexion = new mysqli($hn, $un, $pw, $db);
if ($connexion->connect_error) {
    die ('Erreur Fatale');
}

//Selection des donnees de la bdd pour l'utilisateur $idUser
$querySelection="SELECT idTransaction, amount, date, category, modePaiement, typeTransaction, coefficient FROM transaction INNER JOIN category ON transaction.idCategory=category.idCategory INNER JOIN modePaiement ON transaction.idModePaiement=modePaiement.idModePaiement INNER JOIN typeTransaction ON category.idTypeTransaction=typeTransaction.idTypeTransaction WHERE `idUser` = '$idUser' ORDER BY idTransaction";
$resultSelection=$connexion->query($querySelection);
if(!$resultSelection){
    die('Erreur Fatale');
}
//Insertion des donnees dans un tableau
$resultats=[];
while($result=mysqli_fetch_assoc($resultSelection)){
  array_push($resultats, $result);
}
$length=count($resultats);


//Récupération des 10 dernières transactions
$data=[];
for($i=($length-1); $i>($length-11); $i--){
  $dataX= $resultats[$i]['category'];
  $dataY=floatval($resultats[$i]['amount']*$resultats[$i]['coefficient']);

  if($dataY<0){
    $color="#E15147";
  } else {
    $color="#31A36A";
  }

  array_push($data, array("label"=>$dataX, "y"=>$dataY, "color"=>$color));
}

//Récupération des dépenses totales par catégorie et par mois de chaque année
$dataDepenses=[];
foreach($resultats as $resultat){
  //Récupération du mois et de l'année de transaction
  $yearMonth= date("Y-m", strtotime($resultat['date']));
  //Récupération du montant de transaction
  $amount=floatval($resultat['amount']);
  if ($resultat['typeTransaction'] == "Dépense"){ //Si j'ai une depense
    if(!array_key_exists($yearMonth,$dataDepenses)){ //Si la clé "date" n'existe pas
      $dataDepenses += [$yearMonth => []]; //Je crée une nouvelle clé "Y-m" qui aura pour valeur un tableau
        if (!array_key_exists($resultat['category'], $dataDepenses[$yearMonth])){ //Si la clé "category" n'existe pas
          $dataDepenses[$yearMonth] += [$resultat['category']=>$amount]; //Je crée une nouvelle clé "category" pour la clé "Y-m" correspondante qui aura le montant total ($amount) comme valeur
        } 
    }else{ //Si la clé "["y-m"][category]" existe  
      $dataDepenses[$yearMonth][$resultat['category']] += $amount; //Ajouter le montant au montant total
    }
  }
};

//Formatage des données ci-dessus (dépenses totales par catégorie et par mois de chaque année) pour utilisation en JS au format JSON
$partDepenses=[];
foreach($dataDepenses as $yearMonth=>$dataDepense){
  if(is_array($dataDepense)){
    $partDepenses[$yearMonth]=[];
    foreach ($dataDepense as $label=>$y){
      array_push($partDepenses[$yearMonth], array("label"=>$label, "y"=>$y));
    }
  }
}
//debug($partDepenses);


//Récupération des recettes et dépenses totales pour chaque mois de chaque année
$bilansMensuels =[];
foreach($resultats as $resultat){
  $transactionDate=date("Y-m", strtotime($resultat['date'])); 
  $somme=floatval($resultat['amount']);
  if(!array_key_exists($transactionDate,$bilansMensuels)){ //Si la clé "date" n'existe pas
    $bilansMensuels += [$transactionDate=>[]]; //Je crée une nouvelle clé "date" qui aura pour valeur un tableau
    if (!array_key_exists($resultat['typeTransaction'], $bilansMensuels[$transactionDate])){ //Si la clé "typeTransaction n'existe pas
      $bilansMensuels[$transactionDate] += [$resultat['typeTransaction'] => $somme];// Je créé une nouvelle clé qui aura pour valeur "$somme"
    }
  }else{ //Si les clés existent  
      $bilansMensuels[$transactionDate][$resultat['typeTransaction']] += $somme; //Ajouter la somme à la somme total pour chaque type de transaction
  }
}

//Formatage des données ci-dessus (Totaux mensuels des recettes et dépenses par mois de chaque année) pour utilisation en JS au format JSON
$bilan=[];
foreach($bilansMensuels as $anneeMois=>$bilanMensuel){
  if(is_array($bilanMensuel)){
    foreach($bilanMensuel as $type=>$montant){
      if(!array_key_exists($type, $bilan)){
        $bilan += [$type=>[]];
      }
      if ($type == "Dépense"){
        $montant =  $montant*(-1);
      }
      array_push($bilan[$type], array("y"=>$montant, "label"=>$anneeMois ));
    }
  }
}

// Calcul du bilan (positif ou négatif) pour les différents mois
$totalMensuel=[];
foreach($bilansMensuels as $anneeMois=>$bilanMensuel){
  if(is_array($bilanMensuel)){
      foreach($bilanMensuel as $type=>$montant){
        $total = $bilanMensuel['Recette']-$bilanMensuel['Dépense'];
      }
      array_push($totalMensuel, array("label"=>$anneeMois, "y"=>$total )); 
  }
}

//Obtention des mois présents dans la BDD pour utilisation dans le formulaire pour la comparaison des dépenses (chart2 et 2B)
$moisAComparer=[];
foreach($resultats as $resultat){
  $mois=date("m", strtotime($resultat['date']));
  $annee=date("Y", strtotime($resultat['date']));
  $transactionDate=$moisDeAnnee[$mois]." ".$annee;
  if(!in_array($transactionDate,$moisAComparer)){
    array_push($moisAComparer, $transactionDate);
  }
}

if(isset($_GET['moisCompare'])){
  $date = $_GET['moisCompare']; //Renvoie string(12) "Juillet 2021"
  $dateFormGet = explode(" ", $_GET['moisCompare']); //Sépare le mois en lettres et l'année
  $keyYear = $dateFormGet[1];
  $keyMonth = array_search($dateFormGet[0], $moisDeAnnee); //Trouver le numéro du mois dans $moisDeAnnee
  $dateCompare = $keyYear."-".$keyMonth; //Obtention de la clé pour le tableau des dépenses mensuelles (partDepense)
} else {
  $dateCompare = date("Y-m", strtotime("-1month"));
}
?>

<h2>Bonjour <?=$_SESSION['auth']['username']?></h2>
<div class="syntheseOFF">   <!--div pour affichage message sur mobile-->
  <p>La taille de votre écran est insuffisante pour visualiser cette page.</p>
</div>
<div class="syntheseON"> <!--div pour affichage message sur desktop-->
  <h3>Voici la synthèse de vos transactions</h3>

  <!--Tableau des 10 dernières transactions-->
  <div class="lastTransactions">
  <h2 class="titreDiv">Vos 10 dernières transactions</h2>
  <table class="tableTransaction">
    <thead class="theadTransaction">
      <tr class="trTransaction">
        <td class="tdTransaction colonne">Montant</td>
        <td class="tdTransaction colonne">Date</td>
        <td class="tdTransaction colonne">Type</td>
        <td class="tdTransaction colonne">Catégorie</td>
        <td class="tdTransaction colonne">Mode de paiement</td>
      </tr>
    </thead>
    <tbody class="tbodyTransaction">
    <?php for($i=($length-1); $i>($length-11); $i--):?>
      <tr>
        <td class="tdTransaction donnees"><?php $amount=str_replace('.', ',',$resultats[$i]['amount']);
              echo $amount." €"?></td>
        <td class="tdTransaction donnees"><?=date('d-m-Y', strtotime($resultats[$i]['date']))?></td>
        <td class="tdTransaction donnees"><?=$resultats[$i]['typeTransaction']?></td>
        <td class="tdTransaction donnees"><?=$resultats[$i]['category']?></td>
        <td class="tdTransaction donnees"><?=$resultats[$i]['modePaiement']?></td>
      </tr>
    <?php endfor ?>
    </tbody>
  </table>
  </div>    
  <br>

  <!--les container des graphiques-->
  <!--le container des 10 dernières transactions-->
  <div id="chartContainer1" style="height: 400px; width: 100%;"></div>
  <br>
  <!--le container des camenberts de répartitions des dépenses mensuelles-->
  <!--avec formulaire pour choisir le mois à comparer au mois actuel-->
  <div class="compareChart">
    <div class="depenseChart" id="chartContainer2" style="height: 400px; width: 100%;"></div>
    <div class="depenseChart" id="chartContainer2B" style="height: 400px; width: 100%;"></div>
  </div>
  <form action="" method="get" class="chart2-form">
    <div class="chartForm">
        <label for="moisCompare">Comparer au mois de </label>
        <select id="moisCompare" name="moisCompare" class="chart2Form">
          <option value="" selected >--choix du mois--</option>
            <?php foreach ($moisAComparer as $mois):?>
              <option value="<?=$mois?>"><?=$mois?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="divForm">
        <button class="button" type="submit" value="valider">Valider</button>
    </div>
  </form>
  <br>
  <!--le container pour la comparaison des recettes et dépenses totales par mois avec bilan-->
  <div id="chartContainer3" style="height: 400px; width: 100%;"></div>
</div>

<script type="text/javascript">
  //formatage des données php au format JSON
  let dataphp = <?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK); ?>;
  let partDepense = <?= json_encode($partDepenses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);?>;
  let bilan = <?= json_encode($bilan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);?>;
  let totaux = <?= json_encode($totalMensuel, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);?>;

  //Construction des données pour "Bilan des recettes et dépenses" (chart3)
  let dataS=[];
  for (let legend in bilan){
    dataSerie={
      "type":"stackedColumn",
      "showInLegend": true,
      "legendText": legend,
      "dataPoints":Object.values(bilan[legend]),
    }
    dataS.push(dataSerie)  
  }
  //Ajout des données pour le bilan du chart3 (= Recettes - Dépense)
  let dataTotal={
      "type":"line",
      "showInLegend": true,
      "legendText": "Bilan du mois",
      "dataPoints":totaux,
  }
  dataS.push(dataTotal)  

  window.onload = function () {

    var chart1 = new CanvasJS.Chart("chartContainer1", {
      theme:"light1",
      title:{
        text: "Vos 10 dernières transactions"              
      },
      axisX:{
        title: "Catégories de transaction",
        labelAngle: 0, 
        labelFontSize: 12 ,
      },
      data: [            
        {
          type: "column",
          dataPoints: dataphp,
        },
       ],
    });
    
    var chart2 = new CanvasJS.Chart("chartContainer2", {
      animationEnabled: true,
      colorSet:"colorSetPieChart",
      title: {
        fontSize:25,
        padding:10,
        text: "Répartition de vos dépenses pour le mois de <?=$moisDeAnnee[date("m")]." ".date("Y")?>"
      },
      data: [
        {
        type: "pie",
        startAngle: 120,
        yValueFormatString: "##0.00\"€\"",
        indexLabel: "{label} {y}",
        dataPoints: partDepense ['<?=date("Y-m")?>']
    	},
      ],
    });

    var chart2B = new CanvasJS.Chart("chartContainer2B", {
      animationEnabled: true,
      colorSet:"colorSetPieChart",
      title: {
        fontSize:25,
        padding:10,
        text: "Répartition de vos dépenses pour le mois de <?=$moisDeAnnee[date('m',strtotime($dateCompare))]." ".$keyYear?>"
      },
      data: [
        {
        type: "pie",
        startAngle: 120,
        yValueFormatString: "##0.00\"€\"",
        indexLabel: "{label} {y}",
        dataPoints: partDepense ['<?=$dateCompare?>']
    	},
      ],
    });


    var chart3 = new CanvasJS.Chart("chartContainer3", {
      title: {
          text: "Bilan de mes recettes et dépenses"
      },
      data: dataS
      
    });
    chart1.render();
    chart2.render();
    chart2B.render();
    chart3.render();
  }
</script>

<?php require_once 'footer.php';?>