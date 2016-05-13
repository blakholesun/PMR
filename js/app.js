var app = angular.module('myApp', []);

//Populated the table
app.controller('pets', function($scope, $http) {
  $http.get("http://localhost/PMR/php/getPatientData.php").then(function (response) {
    $scope.names = response.data.things;
  });
});

// Controller to toggle the view wrt collapsible button selection
//TODO Make it modular!
app.controller('infoCtrl', function($scope){
	$scope.myTable = true;
	$scope.myCarousel = false;
  $scope.toggle = function() {
   $scope.myTable = !$scope.myTable;
   $scope.myCarousel = !$scope.myCarousel;
 }
});

// Controller to get patient list and store in scope
/*app.controller('patientListCtrl', function($scope, $http) {
    $http.get("welcome.htm")
    .then(function(response) {
        $scope.PatientList = response.data;
    });
*/

// Controller to request the ptient's data from the server
/*app.controller('patientDataCtrl', function($scope, $http) {
    $http.get("welcome.htm")
    .then(function(response) {
        $scope.PatientToDisplay = response.data;
    });
*/

// Make patient class to store data
function patient(patient_id){
  //Generic patient info, name, age, doctor, diagnosis
  $scope.PatientToDisplay.info = function(){

  };
  // List of patient docs
  $scope.PatientToDisplay.docs = function(){

  };
  
}