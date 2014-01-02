
  function init_chart1_temp(data, seriesOptions) {
    return {
      chart: {
        renderTo: 'chart1',
        events: {
          load: function(chart) {
            this.setTitle(null, {
              text: 'Construit en '+ (new Date() - start) +'ms'
            });
            if ($('#chart1legende').length) {
              $("#chart1legende").html(data.subtitle);
            }
          }
        },
        borderColor: '#EBBA95',
        borderWidth: 2,
        borderRadius: 10,
        ignoreHiddenSeries: false
      },
      credits: {
        enabled: false
      },
      title: {
        text : data.title
      },
      subtitle: {
        text: 'Construit en...'
      },
      rangeSelector : {
        buttons : [{
          type : 'hour',
          count : 1,
          text : '1h'
        },{
          type : 'hour',
          count : 3,
          text : '3h'
        },{
          type : 'hour',
          count : 6,
          text : '6h'
        },{
          type : 'hour',
          count : 9,
          text : '9h'
        },{
          type : 'hour',
          count : 12,
          text : '12h'
        },{
          type : 'hour',
          count : 24,
          text : '24h'
        },{
          type : 'hour',
          count : 48,
          text : '48h'
        },{
          type : 'all',
          count : 1,
          text : 'All'
        }],
        selected : 5,
        inputEnabled : false
      },
      xAxis: {
        type: 'datetime',
         dateTimeLabelFormats: {
            hour: '%H:%M',
          	day: '%H:%M',
          	week: '%H:%M',
            month: '%H:%M'
         }
      },
      yAxis: [{
        labels: {
          formatter: function() {
             return this.value +' °C';
          }
        },
        title: {
          text: '°C'
        },
        lineWidth: 2,
        showLastLabel: true,
        //min: 0,
        alternateGridColor: '#FDFFD5',
        minorGridLineWidth: 0,
        plotLines : [{ // lignes min et max
          value : data.seuils.min,
          color : 'green',
          dashStyle : 'shortdash',
          width : 2,
          label : {
            text : 'minimum ' + data.seuils.min + '°C'
          }
        }, {
          value : data.seuils.max,
          color : 'red',
          dashStyle : 'shortdash',
          width : 2,
          label : {
            text : 'maximum ' + data.seuils.max + '°C'
          }
        }]
      }],

      series : seriesOptions,
      legend: {
        enabled: true,
        borderColor: 'black',
        borderWidth: 1,
        shadow: true
      },
      navigator: {
        baseSeries: 2,
        top: 390,
        menuItemStyle: {
          fontSize: '10px'
        },
        series: {
          name: 'navigator',
          data: data.navigator
        }
      },
      scrollbar: { // scrollbar "stylée" grise
        barBackgroundColor: 'gray',
        barBorderRadius: 7,
        barBorderWidth: 0,
        buttonBackgroundColor: 'gray',
        buttonBorderWidth: 0,
        buttonBorderRadius: 7,
        trackBackgroundColor: 'none',
        trackBorderWidth: 1,
        trackBorderRadius: 8,
        trackBorderColor: '#CCC'
      },
    }
  }
  

function init_chart2_temp(data) {
    return {
      chart: {
        renderTo: 'chart2',
        events: {
          load: function(chart) {
            this.setTitle(null, {
              text: 'Construit en '+ (new Date() - start) +'ms'
            });
            if ($('#chart2legende').length) {
              $("#chart2legende").html(data.subtitle);
            }
          }
        },
        defaultSeriesType: 'column',
        ignoreHiddenSeries: false
      },
      credits: {
        enabled: false
      },
      title: {
        text : data.title
      },
      subtitle: {
        text: 'Construit en...'
      },
      xAxis: [{
         categories: data.categories,
         labels: { x:5, y : 10, rotation: -45, align: 'right' }
      }],
      yAxis: {
        title: {
          text: '°C'
        },
        min: 0,
        minorGridLineWidth: 0,
        labels: { formatter: function() { return this.value +' °C' } }
      },
      tooltip: {
        formatter: function() {

          tooltip = '<b> '+ this.x +' <b><br /><b>'+ this.series.name +' '+ Highcharts.numberFormat(this.y, 2) +' °C<b><br />';
          return tooltip;
        }
      },
      plotOptions: {
        column: {
          stacking: 'normal',
        }
      },
      series: [{
        name : data.BASE_name,
        data : data.BASE_data,
        events: {
          click: function(e) {
            var newdate = new Date();
            newdate.setTime (data.debut);
            newdate.setDate(newdate.getDate()+e.point.x);
          }
        },
        dataLabels: {
          enabled: true,
          color: '#FFFFFF',
          y: 13,
          formatter: function() {
            return this.y;
          },
          style: {
            font: 'normal 13px Verdana, sans-serif'
          }
        },
        type: 'column',
        //type: 'areaspline',
        showInLegend: true
      }],
      navigation: {
        menuItemStyle: {
          fontSize: '10px'
        }
      }
    }
  }

  function refresh_chart1_temp(page, datedebut, datefin, avecMeteo) {
  	
    var complement = (menuId[currentMenu]!=null)?"&id="+menuId[currentMenu]:"";
    
    complement = complement + ((avecMeteo == true)?"&avecMeteo=1":"");
    
    // remise à zéro du chronomètre
    start = new Date();
    
    // on relance la requête historique
    $.getJSON('json.php?query=daily&datedebut='+parseInt(datedebut.getTime()/1000)+'&datefin='+parseInt(datefin.getTime()/1000)+'&page='+page+complement, function(data) {
    	
      var seriesOptions = [];

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
    	  	
      // Remplacement du graphique

      chart_elec1= new Highcharts.StockChart(init_chart1(page, data, seriesOptions));
      
      $("#DateDebut").val(new Date(Math.round(data.tsdebut*1000)).format('dd/MM/yyyy'));
      $("#DateFin").val(new Date(Math.round(data.tsfin*1000)).format('dd/MM/yyyy'));
    });
  }
  
  function refresh_chart2_temp(page, periode) {
    
    if (!chart2inload) {
    
        chart2inload = true;
    
    	var complement = (menuId[currentMenu]!=null)?"&id="+menuId[currentMenu]:"";
    
        // remise à zéro du chronomètre
        start = new Date(); 
    
        // on relance la requête historique
        $.getJSON('json.php?query=history&regroupement='+periode+'&page='+page+complement, function(data) {
          // Remplacement du graphique
          chart_elec2 = new Highcharts.Chart(init_chart2(page, data));
          
          $("#DateDebut2").val(new Date(Math.round(data.tsdebut*1000)).format('dd/MM/yyyy'));
          $("#DateFin2").val(new Date(Math.round(data.tsfin*1000)).format('dd/MM/yyyy'));
          $("#typeGraph").val(periode);
          chart2inload = false;
        });
    } else {
        if (window.console) console.log('Double click');
    }
  }
  
  function refresh_chart2btn_temp(page, periode, datedebut, datefin) {
    
    if (!chart2inload) {
    
        chart2inload = true;
        
    	var complement = (menuId[currentMenu]!=null)?"&id="+menuId[currentMenu]:"";
    
        // remise à zéro du chronomètre
        start = new Date();
    
        // on relance la requête historique
        $.getJSON('json.php?query=history&regroupement='+periode+'&datedebut='+parseInt(datedebut.getTime()/1000)+'&datefin='+parseInt(datefin.getTime()/1000)+'&page='+page+complement, function(data) {
          // Remplacement du graphique
          chart_elec2 = new Highcharts.Chart(init_chart2(page, data));
          
          $("#DateDebut2").val(new Date(Math.round(data.tsdebut*1000)).format('dd/MM/yyyy'));
          $("#DateFin2").val(new Date(Math.round(data.tsfin*1000)).format('dd/MM/yyyy'));
          $("#typeGraph").val(periode);
          chart2inload = false;
    
        });
    } else {
        if (window.console) console.log('Double click');
    }
  }
