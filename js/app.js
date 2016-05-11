var app = angular.module('myApp', []);
app.controller('pets', function($scope, $http) {
  $http.get("http://localhost/PMR/php/getPatientData.php").then(function (response) {
    console.log(response)
    $scope.names = response.data.things;
  });
});