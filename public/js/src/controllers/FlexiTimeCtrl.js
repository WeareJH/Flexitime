(function() { 'use strict';

    var app = angular.module("JhHub");

    app.controller("FlexiTimeCtrl", function ($scope, $http, BookingService) {

        $scope.weeks = [];

        BookingService.getBookings().then(function(weeks) {
            $scope.weeks = weeks;
        });

        $scope.currentEditRow = false;

        $scope.setCurrentEditRow = function(editRowDate) {
            $scope.currentEditRow = editRowDate;
        };


        /*$scope.currentDate = new Date();


        $scope.updatePeriod = function(month, year, user) {

            $http({
                url: '/flexi-time-rest',
                method: 'GET',
                params: {
                    y: year,
                    m: month
                }
            }).success(function(data) {
                $scope.records      = data.bookings;
                $scope.totals       = data.bookings.totals;
                $scope.pagination   = data.pagination;
                var user = {
                    fName   : data.bookings.user.name.split(' ')[0],
                    email   : data.bookings.user.email,
                    name    : data.bookings.user.name,
                    id      : data.bookings.user.id
                };
                $scope.user     = user;
                $scope.date     = new Date(data.date.date.split(" ")[0]);
                $scope.today    = new Date(data.today.date.split(" ")[0]);
            });

        };

        $scope.loadUserRecords = function() {
            $http.get('/flexi-time-rest').success(function(data) {
                $scope.records      = data.bookings;
                $scope.totals       = data.bookings.totals;
                $scope.pagination   = data.pagination;
                var user = {
                    fName   : data.bookings.user.name.split(' ')[0],
                    email   : data.bookings.user.email,
                    name    : data.bookings.user.name,
                    id      : data.bookings.user.id
                };
                $scope.user     = user;
                $scope.date     = new Date(data.date.date.split(" ")[0]);
                $scope.today    = new Date(data.today.date.split(" ")[0]);
            });
        };
        $scope.loadUserRecords();*/

    });

})();