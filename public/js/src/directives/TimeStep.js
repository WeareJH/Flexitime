(function(angular) { 'use strict';

    var app = angular.module("JhHub");

    app.directive('timeStep', function() {
        return {
            require: 'ngModel',
            link: function(scope, elm, attrs, ctrl) {
                ctrl.$parsers.unshift(function(viewValue) {

                    if(viewValue === undefined) {
                        ctrl.$setValidity('timeStep', false);
                        return undefined;
                    }

                    var parts   = viewValue.split(":");
                    var minutes = parts[1];

                    //Check the time is a valid minutes step
                    if (minutes % 15 === 0) {
                        // it is valid
                        ctrl.$setValidity('timeStep', true);
                        return viewValue;
                    } else {
                        // it is invalid, return undefined (no model update)
                        ctrl.$setValidity('timeStep', false);
                        return undefined;
                    }
                });
            }
        };
    });

})(angular);