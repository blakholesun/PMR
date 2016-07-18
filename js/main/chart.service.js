angular.module('myApp').factory('chartService', ['$http', function ($http) {

  return function(element,patientId,SGAS){

    $http.post("php/getPlanningTimes2", {patientID: patientId})
    .then( function (response) {

      var times = {};
      times.planTimes = response.data;
      sequenceLength = times.planTimes.sequence.length;
      console.log(times.planTimes.sequence);
      //console.log(SGAS.DueDate);

      var getColorArray = function(times, SGAS){
        var green = '#76FF03';
        var red = '#D50000';
        var blue = '#2196F3';
        var colorArray = [];

        colorArray.push(blue);
        switch(SGAS.priorityNum){
          case 1:
            var P1 = [0,0,0,0];

            for (var i = 1; i<=times.planTimes.planTimes.length - 2; i++){
              if (Math.floor(times.planTimes.planTimes[i]) > P1[i-1]){
                colorArray.push(red);
              } else{
                colorArray.push(green);
              }
            }

            break;
          case 2:
            var P2 = [2,1,0,0];
            for (var i = 1; i<=times.planTimes.planTimes.length - 2; i++){
              //console.log(Math.floor(times.planTimes.planTimes[i])+ " >= " + P2[i-1]);
              //console.log(Math.floor(times.planTimes.planTimes[i]) >=  P2[i-1]);
              if (times.planTimes.planTimes[i] > P2[i-1]+1){
                colorArray.push(red);
              } else{
                colorArray.push(green);
              }
            }
            break;
          case 3:
            var P3 = [5,3,1,2];
            console.log(times.planTimes.planTimes);
            for (var i = 1; i<=times.planTimes.planTimes.length - 2; i++){
              if (times.planTimes.planTimes[i] > P3[i-1]){
                colorArray.push(red);
              } else{
                colorArray.push(green);
              }
            }
            break;
          case 4:
            var P4 = [5,5,2,3];
            for (var i = 1; i<=times.planTimes.planTimes.length - 2; i++){
              if (times.planTimes.planTimes[i] > P4[i-1]){
                colorArray.push(red);
              } else{
                colorArray.push(green);
              }
            }
            break;
        }
        colorArray.push(blue);
        console.log(colorArray);
        return colorArray.reverse();
      }

      var elapsed = function(times, SGAS){

        var consultDate = new Date(times.planTimes.sequence[
          times.planTimes.sequence.length-1]['JSDate']);
        console.log(consultDate);
        console.log(SGAS.MedicallyReady);
        var medReady = SGAS.MedicallyReady.getTime();
        if (medReady < consultDate){
          medReady = consultDate;
        }
        consultToMR = (medReady - consultDate)/(24*3600*1000);
        consultToNew = times.planTimes.planTimes.reduce((pv, cv) => pv+cv, 0).toFixed(0);
        if (consultToNew < consultToMR){
          return consultToNew;
        }else {
          return (consultToNew - consultToMR).toFixed(2);
        }
      }

      if (!times.planTimes.hasOwnProperty('planTimes')){
        var newHTML = ['<div><h4 class="text-danger" style="text-align:center">'+
        'Cannot generate plot. Here are the events found</h4>' +
        '<table class="table table-striped table-hover">' +
        '<thead>' +
        '<tr>' +
        '<th>Event</th>' +
        '<th>Date</th>' +
        '</tr>' +
        '</thead><tbody>'];
        for (var i = 0; i < sequenceLength; i++) {
          newHTML.push('<tr><td><strong>' + times.planTimes.sequence[i]['ActivityCode'] + 
            '</strong></td><td>'+ times.planTimes.sequence[i]['JSDate'] +'</td></tr>');
        }
        newHTML.push('</tbody></table></div>')
        $(element).html(newHTML.join(""));

      }else{

        $(element).highcharts({

          colors: getColorArray(times,SGAS),
          chart: {
            style: {
              fontSize: '18px',
              color: "#000"
            },
            type: 'columnrange',
            zoomType: 'y',
            inverted: true
          },

          title: {
            text: 'Elapsed time from Medically Ready: <strong>' + 
            elapsed(times,SGAS) +
            ' </strong> days'
          },

          xAxis: {
            labels: {
              style: {
                fontSize : '15px',
                color:'#000' 
              }
            },
            categories: [
            'ConsultReferral<br>ROConsult',/*<br><strong>' + 
              times.planTimes.planTimes[5].toFixed(2) +' days</strong>',*/
            'ROConsult<br>CTsim',
            'CTsim<br>MDContour',
            'MDContour<br>DoseCalc', 
            'DoseCalc<br>ReadyTreat',
            'ReadyTreat<br>NewStart']
          },

          yAxis: {
            plotLines: [{
              label: { 
                text: 'MR', // Content of the label. 
                align: 'center', // Positioning of the label. 
                verticalAlign: 'bottom',//Default to center. x: +10 // Amount of pixels the label will be repositioned according to the alignment. 
                //rotation: 0,
                y:-15
              },
              color: 'red', // Color value
              //dashStyle: 'longdashdot', // Style of the plot line. Default to solid
              value: SGAS.MedicallyReady, // Value of where the line will appear
              width: 2, // Width of the line    
              zIndex: 3,
              dashStyle: 'ShortDot'
            },{
              label: { 
                text: 'Due', // Content of the label. 
                align: 'center', // Positioning of the label. 
                verticalAlign: 'top',//Default to center. x: +10 // Amount of pixels the label will be repositioned according to the alignment. 
                //rotation:0,
                y:+15
              },
              color: 'red', // Color value
              //dashStyle: 'longdashdot', // Style of the plot line. Default to solid
              value: SGAS.DueDate, // Value of where the line will appear
              width: 2, // Width of the line
              zIndex: 3,
              dashStyle: 'ShortDot'
            }],
            labels: {
              style: {
                fontSize : '15px',
                color:'#000'
              }
            },
            title: {
              text: 'Date'
            },
            type: 'datetime'
          },

          tooltip: {
            useHTML:true,
            //shared: true,
            enabled :true,
            followpointer: true,
            headerFormat: '',
            pointFormatter: function(){
              return '<div style="font-size:15px"><strong>Time in step: ' + 
              ((this.high-this.low)/(24*3600*1000)).toFixed(2) + ' days</strong><br>'+
              times.planTimes.sequence[sequenceLength-this.x-1]['ActivityCode'] + ': ' +
              times.planTimes.sequence[sequenceLength-this.x-1]['JSDate'] + '<br>' +
              times.planTimes.sequence[sequenceLength-this.x-2]['ActivityCode'] + ': ' + 
              times.planTimes.sequence[sequenceLength-this.x-2]['JSDate']+ '<br>' +
              '</div>';
            }
          },

          plotOptions: {
            columnrange: {
              colorByPoint: true,
              dataLabels: {
                enabled: true,
                useHTML: false,
                formatter: function() {
                  
                  return Highcharts.dateFormat('%e %b', this.y);
                  
                }
              }
            },
            series: {
              shadow: true
            }
          },

          legend: {
            enabled: false
          },

          series: [{
            name: 'Dates',
            data: [{
              x: 0,
              low: Date.parse(times.planTimes.sequence[6]['JSDate']),
              high: Date.parse(times.planTimes.sequence[5]['JSDate']),
            }, {
              x: 1,
              low: Date.parse(times.planTimes.sequence[5]['JSDate']),
              high: Date.parse(times.planTimes.sequence[4]['JSDate']),
            }, {
              x: 2,
              low: Date.parse(times.planTimes.sequence[4]['JSDate']),
              high: Date.parse(times.planTimes.sequence[3]['JSDate']),
            }, {
              x: 3,
              low: Date.parse(times.planTimes.sequence[3]['JSDate']),
              high: Date.parse(times.planTimes.sequence[2]['JSDate']),
            }, {
              x: 4,
              low: Date.parse(times.planTimes.sequence[2]['JSDate']),
              high: Date.parse(times.planTimes.sequence[1]['JSDate']),
            }, {
              x: 5,
              low: Date.parse(times.planTimes.sequence[1]['JSDate']),
              high: Date.parse(times.planTimes.sequence[0]['JSDate']),
            }]

          }]

        });
}
});
}
}]);