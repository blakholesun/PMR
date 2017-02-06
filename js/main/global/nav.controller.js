(function () {
    'use strict';

    angular
        .module('myApp')
        .controller('navController', navController);

    navController.$inject = ['initPage'];

    /* @ngInject */
    function navController(initPage) {
        var vm = this;
        vm.title = 'navController';
        vm.patientData = [];

        activate();

        ////////////////

        function activate() {

        }
    }

})();

