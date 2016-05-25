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
              new Patient(  $scope.data[j].FirstName,
                            $scope.data[j].LastName,
                            $scope.data[j].PatientID
                )
              );
          }
        }
      }
    }
    $scope.dataList = dataList;
    $scope.activeDoctorIndex = 0; // set first doctor as default
    $scope.activePatientIndex = 0; // Set first patient for first doctor
    $scope.patientID = $scope.dataList[$scope.activeDoctorIndex].patients[$scope.activePatientIndex].ID;
    //console.log($scope.dataList[$scope.activeDoctorIndex].lastName);
    $http.post("php/getPatientPhoto.php", {patientID: $scope.patientID})
    .then( function (response) {
      $scope.patientPhoto = response.data;
    });
  });

  // Grab the data for each patient
  $scope.updateActivePatient = function(lookupID){
    var storedID, found = false;
    for (var i = 0; i < $scope.dataList.length && !found; i++){
      for (var j =0; j < $scope.dataList[i].patients.length && !found; j++){
        storedID = $scope.dataList[i].patients[j].ID;
        if (lookupID === storedID){
          found = true;
          $scope.activeDoctorIndex = i;
          $scope.activePatientIndex = j;
          $scope.patientID = lookupID;
        }
      }
    }
  }

  $scope.grabPatientPhoto = function() {
    var data = {
      patientID: $scope.patientID
    };

    $http.post( "php/getPatientPhoto.php", data).then( function (response) {
      $scope.patientPhoto = response.data;
    });
  }

  $scope.nextPatient = function(){
    var isLastDoctorPatient = $scope.dataList[$scope.activeDoctorIndex].patients.length-1 === 
    $scope.activePatientIndex;
    var isLastDoctor = $scope.dataList.length-1 === $scope.activeDoctorIndex;

    if (isLastDoctor && isLastDoctorPatient){
      $scope.activeDoctorIndex = 0;
      $scope.activePatientIndex = 0;
    }
    else if (isLastDoctorPatient){
      $scope.activeDoctorIndex++;
      $scope.activePatientIndex = 0;
    } else {
      $scope.activePatientIndex++;
    }
    $scope.patientID = $scope.dataList[$scope.activeDoctorIndex].patients[$scope.activePatientIndex].ID;
  }

  $scope.previousPatient = function(){
    var isFirstDoctorPatient = $scope.activePatientIndex === 0;
    var isFirstDoctor = $scope.activeDoctorIndex === 0;

    if (isFirstDoctor && isFirstDoctorPatient){
      $scope.activeDoctorIndex = $scope.dataList.length-1;
      $scope.activePatientIndex = $scope.dataList[$scope.activeDoctorIndex]
      .patients.length-1;
    }
    else if (isFirstDoctorPatient){
      $scope.activeDoctorIndex--;
      $scope.activePatientIndex = $scope.dataList[$scope.activeDoctorIndex]
      .patients.length-1;
    } else{
      $scope.activePatientIndex--;
    }
    $scope.patientID = $scope.dataList[$scope.activeDoctorIndex].patients[$scope.activePatientIndex].ID;
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