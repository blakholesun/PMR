angular.
module('myApp')
    .config(function($stateProvider, $urlRouterProvider) {
        //
        // For any unmatched url, redirect to /state1z
        $urlRouterProvider.otherwise("/");
        //
        // Now set up the states
        $stateProvider
            .state('pmr', {
                url: "/",
                templateUrl: "js/main/global/nav.template.html",
                controller: 'navController'
            })
            .state('pmr.search',{
                url:"/search",
                templateUrl: "js/main/landing/landing.template.html",
                controller: 'formController'
            })
            .state('pmr.mainpage', {
                url: "/mainpage",
                templateUrl: "js/main/mainpage/page.template.html",
                controller: 'pageController'
            })
    });