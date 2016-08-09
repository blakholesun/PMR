// Page controller
angular.module('myApp')
.controller('pageController',['$http','$scope','$timeout','$filter','initPage','updateService',
function ($http,$scope,$timeout,$filter,initPage,updateService){

  //Initialize the data in the page. Data List is the list of patients classified by doc
  initPage.init().then(function(initData){

    $scope.dataList = initData.dataList;
    $scope.numPatients = initData.numPatients;
    // set first doctor as default
    $scope.activeDoctorIndex = 0; 
    // Set first patient for first doctor
    $scope.activePatientIndex = 0; 
    $scope.patientID = initData.dataList[0].patients[0].ID;
    $scope.patientDiagnosis = initData.dataList[0].patients[0].diagnosis;
    $scope.doctorLast ="";
    $scope.doctorFirst ="";
    $scope.activeTab = 0;
    
    // First patient info
    updateService.getPhoto($scope.patientID).then(function(data){
      $scope.patientPhoto = data;
    });

    // First Patient documenst
    updateService.getDocs($scope.patientID).then(function(data){
      $scope.documents = data.documents;
      $scope.requiredDocuments = data.requiredDocs;
    });

    //Get patient treatment info
    updateService.getTreatInfo($scope.patientID).then(function(data){
      $scope.TreatmentInfo = data;
    });

    //Get new start and SGAS dates

    updateService.getNewStart($scope.patientID).then(function(data){
      $scope.NewStart = data[0];
      $scope.SGAS = data[1];
      $timeout(function() {
          updateService.makeChart('#progress', $scope.patientID, $scope.SGAS, $scope.requiredDocuments[2].ApprovalDate);
      }, 200);
    });
    

    console.log($scope.dataList);

    //Stope the loading gif
    $(".se-pre-con").fadeOut(1000);
    $('#question').popover({ html : true});
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
          $scope.patientDiagnosis = $scope.dataList[i].patients[j].diagnosis;
        }
      }
    }
  }

  $scope.updateDoctor = function(index){
    if (index === -1){
      $scope.doctorLast = "";
      $scope.doctorFirst = "";
    } else{
      $scope.doctorLast = $scope.dataList[index].lastName;
      $scope.doctorFirst = $scope.dataList[index].firstName;
    }
    $scope.activeDoctorIndex = index;
    console.log(index);
  }

  $scope.getAge = function(){
    var today = new Date();
    var dob = $scope.dataList[$scope.activeDoctorIndex].patients[$scope.activePatientIndex].DOB;
    dob = new Date(dob.substr(0,12));
    //console.log(dob);
    return Math.floor((today-dob)/(365*24*3600*1000));

  }

  //Grabs new patient info on next or previous click or 
  $scope.grabPatientInfo = function() {
    if ($scope.doctorLast !==""){
      $scope.doctorLast = $scope.dataList[$scope.activeDoctorIndex].lastName;
      $scope.doctorFirst = $scope.dataList[$scope.activeDoctorIndex].firstName;
    }

    updateService.getPhoto($scope.patientID).then(function(data){
      $scope.patientPhoto = data;
    });

    updateService.getDocs($scope.patientID).then(function(data){
      $scope.documents = data.documents;
      $scope.requiredDocuments = data.requiredDocs;
    });
    //console.log($scope.dataList);
    updateService.getTreatInfo($scope.patientID).then(function(data){
      $scope.TreatmentInfo = data;
    });
    //console.log(patientTreatInfo);
    updateService.getNewStart($scope.patientID).then(function(data){
      $scope.NewStart = data[0];
      $scope.SGAS = data[1];
      /*$("#overview-tab").click();*/
      $scope.activeTab = 0;
        $timeout(function() {
          updateService.makeChart('#progress', $scope.patientID, $scope.SGAS, $scope.requiredDocuments[2].ApprovalDate);
        }, 100);
    });

    
    $timeout(function() {
      var element = document.getElementById("docpane");
      var original = document.getElementById("Doc");
      element.removeChild(original);
      element.innerHTML = '<div id="Doc"><h2>Please select a document from the list on the left.</h2></div>'
    }, 2000); 
  }

  // Moves to next patient and updates fields
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
    $scope.patientDiagnosis = $scope.dataList[$scope.activeDoctorIndex].patients[$scope.activePatientIndex].diagnosis;
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
    $scope.patientDiagnosis = $scope.dataList[$scope.activeDoctorIndex].patients[$scope.activePatientIndex].diagnosis;
  }

  // Grabbing the patient document that is clicked on
  $scope.displayDocument = function(doc){
    console.log(doc);

    var options = {
            pdfOpenParams: {
              zoom: "page-width"
              //pagemode: "thumbs",
              //search: "warning: hidden"
            }
        }

    var lowerCaseDoc = doc.DocType.toLowerCase();
    if (lowerCaseDoc.indexOf("distribution") != -1 || lowerCaseDoc.indexOf("imrt") != -1){
      options.page = 4;
    }
    $http.post( "api/getDocument/" + $scope.patientID, {FileName: doc.FileName}).then( function (response) {
          PDFObject.embed(response.data, "#Doc", options);
        });
    $scope.activeTab = 1;
    /*$("#doc-tab").click();*/
  }

  //Filerting out the files that have distribution and imrt in name for documents list
  $scope.keepOnTop = function (x) {
    var lowerCaseDoc = x.DocType.toLowerCase();
    if (lowerCaseDoc.indexOf("distribution") != -1 || lowerCaseDoc.indexOf("imrt") != -1) {
      return -1;
    }else {
      return 1;
    }
  }

}]);