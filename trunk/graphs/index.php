<?php
setlocale(LC_ALL , "fr_FR" );
date_default_timezone_set("Europe/Paris");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta content="no-cache" http-equiv="Pragma">

    

    <script type="text/javascript" src="./js/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="./js/jquery-ui-1.10.3.custom.js"></script>
    <script type="text/javascript" src="./js/datepicker.fr.js"></script>

    <!--<script type="text/javascript" src="./js/highcharts.js"></script>-->
    <script type="text/javascript" src="./js/highstock.js"></script>
    <script type="text/javascript" src="./js/modules/exporting.js"></script>
    
    <script type="text/javascript" src="./js/dateformat.js"></script>

    <script type='text/javascript' src="graphs.js"></script>
    <script type='text/javascript' src="teleinfo.js"></script>
    <script type='text/javascript' src="temperatures.js"></script>
    
    <title>Graphs</title>

<link rel="stylesheet" href="./css/themes/cupertino/jquery-ui-1.10.2.custom.min.css">
    <link rel="stylesheet" href="./ui.css">
    
    <link rel="shortcut icon" href="./favicon.ico">

  </head>
  <body>
  <div id="tabs">
  <ul>
    <li><a href="#tabs-1">Home</a></li>
    <li><a href="#main">Electricite</a></li>
    <li><a href="#main">Temperatures</a></li>
  </ul>
  <div id="tabs-1">
    <p><a href="/cgi-bin/upsstats.cgi">Onduleur(s)</a></p>
  </div>
  <div id="main">
    <ul id="menuTemp">
  	</ul>
  	<br/>
    <div style="text-align: center; width: 800px; margin: 0 auto">
        <button class="button_chart1" value="1prec">- 24h</button>
        <button class="button_chart1" value="now">Aujourd'hui</button>
        <button class="button_chart1" value="1suiv">+ 24h</button>
        <form>
            <input type="text" id="DateDebut" />
            <input type="text" id="DateFin" />
           	<input type="checkbox" id="AvecMeteo" style="display: none" />	
            <button id="btnRefresh">Refresh</button>
        </form>
    </div>
    <br />
    <div id="chart1" style="width: 800px; height: 500px; margin: 0 auto"></div>
    <br /><br />
    
    <div style="text-align: center;">
        <button class="button_chart2" value="j">Jours</button>
        <button class="button_chart2" value="s">Semaines</button>
        <button class="button_chart2" value="m">Mois</button>
        <button class="button_chart2" value="a">Ans</button>
        <form>
            <input type="text" id="DateDebut2" />
            <input type="text" id="DateFin2" />
            <select id="typeGraph">
              <option value="j" default="true">Jours</option>
              <option value="s">Semaines</option>
              <option value="m">Mois</option>
              <option value="a">Ans</option>
            </select>
            <button id="btnRefresh2">Refresh</button>
        </form>
    </div>
    <br />
    <div id="chart2" style="width: 800px; height: 400px; margin: 0 auto"></div>
	</div>
  </div>
  
  </body>
</html>
