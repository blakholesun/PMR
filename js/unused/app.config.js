angular.
	module('myApp')
	.config(function($stateProvider, $urlRouterProvider) {
  //
  // For any unmatched url, redirect to /state1
  $urlRouterProvider.otherwise("/landing");
  //
  // Now set up the states
  $stateProvider
    .state('landing', {
      url: "/landing",
      templateUrl: "js/main/landing.template.html",
      controller: 'formController'
    })
    .state('mainpage', {
      url: "/mainpage",
      templateUrl: "js/main/page.template.html",
      controller: 'pageController'
    })
  });