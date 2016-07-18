angular.module('myApp').factory('initPage', ['$http','$q', function ($http,$q) {

	return {

		init: function(){

			var defer = $q.defer();

			$http.post("php/getPatientData",{}).then(function (response) {
    			
    			var allPatientData = {};

    			data=response.data.list;
    			//console.log(data);
    			allPatientData.numPatients = data.length;
    			//Create a doctor class to store names and list of patients
			    function Doctor(firstName, lastName){
			      this.firstName = firstName;
			      this.lastName = lastName;
			      this.patients = [];
			    }

			    //Create a patient class to store names and id
			    function Patient(firstName, lastName, ID, PMR){
			      this.firstName      = firstName;
			      this.lastName       = lastName;
			      this.ID             = ID;
			      this.diagnosis      = [];
			      this.isReviewed     = false;
			      this.PMR			  = PMR;
			    }

			    //
			    function Diagnosis(desc, tumorSize, summaryStage, stageCriteria, dateStamp){
			      this.desc           = desc;
			      this.tumorSize      = tumorSize;
			      this.summaryStage   = summaryStage;
			      this.stageCriteria  = stageCriteria;
			      this.dateStamp      = dateStamp;
			    }

			    var dataList = []; // the list of doctor objects with patients
			    var docsInList = []; // create an array to hold doctor names
			    var index;
			    var indexPat;

			    // scan thorugh whole sql table
			    for (var i = 0; i < data.length; i++){
			      //load index of doctor lastname
			      index = docsInList.indexOf(data[i].DocLastName);
			      //check if doctor already in list exists
			      if (index === -1){
			        //if not push doc name to list of already found doctors
			        docsInList.push(data[i].DocLastName);
			        //push doctot to list to be stored in scope
			        dataList.push(new Doctor(data[i].DocFirstName,data[i].DocLastName));
			        // look though list for patients with same doctor
			        var patientsInList = [];
			        for (var j = 0; j<data.length; j++){
			          if (docsInList[docsInList.length-1] === data[j].DocLastName){
			            // push patients in doctor list
			            indexPat = patientsInList.indexOf(data[j].PatientID);
			            if ( indexPat === -1){
			              patientsInList.push(data[j].PatientID);
			              var diagnosisData = new Diagnosis(data[j].Diagnosis,
			                data[j].TumorSize,
			                data[j].SummaryStage,
			                data[j].StageCriteria,
			                data[j].DiagDate);
			              //console.log(data[j].DiagDate);
			              var patient = new Patient(  data[j].FirstName,
			                data[j].LastName,
			                data[j].PatientID,
			                data[j].PMR);

			              patient.diagnosis.push(diagnosisData);
			              dataList[dataList.length-1].patients.push(patient);
			            } else {
			              var diagnosisData = new Diagnosis(data[j].Diagnosis,
			                data[j].TumorSize,
			                data[j].SummaryStage,
			                data[j].StageCriteria,
			                data[j].DiagDate);
			              dataList[dataList.length-1]
			              .patients[dataList[dataList.length-1].patients.length-1]
			              .diagnosis.push(diagnosisData);
			            }
			          }
			        }
			      }
			    }
			    allPatientData.dataList = dataList;
			    allPatientData.patientID = allPatientData.dataList[0].patients[0].ID;
			    allPatientData.patientDiagnosis = allPatientData.dataList[0].patients[0].diagnosis;

			    defer.resolve(allPatientData);
			});
			return defer.promise;
		}

	}

}]);