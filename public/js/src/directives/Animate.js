(function(angular) { 'use strict';
    var app = angular.module("JhHub");

    app.directive('animate', function($animate) {
        return function(scope, elem, attr) {
            scope.$watch(attr.animate, function(nv, ov) {

                if (nv) {

                    var c = '';
                    switch (nv) {
                        case 'updated': c = 'success';  break;
                        case 'removed': c = 'danger';   break;
                    }

                    $animate.addClass(elem, c, function() {
                        $animate.removeClass(elem, c, function() {
                            scope.$apply(function() {
                                scope.updated = false;
                            });
                        });
                    });
                }
            })
        }
    });
})(angular);