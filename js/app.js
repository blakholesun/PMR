var app = angular.module('myApp', []);

//Load the patient list
app.controller('getPatientData', ['$http','$rootScope', '$scope', function($http,$rootScope){
  $http.get("http://localhost/PMR/php/getPatientData_local.php").then(function (response) {
    $rootScope.data = response.data.things;
    /*console.log($rootScope.data);
    for (var x in $rootScope.data){
      console.log($rootScope.data[x]);
    }*/
  })
}]);

//Populate the navrbar menu by doctor=>patient
app.controller('makeMenu', [ '$scope', function($scope) {
    // Create a doctor class
    function Doctor(dname, patients) {
      this.dname = dname;
      this.patients = [patients];
    }

    // Create an array of doctors that will contain the list of patients
    var dataList = [];
    for (var x in $scope.data){
      //console.log($rootScope.data[x]);
      if (dataList.indexOf(x.owner) = -1){
        dataList.push(new Doctor(x.owner, x.name));
      }
      //console.log(dataList[0].dname);
    }
    $scope.dataList = dataList;
    console.log($scope.data);
}]);

//Generate the analytics panel
app.controller('makeAnalytics', function($scope){
  
});

// Controller to toggle the view wrt collapsible button selection
//TODO Make it modular!
app.controller('infoCtrl', function($scope){
	$scope.myCarousel = true;
  $scope.toggle = function() {
   $scope.myCarousel = !$scope.myCarousel;
 }
});

// Controller to request the ptient's data from the server
/*app.controller('patientDataCtrl', function($scope, $http) {
    $http.get("welcome.htm")
    .then(function(response) {
        $scope.PatientToDisplay = response.data;
    });
*/