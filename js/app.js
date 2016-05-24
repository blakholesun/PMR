var app = angular.module('myApp', []);

// Page controller
app.controller('pageController', function($http,$scope,$timeout,$filter){
  // Grab the list of patients and categorize by doctor
  $http.get("php/getPatientData.php").then(function (response) {
    $scope.data=response.data.list;
    function Doctor(firstName, lastName){
      this.firstName = firstName;
      this.lastName = lastName;
      this.patients = [];
    }
    function Patient(firstName, lastName, ID){
      this.firstName = firstName;
      this.lastName = lastName;
      this.ID = ID;
    }
    var dataList = []; // the list of doctor objects with patients
    var docs = []; // create an array to hold doctor names
    var index;
    for (var i = 0; i < $scope.data.length; i++){
      index = docs.indexOf($scope.data[i].DocLastName);
      if (index === -1){
        docs.push($scope.data[i].DocLastName);
        dataList.push(new Doctor($scope.data[i].DocFirstName,$scope.data[i].DocLastName));
        for (var j = 0; j<$scope.data.length; j++){
          if (docs[docs.length-1] === $scope.data[j].DocLastName){
            dataList[dataList.length-1].patients.push(
              new Patient(  ($scope.data[j].FirstName),
                            ($scope.data[j].LastName),
                            $scope.data[j].PatientID)
              );
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
    $http.get("http://localhost/PMR/php/getPatientDataAll.php").then(function (response) {
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