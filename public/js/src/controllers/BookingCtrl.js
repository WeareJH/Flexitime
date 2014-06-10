(function(angular) {

    var app = angular.module("JhHub", ['ui.bootstrap', 'ngResource']);

    app.controller("BookingCtrl", ["$scope", "Booking", "$filter", "timeStep", "timeSettings", function ($scope, Booking, $filter, timeStep, timeSettings) {

        $scope.showEditRow = false;

        $scope.saving = false;

        $scope.deleting = false;

        $scope.toggleEditRow = function () {
            if ($scope.$parent.currentEditRow == $scope.booking.date) {
                $scope.setCurrentEditRow(false);
            } else {
                $scope.setCurrentEditRow($scope.booking.date);
            }
        };

        $scope.timeSettings = {
            timeStep: timeStep,
            startTime: timeSettings.startTime
        };

        $scope.newBooking = function () {
            //filter date
            var date = $filter('isoDate')($scope.day.date.date);
            date     = $filter('date')(date, 'yyyy-MM-dd');

            $scope.booking = new Booking({
                date: date,
                startTime: '07:00',
                endTime: '16:00'
            });
        };

        if ($scope.day.booking) {
            $scope.booking = new Booking({
                id: $scope.day.booking.id,
                date: $scope.day.booking.date,
                startTime: $scope.day.booking.startTime,
                endTime: $scope.day.booking.endTime,
                total: $scope.day.booking.total,
                notes: $scope.day.booking.notes
            });
        } else {
            $scope.newBooking();
        }

        $scope.save = function () {
            $scope.saving = true;

            if ($scope.booking.id) {
                $scope.booking.$update().then(function (result) {
                    $scope.saving = false;
                    $scope.booking = new Booking(result.booking);
                    $scope.bookingForm.$setPristine();
                    $scope.toggleEditRow();
                });
            } else {
                $scope.booking.$save().then(function (result) {
                    $scope.saving = false;
                    $scope.booking = new Booking(result.booking);
                    $scope.toggleEditRow();
                });
            }

        };

        $scope.delete = function () {
            $scope.deleting = true;
            $scope.booking.$delete().then(function (result) {
                $scope.newBooking();
                $scope.deleting = false;
                $scope.toggleEditRow();
            });
        };
    }]);

})(angular);