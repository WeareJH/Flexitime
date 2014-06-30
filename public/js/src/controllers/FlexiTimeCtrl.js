(function(angular) { 'use strict';

    var app = angular.module("JhHub");

    app.controller("FlexiTimeCtrl", function ($scope, BookingService, TotalsService) {

        $scope.loadingRecords = true;
        $scope.totals = TotalsService.totals;

        $scope.$watch( function() { return TotalsService.totals; }, function(data) {
            $scope.totals = TotalsService.totals;
        }, true);


        $scope.updatePeriod = function(month, year) {
            $scope.loadingRecords = true;
            BookingService.getBookings({
                params: {
                    y: year,
                    m: month
                }
            }).then(function (data) {
                $scope.weeks            = data.weeks;
                $scope.date             = data.date;
                $scope.pagination       = data.pagination;
                TotalsService.totals    = data.totals
                $scope.loadingRecords   = false;
            });
        };

        $scope.weeks = [];

        $scope.currentEditRow = false;

        $scope.setCurrentEditRow = function(editRowDate) {
            $scope.currentEditRow = editRowDate;
        };

        $scope.currentDate = new Date();

        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var currentMonth = months[$scope.currentDate.getMonth()];
        $scope.updatePeriod(currentMonth, $scope.currentDate.getFullYear());

    });

})(angular);