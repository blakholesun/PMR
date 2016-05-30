var app = angular.module('myApp', []);

// Page controller
app.controller('pageController', function($http,$scope,$timeout,$filter,$sce){
  // Grab the list of patients and categorize by doctor
  $http.get("php/getPatientData.php").then(function (response) {
    $scope.data=response.data.list;

    //Create a doctor class to store names and list of patients
    function Doctor(firstName, lastName){
      this.firstName = firstName;
      this.lastName = lastName;
      this.patients = [];
    }

    //Create a patient class to store names and id
    function Patient(firstName, lastName, ID, tumorSize, summaryStage, stageCriteria, diagnosis){
      this.firstName      = firstName;
      this.lastName       = lastName;
      this.ID             = ID;
      this.tumorSize      = tumorSize;
      this.summaryStage   = summaryStage;
      this.stageCriteria  = stageCriteria;
      this.diagnosis      = diagnosis;
      this.isReviewed     = false;
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
                            $scope.data[j].PatientID,
                            $scope.data[j].TumorSize,
                            $scope.data[j].SummaryStage,
                            $scope.data[j].StageCriteria,
                            $scope.data[j].Diagnosis
                          )
              );
          }
        }
      }
    }
    $scope.dataList = dataList;
    // set first doctor as default
    $scope.activeDoctorIndex = 0; 
    // Set first patient for first doctor
    $scope.activePatientIndex = 0; 
    $scope.patientID = $scope.dataList[0].patients[0].ID;

    //Set the completion
    $scope.patientCompletionCount = 0;

    // First patient info
    $http.post("php/getPatientPhoto.php", {patientID: $scope.patientID})
    .then( function (response) {
      $scope.patientPhoto = response.data;
    });

    // First Patient documenst
    $http.post( "php/getPatientDocuments.php", {patientID: $scope.patientID})
    .then( function (response) {
      $scope.documents = response.data
    });

  });

  // Grab the data for active patient when selecting frrom dropdown
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

  //Grabs patient info on next or previous click
  $scope.grabPatientInfo = function() {
    var data = {
      patientID: $scope.patientID
    };

    $http.post( "php/getPatientPhoto.php", data).then( function (response) {
      $scope.patientPhoto = response.data;
    });

    $http.post( "php/getPatientDocuments.php", data).then( function (response) {
      $scope.documents = response.data
      //console.log($scope.documents);
    });

  }

  // Moves to next patient and updates fields for current patient
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

  // Moves to next patient and updates fields for current patient
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

  // Grabbing the patient document that is clicked on
  $scope.displayDocument = function(filename){
    var data = {
      FileName: filename
    };

    var options = {
      pdfOpenParams: { view: 'FitH', page: '1' }
    };

    $http.post( "php/downloadPDF.php", data).then( function (response) {
      PDFObject.embed(response.data, "#Doc", options);
    });
  }

  //Filerting out the files that have consult and imrt in name
  $scope.keepOnTop = function (x) {
    var lowerCaseDoc = x.DocType.toLowerCase();
    if (lowerCaseDoc.indexOf("consult") != -1 || lowerCaseDoc.indexOf("eclipse") != -1) {
      return -1;
    }else {
      return 1;
    }
  }

  var bar = new ProgressBar.Circle(progress, {
    strokeWidth: 6,
    easing: 'easeInOut',
    duration: 1400,
    color: '#FFEA82',
    trailColor: '#eee',
    trailWidth: 1,
    svgStyle: null
  });

  bar.animate(1.0);  // Number from 0.0 to 1.0
/*  $scope.toggle = function() {
    $scope.review = !$scope.review;
  }*/

/*  $scope.patientCompletionIncrement = function() {
    $scope.patientCompletionCount++;
    // Set completion in object to true
    $scope.dataList[$scope.activeDoctorIndex].patients[$scope.activePatientIndex] = true;
  }*/

});