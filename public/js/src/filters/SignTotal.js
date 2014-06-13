(function() { 'use strict';

    var app = angular.module("JhHub");

    app.filter('signTotal', function() {
        return function(input) {
            input = "" + input;
            if(input >= 0) {
                return  "+ " + input;
            }

            return input.replace("-", "- ");
        };
    });

})();