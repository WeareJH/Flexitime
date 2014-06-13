(function() { 'use strict';

    var app = angular.module("JhHub");

    app.directive('timeFormat', function() {
        return {
            require: 'ngModel',
            link: function(scope, elm, attrs, ctrl) {
                ctrl.$parsers.unshift(function(viewValue) {

                    if (viewValue.match(/^\d{1,2}:\d\d$/)) {
                        // it is valid
                        ctrl.$setValidity('timeFormat', true);
                        return viewValue;
                    } else {
                        // it is invalid, return undefined (no model update)
                        ctrl.$setValidity('timeFormat', false);
                        return undefined;
                    }
                });
            }
        };
    });

})();