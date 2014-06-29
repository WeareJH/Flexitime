var app = angular.module("JhHubAdmin", ['ui.bootstrap']);
var today = new Date();

app.controller("AdminTimeCtrl", function ($scope, $http) {

    $scope.currentDate = new Date();

    $http.get('/admin/flexi-time/users').success(function(data) {
        var users = [];
        for(var i in data.users) {
            var user = data.users[i];
            if(i == 0) {
                $scope.loadUserRecords(user.id);
            }

            users.push({
                id      : user.id,
                email   : user.email,
                name    : user.name,
                img     : data.images[user.email]
            })
        }
        $scope.users = users;
    });

    $scope.updatePeriod = function(month, year, user) {

        $http({
            url: '/admin/flexi-time/list/' + user.id,
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
            }
            $scope.user     = user;
            $scope.date     = new Date(data.date.date.split(" ")[0]);
            $scope.today    = new Date(data.today.date.split(" ")[0]);
        });

    };

    $scope.loadUserRecords = function(userId) {
        $http.get('/admin/flexi-time/list/' + userId).success(function(data) {
            $scope.records      = data.bookings;
            $scope.totals       = data.bookings.totals;
            $scope.pagination   = data.pagination;
            var user = {
                fName   : data.bookings.user.name.split(' ')[0],
                email   : data.bookings.user.email,
                name    : data.bookings.user.name,
                id      : data.bookings.user.id
            }
            $scope.user     = user;
            $scope.date     = new Date(data.date.date.split(" ")[0]);
            $scope.today    = new Date(data.today.date.split(" ")[0]);
        });
    }
});

app.filter('isoDate', function() {
    return function(input) {
        input = input.split(" ")[0];
        input = new Date(input).toISOString();
        return input;
    };
});

app.filter('signTotal', function() {
    return function(input) {
        input = "" + input;
        if(input >= 0) {
            return  "+ " + input;
        }

        return input.replace("-", "- ");
    };
});

app.filter('bookingClasses', function() {
    return function(day) {
        var date = new Date(day.date.date);
        var classes = [];

        if(
            date.getDate() ===  today.getDate() &&
                date.getMonth() ===  today.getMonth() &&
                date.getYear() ===  today.getYear()
            ) {
            classes.push('today');
        }

        if(!day.booking) {
            classes.push('no-booking');

            if(date > today.currentDate) {
                classes.push('no-booking-future');
            } else if(date < today.currentDate) {
                classes.push('no-booking-past');
            }
        }

        return classes.join(" ");
    }
});
