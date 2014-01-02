	<?php
header('Content-type: application/json');

setlocale(LC_ALL , "fr_FR" );
date_default_timezone_set("Europe/Paris");

// Config : Connexion MySql et requête. et prix du kWh 
include_once("config.php");


/****************************************************************************************/
/*    Graph temperatures des 3 derniers jours										    */
/****************************************************************************************/
function dailyTemp () {
  global $table, $tarif_type;

  $time_start = microtime(true); 
  
  $courbe_titre[0]="Heures de Base";
  $courbe_min[0]=5000;
  $courbe_max[0]=0;
  $courbe_titre[1]="Heures Pleines";
  $courbe_min[1]=5000;
  $courbe_max[1]=0;
  $courbe_titre[2]="Heures Creuses";
  $courbe_min[2]=5000;
  $courbe_max[2]=0;

  $courbe_titre[3]="Intensité";
  $courbe_min[3]=45;
  $courbe_max[3]=0;

  $datedebut = isset($_GET['datedebut'])?$_GET['datedebut']:null;
  $datefin = isset($_GET['datefin'])?$_GET['datefin']:null;
  $id = isset($_GET['id'])?$_GET['id']:null;
  
  $avecMeteo = isset($_GET['avecMeteo'])?$_GET['avecMeteo']:0;
  
  if (isset($datefin)) {
  
    $timestampheurefin = min($datefin, time());
    $timestampheurefin = mktime(23,59,59,date("m",$timestampheurefin),date("d",$timestampheurefin),date("Y",$timestampheurefin));
  
  	if (isset($datedebut)) {
    	$timestampheuredebut = mktime(0,0,0,date("m",$datedebut),date("d",$datedebut),date("Y",$datedebut));
    	
    	$timestampminref = $timestampheurefin - (3*24*60*60);
    	if ($timestampheuredebut>=$timestampminref)
    	{
    		$timestampheuredebut = $timestampminref;
    	} 	
    	
  	} else {
  		$timestampheuredebut = $timestampheurefin - (3*24*60*60);
  	}
    
    
  } else {
    $heurecourante = date('H') ;              // Heure courante.
	$timestampheurefin = mktime($heurecourante+1,0,0,date("m"),date("d"),date("Y"));  // Timestamp courant à heure fixe (mn et s à 0).
    $timestampheuredebut = $timestampheurefin - (3*24*60*60);
  }

  $timestampfin = $timestampheurefin;              
  $timestampdebut = $timestampheuredebut ;             

  $time_sql = microtime(true); 

  $query = obtenirSondesTemp($id, $avecMeteo);
  
  $result=mysql_query($query) or die ("<b>Erreur</b> dans la requète <b>" . $query . "</b> : "  . mysql_error() . " !<br>");

  $nbenreg = mysql_num_rows($result);
  $sondes = array();

  while ($row = mysql_fetch_array($result)) {
  	$sondes[$row['id']] = $row['nom']; 
  	${'array_'.$row['id']} = array();
  }
  
  $query = queryDailyTemp($timestampdebut, $timestampfin, $id, $avecMeteo);
  
  $result=mysql_query($query) or die ("<b>Erreur</b> dans la requète <b>" . $query . "</b> : "  . mysql_error() . " !<br>");

  // Temps exec
  $time_end_sql = microtime(true);

  //dividing with 60 will give the execution time in minutes other wise seconds
  $execution_time_sql = ($time_end_sql - $time_sql);

  $nbdata=0;
  $nbenreg = mysql_num_rows($result);
  //$nbenreg--;
  $date_deb=0; // date du 1er enregistrement
  $date_fin=time();

  $navigator = array();

  $row = mysql_fetch_array($result);
  $ts = intval($row["timestamp"]);

  while ($nbenreg > 0 ){
    if ($date_deb==0) {
      $date_deb = $row["timestamp"];
    }
    $ts = intval($row["timestamp"]) * 1000;
   
    $val = floatval(str_replace(",", ".", $row["temp"]));
    
    array_push ( ${'array_'.$row["id"]}, array($ts, $val ));

    array_push ( $navigator , array($ts, $val ));
    if ($courbe_max[0]<$val) {$courbe_max[0] = $val; $courbe_maxdate[0] = $ts;};
    if ($courbe_min[0]>$val) {$courbe_min[0] = $val; $courbe_mindate[0] = $ts;};
    
    // récupérer prochaine occurence de la table
    $row = mysql_fetch_array($result);
    $nbenreg--;
    $nbdata++;
  }
  mysql_free_result($result);

  $date_fin = $ts/1000;

  $plotlines_max = max($courbe_max[0], $courbe_max[1], $courbe_max[2]);
  $plotlines_min = min($courbe_min[0], $courbe_min[1], $courbe_min[2]);

  $ddannee = date("Y",$date_deb);
  $ddmois = date("m",$date_deb);
  $ddjour = date("d",$date_deb);
  $ddheure = date("G",$date_deb); //Heure, au format 24h, sans les zéros initiaux
  $ddminute = date("i",$date_deb);

  $ddannee_fin = date("Y",$date_fin);
  $ddmois_fin = date("m",$date_fin);
  $ddjour_fin = date("d",$date_fin);
  $ddheure_fin = date("G",$date_fin); //Heure, au format 24h, sans les zéros initiaux
  $ddminute_fin = date("i",$date_fin);

  $date_deb_UTC=$date_deb*1000;
  $date_fin_UTC=$date_fin*1000;

  $datetext = "$ddjour/$ddmois  $ddheure:$ddminute au $ddjour_fin/$ddmois_fin  $ddheure_fin:$ddminute_fin";

  $seuils = array (
    'min' => $plotlines_min,
    'max' => $plotlines_max,
  );
  
  // Temps exec
  $time_end = microtime(true);

  //dividing with 60 will give the execution time in minutes other wise seconds
  $execution_time = ($time_end - $time_start);

  $response = array(
  'title' => "Graph du $datetext",
    'subtitle' => "",
    'stats' => "Global: ".$execution_time." Sql: ".$execution_time_sql,
    'debut' => $date_deb_UTC,
    'fin' => $date_fin_UTC,
    'tsdebut' => $timestampdebut,
    'tsfin' => $timestampfin,
    'navigator' => $navigator,
    'seuils' => $seuils
    );

  foreach ($sondes as $key => $value) {
  	//$response["id${value}_name"] = "Sonde $value";
  	//$response["id${value}_data"] = ${'array_'.$value};
  	
  	$response["temps"][] = array(
  		"id_name" => $value, 
  		"id_data" => ${'array_'.$key}
  	);
  }
  
  return $response;
}

/*************************************************************/
/*    Graph temp sur période [8jours|8semaines|8mois|1an]    */
/*************************************************************/
function historyTemp() {
  global $table;
  global $abo_annuel;
  global $prixBASE;
  global $prixHP;
  global $prixHC;
  global $tarif_type;

  $regroupement = isset($_GET['regroupement'])?$_GET['regroupement']:"j";
  $datedebut = isset($_GET['datedebut'])?$_GET['datedebut']:null;
  $datefin = isset($_GET['datefin'])?$_GET['datefin']:null;
  $id = isset($_GET['id'])?$_GET['id']:null;
  
  if (isset($datefin)) {
  
    $timestampheurefin = min($datefin, time());
  
    if (isset($datedebut))
    {
        $timestampheuredebut = min($datedebut, time());
    } else {
        $timestampheuredebut = null;
    }
  }
  else 
  {
    $heurecourante = date('H') ;              // Heure courante.
	$timestampheurefin = mktime(23,59,59,date("m"),date("d"),date("Y"));  // Timestamp courant à heure fixe (mn et s à 0).
    $timestampheuredebut = null; 
  }
  
  if ($timestampheuredebut == null) {
  switch ($regroupement) {
    case "j":
        $timestampheuredebut = $timestampheurefin - (24*60*60*7);
    break;
    case "s":
        $timestampheuredebut = mktime(0,0,0,date("m", $timestampheurefin), date("d",$timestampheurefin)-(7*7), date("Y",$timestampheurefin));
    break;
    case "m":
        $timestampheuredebut = mktime(0,0,0,date("m", $timestampheurefin)-7, 1, date("Y",$timestampheurefin));
    break;
    case "a":
        $timestampheuredebut = mktime(0,0,0,date("m", $timestampheurefin), 1, (date("Y",$timestampheurefin)-1));
    break;
  }
  }
  
  // Garde fou minuit 23:59:59
  $timestampheurefin = mktime(23,59,59,date("m",$timestampheurefin),date("d",$timestampheurefin),date("Y",$timestampheurefin));
  $timestampheuredebut = mktime(0,0,0, date("m", $timestampheuredebut),date("d", $timestampheuredebut),date("Y", $timestampheuredebut));

  
  switch ($regroupement) {
    case "j":
        // Jour

        $xlabel = floor(($timestampheurefin-$timestampheuredebut)/3600/24)." jours (du ".date("d/m/Y",$timestampheuredebut)." au ".date("d/m/Y",$timestampheurefin).")";
        $dateformatsql = "%a %e/%c/%Y" ;
    break;
    case "s":
        // Semaine
        while ( date("w", $timestampheuredebut) != 1 )  // Recule d'un jour jusqu'a un lundi
        {
            $timestampheuredebut = gmmktime(0,0,0, date("m", $timestampheuredebut), date("d", $timestampheuredebut)-1, date("Y", $timestampheuredebut));
        }
        $nbanne = date("Y", $timestampheurefin)-date("Y", $timestampheuredebut);
        $nbsemaine = date("W", $timestampheurefin)-date("W", $timestampheuredebut);
        
        $nbsemaine = $nbsemaine + ($nbanne*52) + 1;
        
        $xlabel = $nbsemaine." semaines (du ".date("d/m/Y",$timestampheuredebut)." au ".date("d/m/Y",$timestampheurefin).")";
        $dateformatsql = "%v/%Y" ;
    break;
    case "m":
        // Mois
        
        // Premier jour du mois
        $timestampheuredebut = mktime(0,0,0,date("m", $timestampheuredebut),1,date("Y", $timestampheuredebut));
        
        $nbanne = date("Y", $timestampheurefin)-date("Y", $timestampheuredebut);
        $nbmois = date("m", $timestampheurefin)-date("m", $timestampheuredebut);
        
        $nbmois = $nbmois + ($nbanne*12) + 1;

        $xlabel = $nbmois." mois (du ".date("d/m/Y",$timestampheuredebut)." au ".date("d/m/Y",$timestampheurefin).")";
        $dateformatsql = "%b/%Y" ;
    break;
    case "a":
        // Annee
        // Premier jour de l'année
        $timestampheuredebut = mktime(0,0,0,1,1,date("Y", $timestampheuredebut));
        

        $xlabel = (date("Y", $timestampheurefin)-date("Y", $timestampheuredebut)+1)." ans (du ".date("d/m/Y",$timestampheuredebut)." au ".date("d/m/Y",$timestampheurefin).")" ;
        $dateformatsql = "%Y" ;
    break;
  }

  $query="SET lc_time_names = 'fr_FR'" ;  // Pour afficher date en français dans MySql.
  mysql_query($query) ;

  $query = queryHistoryTemp($timestampheuredebut, $timestampheurefin, $dateformatsql, $id); 
  
  $result=mysql_query($query) or die ("<b>Erreur</b> dans la requète <b>" . $query . "</b> : "  . mysql_error() . " !<br>");
  $num_rows = mysql_num_rows($result) ;
  $no = 0 ;
  $date_deb=0; // date du 1er enregistrement
  $date_fin=time();

  while ($row = mysql_fetch_array($result))
  {
    if ($date_deb==0) {
      $date_deb = strtotime($row["rec_date"]);
    }
    $date[$no] = $row["rec_date"] ;
    $timestp[$no] = $row["periode"] ;
    $kwhbase[$no]=floatval(str_replace(",", ".", $row["Moy"]));
    $no++ ;
  }
  $date_digits_dernier_releve=explode("-", $date[count($date) -1]) ;
  $date_dernier_releve =  Date('d/m/Y', gmmktime(0,0,0, $date_digits_dernier_releve[1] ,$date_digits_dernier_releve[2], $date_digits_dernier_releve[0])) ;

  mysql_free_result($result);

  $ddannee = date("Y",$date_deb);
  $ddmois = date("m",$date_deb);
  $ddjour = date("d",$date_deb);
  $ddheure = date("G",$date_deb); //Heure, au format 24h, sans les zéros initiaux
  $ddminute = date("i",$date_deb);

  $date_deb_UTC=$date_deb*1000;

  $datetext = "$ddjour/$ddmois/$ddannee  $ddheure:$ddminute";
  $ddmois=$ddmois-1; // nécessaire pour Date.UTC() en javascript qui a le mois de 0 à 11 !!!

  
  return array(
    'title' => "Température sur $xlabel",
    'debut' => $date_deb_UTC,
    'tsdebut' => $timestampheuredebut,
    'tsfin' => $timestampheurefin,
    'BASE_name' => 'Moyenne',
    'BASE_data'=> $kwhbase,
    'categories' => $timestp
    );
}

/****************************************************************************************/
/*    Menu dynamique																    */
/****************************************************************************************/
function menu() {
       
  $query = obtenirSondesTemp(null, 1);
  
  $result=mysql_query($query) or die ("<b>Erreur</b> dans la requète <b>" . $query . "</b> : "  . mysql_error() . " !<br>");

  $nbenreg = mysql_num_rows($result);
  $sondes = array();
  
  $response = array();
  
  while ($row = mysql_fetch_array($result)) {
  	$response["menuTemp"][] = array("cle" => $row['id'], "nom" => $row['nom']);
  }
  
  return $response;
}


/*************************************************************/
/*    Elec												     */
/*************************************************************/

/****************************************************************************************/
/*    Graph consomation w des 24 dernières heures + en parrallèle consomation d'Hier    */
/****************************************************************************************/
function dailyElec () {
  global $table, $tarif_type;
  
  $courbe_titre[0]="Heures de Base";
  $courbe_min[0]=5000;
  $courbe_max[0]=0;
  $courbe_titre[1]="Heures Pleines";
  $courbe_min[1]=5000;
  $courbe_max[1]=0;
  $courbe_titre[2]="Heures Creuses";
  $courbe_min[2]=5000;
  $courbe_max[2]=0;

  $courbe_titre[3]="Intensité";
  $courbe_min[3]=45;
  $courbe_max[3]=0;

  $datedebut = isset($_GET['datedebut'])?$_GET['datedebut']:null;
  $datefin = isset($_GET['datefin'])?$_GET['datefin']:null;
  
  if (isset($datefin)) {
  
    $timestampheurefin = min($datefin, time());
  
    if (isset($datedebut))
    {
        $timestampheuredebut = min($datedebut, time());
    } else {
        $timestampheuredebut = $timestampheurefin - (24*60*60);
    }
  }
  else 
  {
    $heurecourante = date('H') ;              // Heure courante.
    $timestampheurefin = mktime($heurecourante+1,0,0,date("m"),date("d"),date("Y"));  // Timestamp courant à heure fixe (mn et s à 0).
    $timestampheuredebut = $timestampheurefin - (24*60*60);
  }

  $timestampfin = $timestampheurefin;
  $periodesecondes = $timestampheurefin-$timestampheuredebut ;              
  $timestampdebut = $timestampheuredebut ;       

  $timestampdebut2 = $timestampdebut;
  $timestampdebut = $timestampdebut - $periodesecondes ;        

  $query = queryDailyElec($timestampdebut, $timestampfin);
  
  $result=mysql_query($query) or die ("<b>Erreur</b> dans la requète <b>" . $query . "</b> : "  . mysql_error() . " !<br>");

  $nbdata=0;
  $nbenreg = mysql_num_rows($result);
  $date_deb=0; // date du 1er enregistrement
  $date_fin=time();

  $array_BASE = array();
  $array_HP = array();
  $array_HC = array();
  $array_I = array();
  $array_JPrec = array();
  $navigator = array();

  $row = mysql_fetch_array($result);
  $ts = intval($row["timestamp"]);

  while (($ts < $timestampdebut2) && ($nbenreg>0) ){
    $ts = ( $ts + 24*3600 ) * 1000;
    $val = floatval(str_replace(",", ".", $row["papp"]));
    array_push ( $array_JPrec , array($ts, $val ));
    $row = mysql_fetch_array($result);
    $ts = intval($row["timestamp"]);
    $nbenreg--;
  }

  while ($nbenreg > 0 ){
    if ($date_deb==0) {
      $date_deb = $row["timestamp"];
    }
    $ts = intval($row["timestamp"]) * 1000;
    if ( $row["ptec"] == "TH.." )      // Test si heures de base.
    {
      $val = floatval(str_replace(",", ".", $row["papp"]));
      array_push ( $array_BASE , array($ts, $val ));
      array_push ( $array_HP , array($ts, null ));
      array_push ( $array_HC , array($ts, null ));
      array_push ( $navigator , array($ts, $val ));
      if ($courbe_max[0]<$val) {$courbe_max[0] = $val; $courbe_maxdate[0] = $ts;};
      if ($courbe_min[0]>$val) {$courbe_min[0] = $val; $courbe_mindate[0] = $ts;};
    }
    elseif ( $row["ptec"] == "HP" )      // Test si heures pleines.
    {
      $val = floatval(str_replace(",", ".", $row["papp"]));
      array_push ( $array_BASE , array($ts, null ));
      array_push ( $array_HP , array($ts, $val ));
      array_push ( $array_HC , array($ts, null ));
      array_push ( $navigator , array($ts, $val ));
      if ($courbe_max[1]<$val) {$courbe_max[1] = $val; $courbe_maxdate[1] = $ts;};
      if ($courbe_min[1]>$val) {$courbe_min[1] = $val; $courbe_mindate[1] = $ts;};
    }
    elseif ( $row["ptec"] == "HC" )      // Test si heures creuses.
    {
      $val = floatval(str_replace(",", ".", $row["papp"]));
      array_push ( $array_BASE , array($ts, null ));
      array_push ( $array_HP , array($ts, null ));
      array_push ( $array_HC , array($ts, $val ));
      array_push ( $navigator , array($ts, $val ));
      if ($courbe_max[2]<$val) {$courbe_max[2] = $val; $courbe_maxdate[2] = $ts;};
      if ($courbe_min[2]>$val) {$courbe_min[2] = $val; $courbe_mindate[2] = $ts;};
    }
    $val = floatval(str_replace(",", ".", $row["iinst"])) ;
    array_push ( $array_I , array($ts, $val ));
    if ($courbe_max[3]<$val) {$courbe_max[3] = $val; $courbe_maxdate[3] = $ts;};
    if ($courbe_min[3]>$val) {$courbe_min[3] = $val; $courbe_mindate[3] = $ts;};
    // récupérer prochaine occurence de la table
    $row = mysql_fetch_array($result);
    $nbenreg--;
    $nbdata++;
  }
  mysql_free_result($result);

  $date_fin = $ts/1000;

  $plotlines_max = max($courbe_max[0], $courbe_max[1], $courbe_max[2]);
  $plotlines_min = min($courbe_min[0], $courbe_min[1], $courbe_min[2]);

  $ddannee = date("Y",$date_deb);
  $ddmois = date("m",$date_deb);
  $ddjour = date("d",$date_deb);
  $ddheure = date("G",$date_deb); //Heure, au format 24h, sans les zéros initiaux
  $ddminute = date("i",$date_deb);

  $ddannee_fin = date("Y",$date_fin);
  $ddmois_fin = date("m",$date_fin);
  $ddjour_fin = date("d",$date_fin);
  $ddheure_fin = date("G",$date_fin); //Heure, au format 24h, sans les zéros initiaux
  $ddminute_fin = date("i",$date_fin);

  $date_deb_UTC=$date_deb*1000;
  $date_fin_UTC=$date_fin*1000;

  $datetext = "$ddjour/$ddmois  $ddheure:$ddminute au $ddjour_fin/$ddmois_fin  $ddheure_fin:$ddminute_fin";

  $seuils = array (
    'min' => $plotlines_min,
    'max' => $plotlines_max,
  );
  
  return array(
    'title' => "Graph du $datetext",
    'subtitle' => "",
    'debut' => $date_deb_UTC,
    'fin' => $date_fin_UTC,
    'tsdebut' => $timestampdebut,
    'tsfin' => $timestampfin,
    'BASE_name' => $courbe_titre[0]." / min ".$courbe_min[0]." max ".$courbe_max[0],
    'BASE_data'=> $array_BASE,
    'HP_name' => $courbe_titre[1]." / min ".$courbe_min[1]." max ".$courbe_max[1],
    'HP_data' => $array_HP,
    'HC_name' => $courbe_titre[2]." / min ".$courbe_min[2]." max ".$courbe_max[2],
    'HC_data' => $array_HC,
    'I_name' => $courbe_titre[3]." / min ".$courbe_min[3]." max ".$courbe_max[3],
    'I_data' => $array_I,
    'JPrec_name' => 'Période précédente', //'Hier',
    'JPrec_data' => $array_JPrec,
    'navigator' => $navigator,
    'seuils' => $seuils,
    'tarif_type' => $tarif_type
    );
}

/*************************************************************/
/*    Graph cout sur période [8jours|8semaines|8mois|1an]    */
/*************************************************************/
function historyElec() {
  global $table;
  global $abo_annuel;
  global $prixBASE;
  global $prixHP;
  global $prixHC;
  global $tarif_type;

  $regroupement = isset($_GET['regroupement'])?$_GET['regroupement']:"j";
  $datedebut = isset($_GET['datedebut'])?$_GET['datedebut']:null;
  $datefin = isset($_GET['datefin'])?$_GET['datefin']:null;
  
  if (isset($datefin)) {
  
    $timestampheurefin = min($datefin, time());
  
    if (isset($datedebut))
    {
        $timestampheuredebut = min($datedebut, time());
    } else {
        $timestampheuredebut = null;
    }
  }
  else 
  {
    $heurecourante = date('H') ;              // Heure courante.
	$timestampheurefin = mktime(23,59,59,date("m"),date("d"),date("Y"));  // Timestamp courant à heure fixe (mn et s à 0).
    $timestampheuredebut = null; 
  }
  
  if ($timestampheuredebut == null) {
  switch ($regroupement) {
    case "j":
        $timestampheuredebut = $timestampheurefin - (24*60*60*7);
    break;
    case "s":
        $timestampheuredebut = mktime(0,0,0,date("m", $timestampheurefin), date("d",$timestampheurefin)-(7*7), date("Y",$timestampheurefin));
    break;
    case "m":
        $timestampheuredebut = mktime(0,0,0,date("m", $timestampheurefin)-7, 1, date("Y",$timestampheurefin));
    break;
    case "a":
        $timestampheuredebut = mktime(0,0,0,date("m", $timestampheurefin), 1, (date("Y",$timestampheurefin)-1));
    break;
  }
  }
  
  // Garde fou minuit 23:59:59
  $timestampheurefin = mktime(23,59,59,date("m",$timestampheurefin),date("d",$timestampheurefin),date("Y",$timestampheurefin));
  $timestampheuredebut = mktime(0,0,0, date("m", $timestampheuredebut),date("d", $timestampheuredebut),date("Y", $timestampheuredebut));

  
  switch ($regroupement) {
    case "j":
        // Jour

        $xlabel = floor(($timestampheurefin-$timestampheuredebut)/3600/24)." jours (du ".date("d/m/Y",$timestampheuredebut)." au ".date("d/m/Y",$timestampheurefin).")";
        $dateformatsql = "%a %e/%c/%Y" ;
        $abonnement = $abo_annuel / 365;
    break;
    case "s":
        // Semaine
        while ( date("w", $timestampheuredebut) != 1 )  // Recule d'un jour jusqu'a un lundi
        {
            $timestampheuredebut = gmmktime(0,0,0, date("m", $timestampheuredebut), date("d", $timestampheuredebut)-1, date("Y", $timestampheuredebut));
        }
        $nbanne = date("Y", $timestampheurefin)-date("Y", $timestampheuredebut);
        $nbsemaine = date("W", $timestampheurefin)-date("W", $timestampheuredebut);
        
        $nbsemaine = $nbsemaine + ($nbanne*52) + 1;
        
        $xlabel = $nbsemaine." semaines (du ".date("d/m/Y",$timestampheuredebut)." au ".date("d/m/Y",$timestampheurefin).")";
        $dateformatsql = "%v/%Y" ;
        $abonnement = $abo_annuel / 52;
    break;
    case "m":
        // Mois
        
        // Premier jour du mois
        $timestampheuredebut = mktime(0,0,0,date("m", $timestampheuredebut),1,date("Y", $timestampheuredebut));
        
        $nbanne = date("Y", $timestampheurefin)-date("Y", $timestampheuredebut);
        $nbmois = date("m", $timestampheurefin)-date("m", $timestampheuredebut);
        
        $nbmois = $nbmois + ($nbanne*12) + 1;


        $xlabel = $nbmois." mois (du ".date("d/m/Y",$timestampheuredebut)." au ".date("d/m/Y",$timestampheurefin).")";
        $dateformatsql = "%b/%Y" ;
        $abonnement = $abo_annuel / 12;
    break;
    case "a":
        // Annee
        // Premier jour de l'année
        $timestampheuredebut = mktime(0,0,0,1,1,date("Y", $timestampheuredebut));
        

        $xlabel = (date("Y", $timestampheurefin)-date("Y", $timestampheuredebut)+1)." ans (du ".date("d/m/Y",$timestampheuredebut)." au ".date("d/m/Y",$timestampheurefin).")" ;
        $dateformatsql = "%Y" ;
        $abonnement = $abo_annuel;
    break;
  }

  $query="SET lc_time_names = 'fr_FR'" ;  // Pour afficher date en français dans MySql.
  mysql_query($query) ;

  $query = queryHistoryElec($timestampheuredebut, $timestampheurefin, $dateformatsql); 
  
  $result=mysql_query($query) or die ("<b>Erreur</b> dans la requète <b>" . $query . "</b> : "  . mysql_error() . " !<br>");
  $num_rows = mysql_num_rows($result) ;
  $no = 0 ;
  $date_deb=0; // date du 1er enregistrement
  $date_fin=time();

  while ($row = mysql_fetch_array($result))
  {
    if ($date_deb==0) {
      $date_deb = strtotime($row["rec_date"]);
    }
    $date[$no] = $row["rec_date"] ;
    $timestp[$no] = $row["periode"] ;
    $kwhbase[$no]=floatval(str_replace(",", ".", $row["base"]));
    $kwhhp[$no]=floatval(str_replace(",", ".", $row["hp"]));
    $kwhhc[$no]=floatval(str_replace(",", ".", $row["hc"]));
    $no++ ;
  }
  $date_digits_dernier_releve=explode("-", $date[count($date) -1]) ;
  $date_dernier_releve =  Date('d/m/Y', gmmktime(0,0,0, $date_digits_dernier_releve[1] ,$date_digits_dernier_releve[2], $date_digits_dernier_releve[0])) ;

  mysql_free_result($result);

  $ddannee = date("Y",$date_deb);
  $ddmois = date("m",$date_deb);
  $ddjour = date("d",$date_deb);
  $ddheure = date("G",$date_deb); //Heure, au format 24h, sans les zéros initiaux
  $ddminute = date("i",$date_deb);

  $date_deb_UTC=$date_deb*1000;

  $datetext = "$ddjour/$ddmois/$ddannee  $ddheure:$ddminute";
  $ddmois=$ddmois-1; // nécessaire pour Date.UTC() en javascript qui a le mois de 0 à 11 !!!

  $mnt_kwhbase = 0;
  $mnt_kwhhp = 0;
  $mnt_kwhhc = 0;
  $mnt_abonnement = 0;
  $i = 0;
  while ($i < count($kwhhp))
  {
    $mnt_kwhbase += $kwhbase[$i] * $prixBASE;
    $mnt_kwhhp += $kwhhp[$i] * $prixHP;
    $mnt_kwhhc += $kwhhc[$i] * $prixHC;
    $mnt_abonnement += $abonnement;
    $i++ ;
  }

  $mnt_total = $mnt_abonnement + $mnt_kwhbase + $mnt_kwhhp + $mnt_kwhhc;

  $prix = array (
    'abonnement' => $abonnement,
    'BASE' => $prixBASE,
    'HP' => $prixHP,
    'HC' => $prixHC,
  );

  if ($tarif_type == "HCHP") {
    $subtitle = "Coût sur la période ".round($mnt_total,2)." Euro<br />( Abonnement : ".round($mnt_abonnement,2)." + HP : ".round($mnt_kwhhp,2)." + HC : ".round($mnt_kwhhc,2)." )";
  } else {
    $subtitle = "Coût sur la période ".round($mnt_total,2)." Euro<br />( Abonnement : ".round($mnt_abonnement,2)." + BASE : ".round($mnt_kwhbase,2)." )";
  }
  
  return array(
    'title' => "Consomation sur $xlabel",
    'subtitle' => $subtitle,
    'debut' => $date_deb_UTC,
    'tsdebut' => $timestampheuredebut,
    'tsfin' => $timestampheurefin,
    'BASE_name' => 'Heures de Base',
    'BASE_data'=> $kwhbase,
    'HP_name' => 'Heures Pleines',
    'HP_data' => $kwhhp,
    'HC_name' => 'Heures Creuses',
    'HC_data' => $kwhhc,
    'categories' => $timestp,
    'prix' => $prix,
    'tarif_type' => $tarif_type
    );
}

/*************************************************************/
/*    Traitement de l'appel								     */
/*************************************************************/

$query = isset($_GET['query'])?$_GET['query']:"daily";
$page = isset($_GET['page'])?$_GET['page']:"";

if (isset($query)) {
  mysql_connect($serveur, $login, $pass) or die("Erreur de connexion au serveur MySql");
  // mysql_select_db($base) or die("Erreur de connexion a la base de donnees $base");
  mysql_query("SET NAMES 'utf8'");

  $newQuery = strtolower($query . $page);

  switch ($newQuery) {
  case "dailyelectricite":
    $data=dailyElec();
    break;
  case "historyelectricite":
    $data=historyElec();
    break;
  case "dailytemperatures":
    $data=dailyTemp();
    break;
  case "historytemperatures":
    $data=historyTemp();
    break;
  case "menu":
    $data=menu();
    break;
  default:
    break;
  };
  echo json_encode($data);

  mysql_close() ;
}

?>
