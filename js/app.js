var app = angular.module('myApp', []);

// Page controller
app.controller('pageController', function($http,$scope,$timeout){
  // Grab the list of patients and categorize by doctor
  $http.get("http://localhost/PMR/php/getPatientData_local.php").then(function (response) {
    $scope.data=response.data.things;

    var dataList = [];
    var index;
    for (var i = 0; i < $scope.data.length; i++){
      index = dataList.indexOf($scope.data[i].owner);
      if (index === -1){
        dataList.push($scope.data[i].owner);
      }
      index = dataList.indexOf($scope.data[i].owner);
      console.log(dataList);
    }
  });

  // Grab the data for each patient
  $scope.grabPatient = function(){
    $http.get("http://localhost/PMR/php/getPatientData_local.php").then(function (response) {
      $scope.patient=reponse;
    });
  }
});

// Controller to toggle the view wrt collapsible button selection
//TODO Make it modular!
app.controller('infoCtrl', function($scope){
	$scope.myCarousel = true;
  $scope.toggle = function() {
   $scope.myCarousel = !$scope.myCarousel;
 }
});