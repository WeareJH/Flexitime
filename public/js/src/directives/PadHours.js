(function(angular) { 'use strict';
    var app = angular.module("JhHub");

    app.directive('padHours', function() {
        return {
            require: 'ngModel',
            link: function(scope, elm, attrs, ctrl) {
                ctrl.$parsers.unshift(function(viewValue) {

                    //convert single digits to double
                    //eg 9:00 - 09:00
                    var parts   = viewValue.split(":");
                    var hours   = parts[0];
                    var minutes = parts[1];

                    if(hours.length == 1) {
                        hours = "0" + hours;
                    }

                    return hours + ":" + minutes;
                });
            }
        };
    });

})(angular);