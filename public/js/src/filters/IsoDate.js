(function(angular) { 'use strict';

    var app = angular.module("JhHub");

    app.filter('isoDate', function() {
        return function(input) {

            input = new Date(input).toISOString();
            return input;
        };
    });

})(angular);