$('#progress').highcharts({
          chart: {
            style: {
              fontSize: '18px'
            },
            type: 'column'
          },
          title: {
            text: 'Patient Planning Times',
          },
          xAxis: {
            type: 'category',
            labels: {
              style: {
                fontSize : '15px'  
              }
            }
          },
          yAxis: {
            title:{
              text:'Time [days]'
            }
          },
          legend: {
            enabled: false
          },
          tooltip: {
            enabled :false
            //pointFormat: '{series.name}: <b>{point.y:.1f} days</b>'
          },
          plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    format: '{point.y:.1f} days'
                }
            }
          },
          series: [{
            name: 'Planning Time',
            colorByPoint: true,
            //innerSize: '50%',
            data: [{
                name: '1) Consult-CTSim',
                y: $scope.planTimes.planTimes[3],
                drilldown: 'cc'
            }, {
                name: '2) CTSim-MD Contour',
                y: $scope.planTimes.planTimes[2],
                drilldown: 'cmd'
            }, {
                name: '3) MD Contour-Dose Calculation',
                y: $scope.planTimes.planTimes[1],
                drilldown: 'mddc'
            }, {
                name: '4) Dose Calculation-Treatment',
                y: $scope.planTimes.planTimes[0],
                drilldown: 'dct'
            }],
            /*data: [
              ['1) Consult-CTSim', $scope.planTimes.planTimes[3]],
              ['2) CTSim-MD Contour', $scope.planTimes.planTimes[2]],
              ['3) MD Contour-Dose Calculation', $scope.planTimes.planTimes[1]],
              ['4) Dose Calculation-Treatment', $scope.planTimes.planTimes[0]],
            ]*/
          }],
          drilldown: {
            xAxis:{

            },
            series: [{
                id: 'cc',
                data: [
                    [$scope.planTimes.sequence[4].ActivityCode, 2
                    ],
                    [$scope.planTimes.sequence[3].ActivityCode, 2
                    ]
                ]
            }, {
                id: 'cmd',
                data: [
                    [$scope.planTimes.sequence[3].ActivityCode, 2
                    ],
                    [$scope.planTimes.sequence[2].ActivityCode, 2
                    ]
                ]
            }, {
                id: 'mddc',
                data: [
                    [$scope.planTimes.sequence[2].ActivityCode, 2
                    ],
                    [$scope.planTimes.sequence[1].ActivityCode, 2
                    ]
                ]
            }, {
                id: 'dct',
                data: [
                    [$scope.planTimes.sequence[1].ActivityCode, 2
                    ],
                    [$scope.planTimes.sequence[0].ActivityCode, 2
                    ]
                ]
            }]
          }
        });
      }
    });
  }

});