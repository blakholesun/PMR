angular.module('myApp').factory('updateService', ['$http','$q','chartService', 'patientTreatInfo',
	function ($http,$q, chartService, patientTreatInfo) {

		//Function that checks whether the required documents are present in the doc list
		//and returns the list
		var checkDocs = function(documents){
			function Document(fileType){
		      	this.fileType = fileType;
		      	this.creationDate = 0;
		      	this.isAvailable = false;
		    }  

		    requiredDocuments = [new Document("RO-Consult"), new Document("Pathology"),
		    new Document("Rad Onc Requisition"), 
		    new Document("RO-CT Planning Sheet"),
		    new Document("Radiotherapy Prescription")];
		    var namesearch = ["consult", "pathology", "requisition", "planning sheet", "prescription"];
		    var x;
		    for (x in documents){        
		      	var name = documents[x].DocType.toLowerCase();
		      	var date = new Date(documents[x].Date.substring(0,11));
		      	var cutoffDate = new Date();
		      	// Change this value for cutoff date
		      	cutoffDate.setDate(cutoffDate.getDate()-360)
		      	for (var i =0; i<namesearch.length; i++){
		        	if(name.indexOf(namesearch[i]) != -1 && date > cutoffDate){
		          	requiredDocuments[i].isAvailable = true;
		        	}  
		      	}

		    }
		    return requiredDocuments;
		}

	return {

		getPhoto: function(patientId){
			var defer = $q.defer();
			$http.post( "php/getPatientPhoto.php", {patientID: patientId})
    		.then( function (response) {
      			patientPhoto = response.data;
      			defer.resolve(patientPhoto);
    		});
			return defer.promise;
		},

		getDocs: function(patientId){
			var defer = $q.defer();
			$http.post( "php/getPatientDocuments.php", {patientID: patientId})
    		.then( function (response) {
    			docData = {};
      			docData.documents = response.data;
      			docData.requiredDocs = checkDocs(docData.documents);
      			defer.resolve(docData);
    		});
			return defer.promise;
		},

		makeChart: function(element,patientID){
			chartService(element, patientID);
		},

		getTreatInfo: function(patientID){
			return patientTreatInfo.getInfo(patientID);
		},

		getNewStart: function(patientId){
			var defer1 = $q.defer();
			var defer2 = $q.defer();
			$http.post( "php/getNewStart.php", {patientID: patientId})
    		.then( function (response) {
      			newStart = new Date(response.data.startDate);
      			defer1.resolve(newStart);
    		});
    		$http.post( "php/getSGAS.php", {patientID: patientId})
    		.then( function (response) {
      			SGAS = new Date(response.data.DueDate);
      			defer2.resolve(SGAS);
    		});
			return $q.all([defer1.promise,defer2.promise]);
		}

	}
}]);