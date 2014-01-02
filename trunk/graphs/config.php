<?php

// Connexion MySql et requête.
$serveur="localhost"; 
$login="teleinfo";

$baseTemp="temperatures";
$baseElec="teleinfo";

$tableTemp="temperatures";
$tableElec="teleinfo";

$pass="owQjygooL3zD";

$tarif_type = "BASE"; // vaut soit "HCHP" soit "BASE"

// prix du kWh :
// prix TTC au 1/01/2012 :
if ( $tarif_type != "HCHP") {
  // prix tarif Base EDF
  $prixBASE = (0.0828+0.003+0.0105)*1.196; // kWh + CSPE + TCFE, TVA 19.6%
  $prixHP = 0;
  $prixHC = 0;
  // Abpnnement pour disjoncteur 30 A
  $abo_annuel = 12*(5.36+1.92/2)*1.055; // Abonnement + CTA, TVA 5.5%
} else {
  // prix tarif HP/HC EDF
  $prixBASE = 0;
  $prixHP = 0.1312;
  $prixHC = 0.0895;
  // Abpnnement pour disjoncteur 45 A
  $abo_annuel = 112.87;
}

function obtenirSondesTemp ($id, $avecMeteo) {
  global $tableTemp;
  global $baseTemp;

  mysql_select_db($baseTemp) or die("Erreur de connexion a la base de donnees $base");
  
  $query="SELECT DISTINCT `piece`, `id`, `nom`
    FROM `$tableTemp` 
     ".((isset($id))?" WHERE id=$id":(($avecMeteo==1)?"":" WHERE id!=3 "))."
    ORDER BY `id`";

  return $query;
}


function queryDailyTemp ($timestampdebut, $timestampfin, $id, $avecMeteo) {
  global $tableTemp;
  global $baseTemp;
  
$datetime1 = new DateTime();
$datetime1->setTimestamp($timestampdebut);
$datetime2 = new DateTime();
$datetime2->setTimestamp($timestampfin);
$interval = $datetime1->diff($datetime2);
$nbjour = $interval->format('%a');

$mask = "%d-%m-%Y %H:%i";

if ($nbjour>30) 
{
	$mask = "%d-%m-%Y %H";
}
else if ($nbjour>60) 
{
	$mask = "%d-%m-%Y";
}

  mysql_select_db($baseTemp) or die("Erreur de connexion a la base de donnees $base");

  
  $query="SELECT UNIX_TIMESTAMP(datecapture) AS timestamp, date(datecapture) AS rec_date, time(datecapture) AS rec_time, `piece`, `id`, `nom`, ROUND(AVG(`temp`),2) AS 'temp'
    FROM `$tableTemp` 
    WHERE datecapture >= FROM_UNIXTIME($timestampdebut)
    AND datecapture < FROM_UNIXTIME($timestampfin) 
    ".((isset($id))?" AND id=$id":(($avecMeteo==1)?"":" AND id!=3 "))."
    GROUP BY `piece`, `id`, `nom`, DATE_FORMAT(datecapture, '$mask')
    ORDER BY datecapture";
// UNIX_TIMESTAMP(datecapture) DIV 8600
// DATE_FORMAT(datecapture, '%d-%m-%Y %H:%i')
// AVG(`temp`) AS 'temp'

// On eclue l'id 3 "Temp internet" par defaut
 //echo "/*".$query."*/";
  return $query;
}

function queryHistoryTemp ($timestampdebut, $timestampfin, $dateformatsql, $id) {
  global $tableTemp;
  global $baseTemp;
 
  mysql_select_db($baseTemp) or die("Erreur de connexion a la base de donnees $base");

  $query="SELECT date(datecapture) AS rec_date, DATE_FORMAT(date(datecapture), '$dateformatsql') AS 'periode' ,
    ROUND(AVG(temp),2) as Moy
    FROM `$tableTemp` 
    WHERE datecapture > FROM_UNIXTIME($timestampdebut)
    AND datecapture < FROM_UNIXTIME($timestampfin)
    ".((isset($id))?" AND id=$id":"")."
    GROUP BY periode
    ORDER BY rec_date" ;

  return $query;
}


function queryDailyElec ($timestampdebut, $timestampfin) {
  global $tableElec;
  global $baseElec;

  mysql_select_db($baseElec) or die("Erreur de connexion a la base de donnees $base");
  
  $query="SELECT UNIX_TIMESTAMP(datecapture) AS timestamp, date(datecapture) AS rec_date, time(datecapture) AS rec_time, ptec, papp, iinst
    FROM `$tableElec` 
    WHERE datecapture >= FROM_UNIXTIME($timestampdebut)
    AND datecapture < FROM_UNIXTIME($timestampfin)
    ORDER BY datecapture";

  return $query;
}

function queryHistoryElec ($timestampdebut, $timestampfin, $dateformatsql) {
  global $tableElec;
  global $baseElec;

  mysql_select_db($baseElec) or die("Erreur de connexion a la base de donnees $base");

  $query="SELECT date(datecapture) AS rec_date, DATE_FORMAT(date(datecapture), '$dateformatsql') AS 'periode' ,
    ROUND( ((MAX(`base`) - MIN(`base`)) / 1000) ,1 ) AS base, 
    ROUND( ((MAX(`hchp`) - MIN(`hchp`)) / 1000) ,1 ) AS hp, 
    ROUND( ((MAX(`hchc`) - MIN(`hchc`)) / 1000) ,1 ) AS hc 
    FROM `$tableElec` 
    WHERE datecapture > FROM_UNIXTIME($timestampdebut)
    AND datecapture < FROM_UNIXTIME($timestampfin)
    GROUP BY periode
    ORDER BY rec_date" ;
  
  return $query;
}


?>
