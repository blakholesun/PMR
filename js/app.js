var app = angular.module('myApp', []);

//Populated the table
app.controller('makeMenu', function($scope, $http) {
  $http.get("http://localhost/PMR/php/getPatientData_local.php").then(function (response) {
    $scope.data = response.data.things;
    
    var doctors = [];
    for (var x in $scope.data){
      if ($scope.data[x].owner)
      console.log($scope.data[x]);
    }

    /*function Doctor(doctorName, patientNames){
      this.doctorName = doctorName;
      this.patientNames = patientNames; */
    })

});

// Custom filter to get unique values in a column
/*app.filter("uniqueValues", function(){
  return function(collection, keyname){
    var output = [];
    var keys = [];

    angular.forEach(collection, function(item){
      var key = item[keyname];
      if(keys.indexOf(key)==-1){
        keys.push(key);
        output.push(item);
      }
    });
    return output;
  };
});*/

// Controller to toggle the view wrt collapsible button selection
//TODO Make it modular!
app.controller('infoCtrl', function($scope){
	$scope.myCarousel = true;
  $scope.toggle = function() {
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