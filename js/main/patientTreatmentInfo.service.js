angular.module('myApp').factory('patientTreatInfo', ['$http','$q', function ($http,$q) {

	return {
		getInfo: function(patientId){

			var defer = $q.defer();

			$http.post("php/queryTreatment.php", {patientID: patientId})
			.then( function (response) {
				patTreatInfo = [];
				var TreatmentInfo = response.data;
				console.log(TreatmentInfo);
				var i = 0;
				var cutoffDate = new Date();

				//Look at past 60 days
				var planCutoffDate = 60;

				cutoffDate.setDate(cutoffDate.getDate()-planCutoffDate);

				for (item1 in TreatmentInfo){
					for (item in TreatmentInfo[item1]){
						planCreationDate = new Date(TreatmentInfo[item1][item].date);
						if (TreatmentInfo[item1][item].hasOwnProperty('dose')
							//&& TreatmentInfo[item1][item].cstatus === 'ACTIVE'
							//&& planCreationDate > cutoffDate
							&& TreatmentInfo[item1][item].intent !== 'VERIFICATION'
							&& TreatmentInfo[item1][item].status === 'TreatApproval'){

							patTreatInfo.push(new Object());
							patTreatInfo[i].Dose =  TreatmentInfo[item1][item].dose;
							patTreatInfo[i].noFractions = TreatmentInfo[item1][item]
							.nofractions;
							patTreatInfo[i].Plan = TreatmentInfo[item1][item].name;
							patTreatInfo[i].PlanDate = TreatmentInfo[item1][item].date;
							i++;
						}
					}
				}
				defer.resolve(patTreatInfo);
			});
			return defer.promise;
		}
	}
}]);