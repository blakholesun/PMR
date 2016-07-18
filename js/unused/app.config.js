angular.
	module('myApp').
	config(['$locationProvider','$routeProvider',
		function config($locationProvider, $routeProvider){
  			$locationProvider.hashPrefix('!');

  			$routeProvider.
  				when('/', {
  					template:'<pagecontroller></pagecontroller>'
  				}).
  				otherwise('/');
		}
]);