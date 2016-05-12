var app = angular.module('myApp', []);

app.controller('pets', function($scope, $http) {
  $http.get("http://localhost/PMR/php/getPatientData.php").then(function (response) {
    console.log(response)
    $scope.names = response.data.things;
  });
});

app.controller('infoCtrl', function($scope){
	$scope.myTable = true;
	$scope.myCarousel = false;
    $scope.toggle = function() {
    	$scope.myTable = !$scope.myTable;
        $scope.myCarousel = !$scope.myCarousel;
    }
});

