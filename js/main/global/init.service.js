(function () {
    'use strict';

    angular
        .module('myApp')
        .factory('Patient', Patient);

    Patient.$inject = ['$http','$q'];

    /* @ngInject */
    function Patient($http,$q) {

        var patientData = {};

        var service = {
            initializePatients: initializePatients,
            initializeSinglePatient: initializeSinglePatient,
            getPatients: getPatients,
            setPatients: setPatients
        };

        return service;

        ////////////////

        function getPatients() {
            return patientData;
        }

        function setPatients(patients) {
            patientData = patients;
        }

        function initializeSinglePatient(patientID) {
            $http.post("api/getPatient/", patientID).then(function () {
                
            });
        }

        function initializePatients(startDate, endDate, patientStatus) {
            var defer = $q.defer();

            var searchOptions = {
                startDate: startDate.toDateString(),
                endDate: endDate.toDateString(),
                status: patientStatus
            }
            console.log(searchOptions);
            $http.post("api/getAllPatients/", searchOptions).then(function (response) {

                var allPatientData = {};

                var data=response.data.list;
                //console.log(data);
                allPatientData.numPatients = data.length;

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
                                        data[j].PMR,
                                        data[j].DateOfBirth,
                                        data[j].Sex);

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

                setPatients(allPatientData);

                allPatientData.dataList.length > 0 ? defer.resolve(allPatientData) : defer.reject("No patients");
            });
            return defer.promise;
        }

        //Create a doctor data structure to store names and list of patients
        function Doctor(firstName, lastName){
            this.firstName = firstName;
            this.lastName = lastName;
            this.patients = [];
            this.numPatients = function()
            {
                return this.patients.length;
            }
        }

        //Create a patient data structure
        function Patient(firstName, lastName, ID, PMR, DOB, Sex){
            this.firstName      = firstName;
            this.lastName       = lastName;
            this.ID             = ID;
            this.diagnosis      = [];
            this.PMR			  = PMR;
            this.DOB			  = DOB;
            this.Sex			  = Sex;
        }

        // Diagnosis data structure
        function Diagnosis(desc, tumorSize, summaryStage, stageCriteria, dateStamp){
            this.desc           = desc;
            this.tumorSize      = tumorSize;
            this.summaryStage   = summaryStage;
            this.stageCriteria  = stageCriteria;
            this.dateStamp      = dateStamp;
        }
    }

})();

