
var start = new Date();
var chart1inload = false;
var chart2inload = false;
var menuId = new Array();
var currentTab;
var currentMenu;

jQuery(function($) {
  Highcharts.setOptions({
    lang: {
      months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
      weekdays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
      decimalPoint: ',',
      thousandsSep: '.',
      rangeSelectorFrom: 'Du',
      rangeSelectorTo: 'au'
    },
    legend: {
      enabled: false
    },
    global: {
      useUTC: false
    }
  });
});

function GetURLParameter(sParam) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');

    for (var i = 0; i < sURLVariables.length; i++)
    {
        var sParameterName = sURLVariables[i].split('=');
        
        if (sParameterName[0] == sParam)
        {
            return sParameterName[1];
        }
    }
    
    return null;
}

var chart_elec1;
var chart_elec2;

$(document).ready(function() {
    
    $( "#DateDebut" ).datepicker({ dateFormat: 'dd/mm/yy' });
	$( "#DateFin" ).datepicker({ dateFormat: 'dd/mm/yy' });

	$( "#DateDebut2" ).datepicker({ dateFormat: 'dd/mm/yy' });
	$( "#DateFin2" ).datepicker({ dateFormat: 'dd/mm/yy' });
	
	$( "#main button" )
		.button()
		.click(function( event ) {
 			event.preventDefault();
		}
	);
	
	$('.button_chart1').click(function() {

	    curdate = chart_elec1.series[0].xData[chart_elec1.series[0].xData.length-1];
	    var datefin = new Date(curdate);
	    
	    var datedebut = new Date(curdate);
	    
	    switch (this.value)
	    {
	      case "1prec":
	        datefin.setDate(datefin.getDate()-1);
	        break;
	      case "1suiv":
	        datefin.setDate(datefin.getDate()+1);
	        break;
	      case "now":
	        datefin = new Date();
	        datefin.setHours(datefin.getHours()+1);
	        break;
	    }
	    
	    datedebut.setDate(datefin.getDate()-1);
	    datedebut.setHours(datefin.getHours());
	    datedebut.setMinutes(0);
	    datedebut.setSeconds(0);
	
	    refresh_chart1(currentTab, datedebut, datefin);
	});
	  
	  $('#btnRefresh').click(function() {
	    refresh_chart1(currentTab, Date.parseString($("#DateDebut").val(),'dd/MM/yyyy'), Date.parseString($("#DateFin").val(),'dd/MM/yyyy'));
	  });
	
	  $('.button_chart2').click(function() {
	    refresh_chart2(currentTab, this.value);
	  });
	  
	  $('#btnRefresh2').click(function() {
	    refresh_chart2btn(currentTab, $("#typeGraph").val(), Date.parseString($("#DateDebut2").val(),'dd/MM/yyyy'), Date.parseString($("#DateFin2").val(),'dd/MM/yyyy'));
	  });
	
		
    var tabSelected = (GetURLParameter("page")!=null)?GetURLParameter("page"):0;
    currentMenu = (GetURLParameter("menu")!=null)?GetURLParameter("menu"):0;
  
   	$.getJSON('json.php?query=menu',{},function(data) {
	    	var ul = $("#menuTemp")
	        	/*.attr("id",data.menuTemp.id)
	        	.addClass(data.menu.class)*/;
	        	
	        // ajout du menu group	
	        var li = $("<li/>")
	            	.appendTo(ul);
	        var a = $("<a/>")
	            	.appendTo(li)
	            	.attr("href", "#")
	            	.text("Ensemble");
	            	
	        if (currentMenu==0)
	        {
	        	a.addClass('ui-state-active');
	        }
	        
	        menuId[0] = null;	
	        var i = 1;
	        	
	    	$.each(data.menuTemp, function() {
	        	var li = $("<li/>")
	            	.appendTo(ul);
	        	var a = $("<a/>")
	            	.appendTo(li)
	            	.attr("href", "#")
	            	.text(this.nom);
	            	
	            if (currentMenu==i)
	        	{
	        		a.addClass('ui-state-active');
	        	}
	        
	            menuId[i] = this.cle;
	            i++;
	            
	    	});
	    	
	    	$( "#menuTemp" ).menu({
	    	 	select: function( event, ui ) {
	    	 		$('#menuTemp .ui-state-active').removeClass('ui-state-active');
      				ui.item.find('a').addClass('ui-state-active');	  		
	    	 		currentMenu = $(ui.item).index();
	    	 		load_json(currentTab);
	    	 	}
	    	
	    	});
	    	
	    	//$(‘#menuTemp').css('width', function(index) {
		//	  	return parseInt($('.ui-menu').css('width')) * i + "px";
		//});

	});
    
    
    $( "#tabs" ).tabs({
      active: tabSelected,
      beforeLoad: function( event, ui ) {
        ui.jqXHR.error(function() {
          ui.panel.html(
            "Couldn't load this tab. We'll try to fix this as soon as possible. " +
            "If this wouldn't be a demo." );
        });
      },
      activate: function( event, ui ) {
      		start = new Date();
      		currentTab = $(ui.newTab).text();
      		load_menu($(ui.newTab).index());
      		load_json(currentTab);	
      },
      create: function( event, ui ) {
      		start = new Date();
      		currentTab = $(ui.tab).text();
      		load_menu(($(ui.newTab).index()==-1)?tabSelected:$(ui.newTab).index());
      		load_json(currentTab);      		
      }
  	});
});
    
 function load_menu(page) {
 	if (page == 2)
    {
		$( "#menuTemp" ).show();
		//$( "#DateDebut" ).hide();
		
		$( "#AvecMeteo" ).show();
    	
    } else {
    	$( "#menuTemp" ).hide();
//$( "#DateDebut" ).show();

		$( "#AvecMeteo" ).hide();
    }

 }
	
	
  function load_json(page) {
  		
  	var complement = (menuId[currentMenu]!=null)?"&id="+menuId[currentMenu]:"";

	$.getJSON('json.php?query=daily&page='+page+complement, function(data) {

		var seriesOptions = [];

		if (data.temps != null) {

			$.each(data.temps, function(i) {
  	
  					seriesOptions[i] = {
						name: this.id_name,
						data: this.id_data,
						id: this.id_name,
			         		threshold : null,
			         		tooltip : { yDecimals : 2 },
			         		showInLegend: true,
			         		type: 'spline'
					};

			});
		}

		// Create the chart
		chart_elec1 = new Highcharts.StockChart(init_chart1(page, data, seriesOptions));

		$("#DateDebut").val(new Date(Math.round(data.tsdebut*1000)).format('dd/MM/yyyy'));
		$("#DateFin").val(new Date(Math.round(data.tsfin*1000)).format('dd/MM/yyyy'));
	});
	
	$.getJSON('json.php?query=history&page='+page+complement, function(data) {
		// Create the chart
		chart_elec2 = new Highcharts.Chart(init_chart2(page, data));
	
		$("#DateDebut2").val(new Date(Math.round(data.tsdebut*1000)).format('dd/MM/yyyy'));
		$("#DateFin2").val(new Date(Math.round(data.tsfin*1000)).format('dd/MM/yyyy'));

	});  
  }

  function init_chart1(page, data, seriesOptions) {
  	if (page == 'Temperatures')
  	{
  		return init_chart1_temp(data, seriesOptions);
  	} else {
  		return init_chart1_elec(data, seriesOptions); 
  	}
  }
  
  function refresh_chart1(page, datedebut, datefin) {
  	if (page == 'Temperatures')
  	{
  		return refresh_chart1_temp(page, datedebut, datefin, $("#AvecMeteo").is(':checked'));
  	} else {
  		return refresh_chart1_elec(page, datedebut, datefin); 
  	}
  }
  
  function init_chart2(page, data) {
  	if (page == 'Temperatures')
  	{
  		return init_chart2_temp(data);
  	} else {
  		return init_chart2_elec(data); 
  	}
  }
  
  function refresh_chart2(page, periode) {
  	if (page == 'Temperatures')
  	{
  		return refresh_chart2_temp(page, periode);
  	} else {
  		return refresh_chart2_elec(page, periode); 
  	}
  }
  
  function refresh_chart2btn(page, periode, datedebut, datefin) {
    if (page == 'Temperatures')
  	{
  		return refresh_chart2btn_temp(page, periode, datedebut, datefin);
  	} else {
  		return refresh_chart2btn_elec(page, periode, datedebut, datefin); 
  	}
  }
  

