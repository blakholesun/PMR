// Page controller
angular.module('myApp')
.component('pagecontroller', {
  templateUrl: 'js/main/page.template.html',
  controller: pageController
});

function pageController($http,$scope,$timeout,$filter,initPage,updateService){
  function hideDivs() {
    $scope.docLoad = true;
    $scope.genLoad = true;
    $scope.diagLoad = true;
    $scope.planLoad = true;
    $scope.picLoad = true;
    $scope.chartLoad = true;
    console.log('hidingdivs');
  }
  //Initialize the data in the page. Data List is the list of patients classified by doc
  

  hideDivs();


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
    
    // First patient info
    updateService.getPhoto($scope.patientID).then(function(data){
      $scope.patientPhoto = data;
      $scope.picLoad = false;
    });

    // First Patient documenst
    updateService.getDocs($scope.patientID).then(function(data){
      $scope.documents = data.documents;
      $scope.requiredDocuments = data.requiredDocs;
      $scope.docLoad = false;
    });

    //Get patient treatment info
    updateService.getTreatInfo($scope.patientID).then(function(data){
      $scope.TreatmentInfo = data;
      $scope.planLoad = false;
    });

    //Get Histology info
    updateService.getHistologyInfo($scope.patientID).then(function(data){
      $scope.HistologyInfo = data;
      $scope.HistologyInfo.trans_log_mtstamp = new Date($scope.HistologyInfo.trans_log_mtstamp);
      $scope.diagLoad = false;
      console.log(data);
    })

    //Get new start and SGAS dates

    updateService.getNewStart($scope.patientID).then(function(data){
      $scope.NewStart = data[0];
      $scope.SGAS = data[1];
      $scope.genLoad = false;
      $timeout(function() {
          updateService.makeChart('#progress', $scope.patientID, $scope.SGAS, $scope.requiredDocuments[2].ApprovalDate);
          $scope.chartLoad = false;
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
      $timeout(function() {
        $scope.picLoad = false;
      },200)
    });

    updateService.getDocs($scope.patientID).then(function(data){
      $timeout(function() {
        $scope.docLoad = false;
      },200)
      $scope.documents = data.documents;
      $scope.requiredDocuments = data.requiredDocs;
    });
    //console.log($scope.dataList);
    updateService.getTreatInfo($scope.patientID).then(function(data){
      $timeout(function() {
        $scope.planLoad = false;
      },400)
      $scope.TreatmentInfo = data;
    });

    updateService.getHistologyInfo($scope.patientID).then(function(data){
      $timeout(function() {
        $scope.diagLoad = false;
      },400)
      $scope.HistologyInfo = data;
      $scope.HistologyInfo.trans_log_mtstamp = new Date($scope.HistologyInfo.trans_log_mtstamp);
      console.log($scope.HistologyInfo);
    });

    //console.log(patientTreatInfo);
    updateService.getNewStart($scope.patientID).then(function(data){
      $timeout(function() {
        $scope.genLoad = false;
      },200)
      $scope.NewStart = data[0];
      $scope.SGAS = data[1];
      $("#overview-tab").click();
        $timeout(function() {
          updateService.makeChart('#progress', $scope.patientID, $scope.SGAS, $scope.requiredDocuments[2].ApprovalDate);
          $scope.chartLoad = false;
        }, 500);
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
    hideDivs();

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
    hideDivs();

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
    $("#doc-tab").click();
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

  $scope.isBreast = function(){
    console.log($scope.patientDiagnosis[0].desc);
    return $scope.patientDiagnosis[0].desc.toLowerCase().indexOf('breast') !== -1;
  }

}