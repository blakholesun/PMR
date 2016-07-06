angular.module('myApp').factory('chartService', ['$http', function ($http) {

  return function(element,patientId){

    $http.post("php/getPlanningTimes2.php", {patientID: patientId})
    .then( function (response) {

      var times = {};
      times.planTimes = response.data;
      sequenceLength = times.planTimes.sequence.length;
      console.log(times.planTimes.sequence);

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
            text: 'Total planning time: <strong>' + 
            times.planTimes.planTimes.reduce((pv, cv) => pv+cv, 0).toFixed(2) +
            ' </strong>business days'
          },

          xAxis: {
            labels: {
              style: {
                fontSize : '15px',
                color:'#000' 
              }
            },
            categories: [
            'Consult-<br>CTsim',
            'CTsim-<br>MDContour',
            'MDContour-<br>DoseCalc', 
            'DoseCalc-<br>ReadyTreat']
          },

          yAxis: {
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
            shared: true,
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
              low: Date.parse(times.planTimes.sequence[4]['JSDate']),
              high: Date.parse(times.planTimes.sequence[3]['JSDate']),
            }, {
              x: 1,
              low: Date.parse(times.planTimes.sequence[3]['JSDate']),
              high: Date.parse(times.planTimes.sequence[2]['JSDate']),
            }, {
              x: 2,
              low: Date.parse(times.planTimes.sequence[2]['JSDate']),
              high: Date.parse(times.planTimes.sequence[1]['JSDate']),
            }, {
              x: 3,
              low: Date.parse(times.planTimes.sequence[1]['JSDate']),
              high: Date.parse(times.planTimes.sequence[0]['JSDate']),
            }]

          }]

        });
}
});
}
}]);