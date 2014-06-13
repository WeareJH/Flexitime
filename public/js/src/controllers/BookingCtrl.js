(function(angular) { 'use strict';

    var app = angular.module("JhHub");

    app.controller("BookingCtrl", ["$scope", "BookingService", "timeSettings", function ($scope, BookingService, timeSettings) {

        $scope.showEditRow = false;

        $scope.saving = false;

        $scope.deleting = false;

        $scope.bookingService = BookingService;

        $scope.toggleEditRow = function () {
            if ($scope.$parent.currentEditRow == $scope.booking.date) {
                $scope.setCurrentEditRow(false);
            } else {
                $scope.setCurrentEditRow($scope.booking.date);
            }
        };

        $scope.save = function() {
            $scope.saving = true;
            $scope.bookingService.saveBooking($scope.booking)
                .then(function() {
                    $scope.saving = false;
                    $scope.bookingForm.$setPristine();
                    $scope.toggleEditRow();
                });
        };

        $scope.delete = function () {
            $scope.deleting = true;
            $scope.bookingService.deleteBooking($scope.booking)
                .then(function() {
                    $scope.deleting = false;
                });
        };
    }]);

})(angular);