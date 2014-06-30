(function(angular) { 'use strict';

    var app = angular.module("JhHub");

    app.directive('timeRange', function() {
        return {
            require: 'ngModel',
            scope: {
                rangeMin: '@',
                rangeMax: '@'
            },
            link: function(scope, elm, attrs, ctrl) {
                ctrl.$parsers.unshift(function(viewValue) {

                    if(viewValue === undefined) {
                        ctrl.$setValidity('timeRange', false);
                        return undefined;
                    }

                    var valid = true;
                    //if min validation is enabled
                    if(scope.rangeMin) {
                        if(viewValue < scope.rangeMin) {
                            valid = false;
                        }
                    }

                    //if max validation is enabled
                    if(scope.rangeMax) {
                        if(viewValue > scope.rangeMax) {
                            valid = false;
                        }
                    }

                    ctrl.$setValidity('timeRange', valid);
                    return viewValue;

                });
            }
        };
    });

})(angular);