angular.module('myApp').factory('updateService', ['$http','$q','chartService', 'patientTreatInfo',
	function ($http,$q, chartService, patientTreatInfo) {

		//Function that checks whether the required documents are present in the doc list
		//and returns the list
		var checkDocs = function(documents){
			function Document(fileType){
		      	this.fileType = fileType;
		      	this.isAvailable = false;
		      	this.Approval = 'Not Approved';
		      	this.Signed = 'Not Signed';
		    }  

		    requiredDocuments = 
		    [
			    new Document("RO-Consult"), 
			    new Document("Pathology"),
			    new Document("Rad Onc Requisition"), 
			    new Document("RO-CT Planning Sheet / Fast-Track"),
			    new Document("Radiotherapy Prescription")
		    ];
		    
		    function SearchName(doc){
		    	this.doc = doc;
		    	this.found = false;
		    }

		    var namesearch = 
		    [
		    	new SearchName("ro - consult"), 
		    	new SearchName("pathology"),
		    	new SearchName("requisition"),
		    	new SearchName("planning sheet;fast-track"),
		    	new SearchName("prescription")
		    ];
		    // documents are sorted in order of date desc
		    for (var x in documents){        
		      	var name = documents[x].DocType.toLowerCase();
		      	var creation_date = new Date(documents[x].Date.substring(0,11));
		      	var apprdate = new Date(documents[x].ApprovalTime.substring(0,11));
		      	var cutoffDate = new Date();
		      	// Change this value for cutoff date
		      	cutoffDate.setDate(cutoffDate.getDate()-360)
		      	for (var i =0; i<namesearch.length; i++){
		      		//seraching through dual string
		      		var res = namesearch[i].doc.split(";");
		      		for (spl in res){
		      			if(name.indexOf(res[spl]) != -1 && creation_date > cutoffDate && !namesearch[i].found){
			          	requiredDocuments[i].isAvailable = true;
			          	requiredDocuments[i].Approval = documents[x].ApprovalStatus;
			          	requiredDocuments[i].Signed = documents[x].Signed;
			          	requiredDocuments[i].ApprovalDate = apprdate;
			          	namesearch[i].found = true;
		      			}
		      		}
		    	}
		    }
		    console.log(requiredDocuments);
		    return requiredDocuments;
		}

	return {

		getPhoto: function(patientId){
			var defer = $q.defer();
			$http.post( "api/getPatientPhoto/"+patientId)
    		.then( function (response) {
      			patientPhoto = response.data;
      			defer.resolve(patientPhoto);
    		});
			return defer.promise;
		},

		getDocs: function(patientId){
			var defer = $q.defer();
			$http.post( "api/getPatientDocumentList/" + patientId)
    		.then( function (response) {
    			docData = {};
      			docData.documents = response.data;
      			docData.requiredDocs = checkDocs(docData.documents);
      			defer.resolve(docData);
    		});
			return defer.promise;
		},

		makeChart: function(element,patientID, SGAS, reqdate){
			chartService(element, patientID, SGAS, reqdate);
		},

		getTreatInfo: function(patientID){
			return patientTreatInfo.getInfo(patientID);
		},

		getNewStart: function(patientId){
			var defer1 = $q.defer();
			var defer2 = $q.defer();
			$http.post( "api/getNewStart/" + patientId)
    		.then( function (response) {
      			newStart = new Date(response.data.startDate);
      			defer1.resolve(newStart);
    		});
    		$http.post( "api/getSGAS/" + patientId)
    		.then( function (response) {
    			var SGAS = {};
    			//console.log(response.data.DueDate)
      			SGAS.DueDate = new Date(response.data.DueDate);
      			SGAS.DueDate.setHours(20);
      			SGAS.Priority = response.data.Priority;
  				//SGAS.MedicallyReady = new Date();
  				SGAS.priorityNum = parseInt(SGAS.Priority.substr(SGAS.Priority.length -1 ));
  				//console.log(priority);
  				var daysToDue = 0;
  				switch (SGAS.priorityNum){
  					case 1:
  						daysToDue = 0;
  						break;
  					case 2:
  						daysToDue = 3;
  						break;
  					case 3:
  						daysToDue = 15;
  						break;
  					case 4:
  						daysToDue = 28;
  						break;
  				}
  				//console.log(daysToDue);
  				SGAS.MedicallyReady = new Date(SGAS.DueDate - daysToDue*24*3600*1000);
      			console.log(SGAS);
      			defer2.resolve(SGAS);
    		});
			return $q.all([defer1.promise,defer2.promise]);
		},

		getHistologyInfo: function(patientId){
			var defer = $q.defer();
			$http.post( "api/getHistologyInfo/" + patientId)
    		.then( function (response) {
    			var histologyInfo = {};
      			histologyInfo = response.data;
      			defer.resolve(histologyInfo);
    		});

    		return defer.promise;
		}

	}
}]);