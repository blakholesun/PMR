angular.module('myApp')
.controller('formController',['$rootScope','$scope','$http','$state',
	function ($rootScope,$scope,$http,$state){
	$scope.startDate = new Date();
	$scope.endDate = new Date();
	$scope.endDate.setDate($scope.endDate.getDate() + 6);
	$rootScope.dates = {};
	/*console.log($rootScope.startDate.toDateString());
	console.log($rootScope.endDate.toDateString());*/

	$scope.setToCurrentWeek = function() {
		$rootScope.dates = {};
		$rootScope.dates.startDate = new Date();
		$rootScope.dates.endDate = new Date();
		$rootScope.dates.endDate.setDate($rootScope.dates.endDate.getDate() + 6);
		$rootScope.dates.patientStatus = 'Open';
		console.log($rootScope.dates);
	}

	$scope.processForm = function(){
		
		$rootScope.dates.startDate = $scope.startDate;
		$rootScope.dates.endDate = $scope.endDate;
		$rootScope.dates.patientStatus = 'Completed';
		console.log($rootScope.dates);
		$state.go('mainpage');
	}


}]);