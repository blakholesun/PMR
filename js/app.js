var app = angular.module('myApp', []);

// Page controller
app.controller('pageController', function($http,$scope,$timeout){
  // Grab the list of patients and categorize by doctor
  $http.get("http://localhost/PMR/php/getPatientData_local.php").then(function (response) {
    $scope.data=response.data.things;
    function Doctor(){
      this.firstName = "";
      this.patients = [];
    }
    var dataList = []; // the list of doctor objects with patients
    var docs = []; // create an array to hold doctor names
    var index;
    for (var i = 0; i < $scope.data.length; i++){
      index = docs.indexOf($scope.data[i].owner);
      if (index === -1){
        docs.push($scope.data[i].owner);
        dataList.push(new Doctor());
        dataList[dataList.length-1].name = $scope.data[i].owner;
        console.log($scope.data[i].owner);
        for (var j = 0; j<$scope.data.length; j++){
          if (docs[docs.length-1] === $scope.data[j].owner){
            dataList[dataList.length-1].patients.push($scope.data[j].name)
          }
        }
      }
    }
    $scope.dataList = dataList;
    $scope.activePatient = 0; // First patient for first doctor
    console.log($scope.dataList);
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