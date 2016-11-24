(function () {
    'use strict';

    angular
        .module('myApp')
        .controller('navController', navController);

    navController.$inject = ['patientTreatInfo', 'initPage'];

    /* @ngInject */
    function navController(patientTreatInfo, initPage) {
        var vm = this;
        vm.title = 'navController';
        //vm.patientData = [];

        activate();

        ////////////////

        function activate() {

        }
    }

})();

