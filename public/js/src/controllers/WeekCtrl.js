(function(angular) { 'use strict';

    var app = angular.module("JhHub");

    app.controller("WeekCtrl", ["$scope", function ($scope) {

        $scope.bookedHours = 0;

        $scope.totalHours = $scope.week.length * 7.5;

        $scope.bookedHours = function() {
            return $scope.week.reduce(function(current, booking) {

                if (booking.id) {
                    return current + parseFloat(booking.total);
                }
                return current;
            }, 0);
        };

        $scope.balance = function() {
            return $scope.bookedHours() - $scope.totalHours;
        };

    }]);

})(angular);