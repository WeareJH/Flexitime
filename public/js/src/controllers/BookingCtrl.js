(function(angular) { 'use strict';

    var app = angular.module("JhHub");

    app.controller("BookingCtrl", ["$scope", "BookingService", "timeSettings", function ($scope, BookingService, TotalsService, timeSettings, $animate) {

        $scope.showEditRow = false;

        $scope.saving = false;

        $scope.updated = false;

        $scope.deleting = false;

        $scope.successVisisble = false;

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
                .then(function(data) {
                    $scope.booking          = data.booking;
                    TotalsService.totals    = data.totals;
                    $scope.saving           = false;
                    $scope.bookingForm.$setPristine();
                    $scope.toggleEditRow();
                    $scope.updated = 'updated';
                });
        };

        $scope.delete = function () {
            $scope.deleting = true;
            $scope.bookingService.deleteBooking($scope.booking)
                .then(function(data) {
                    $scope.booking          = data.booking;
                    TotalsService.totals    = data.totals;
                    $scope.deleting         = false;
                    $scope.toggleEditRow();
                    $scope.updated = 'removed';
                });
        };
    }]);
})(angular);