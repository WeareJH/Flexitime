var app = angular.module("JhHubAdmin", ['ui.bootstrap', 'ui.gravatar']);
var today = new Date();

app.controller("AdminTimeCtrl", function ($scope, $http) {

    $scope.today = new Date();

    $http.get('/user-rest').success(function(data) {
        var users = [];
        for(var i in data.users) {
            var user = data.users[i];
            if(i == 0) {
                $scope.updatePeriod(user, null, null);
            }

            users.push({
                id      : user.id,
                email   : user.email,
                name    : user.name
            })
        }
        $scope.users = users;
    });

    $scope.updatePeriod = function(user, month, year) {

        $http({
            url: '/flexi-time-rest',
            method: 'GET',
            params: {
                y:      year,
                m:      month,
                user:   user.id
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
            $scope.user = user;
            $scope.date = new Date(data.date.date.split(" ")[0]);
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
