(function() {
    'use strict';

    angular.module("JhHub", [
        'ui.bootstrap',
        'ngResource']
    );
})();
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
(function() { 'use strict';

    var app = angular.module("JhHub");

    app.controller("FlexiTimeCtrl", ['$scope', '$http', 'BookingService', function ($scope, $http, BookingService) {

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

    }]);

})();
(function() { 'use strict';

    var app = angular.module("JhHub");

    app.filter('bookingClasses', function(today) {
        return function(booking) {

            var date = new Date(booking.date * 1000);

            var classes = [];

            if(
                date.getDate() ===  today.getDate() &&
                date.getMonth() ===  today.getMonth() &&
                date.getYear() ===  today.getYear()
            ) {
                classes.push('today');
            }

            if(!booking.id) {
                classes.push('no-booking');

                if(date > today) {
                    classes.push('no-booking-future');
                } else if(date < today) {
                    classes.push('no-booking-past');
                }
            }
            return classes.join(" ");
        };
    });

})();
(function() { 'use strict';

    var app = angular.module("JhHub");

    app.filter('isoDate', function() {
        return function(input) {
            input = new Date(input).toISOString();
            return input;
        };
    });

})();
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
(function() { 'use strict';
    var app = angular.module("JhHub");

    app.directive('padHours', function() {
        return {
            require: 'ngModel',
            link: function(scope, elm, attrs, ctrl) {
                ctrl.$parsers.unshift(function(viewValue) {

                    //convert single digits to double
                    //eg 9:00 - 09:00
                    var parts   = viewValue.split(":");
                    var hours   = parts[0];
                    var minutes = parts[1];

                    if(hours.length == 1) {
                        hours = "0" + hours;
                    }

                    return hours + ":" + minutes;
                });
            }
        };
    });

})();
(function() { 'use strict';

    var app = angular.module("JhHub");

    app.directive('timeFormat', function() {
        return {
            require: 'ngModel',
            link: function(scope, elm, attrs, ctrl) {
                ctrl.$parsers.unshift(function(viewValue) {

                    if (viewValue.match(/^\d{1,2}:\d\d$/)) {
                        // it is valid
                        ctrl.$setValidity('timeFormat', true);
                        return viewValue;
                    } else {
                        // it is invalid, return undefined (no model update)
                        ctrl.$setValidity('timeFormat', false);
                        return undefined;
                    }
                });
            }
        };
    });

})();
(function() { 'use strict';

    var app = angular.module("JhHub");

    app.directive('timeRange', function() {
        return {
            require: 'ngModel',
            scope: {
                rangeMin: '@',
                rangeMax: '@'
            },
            link: function(scope, elm, attrs, ctrl) {
                ctrl.$parsers.unshift(function(viewValue) {

                    if(viewValue === undefined) {
                        ctrl.$setValidity('timeRange', false);
                        return undefined;
                    }

                    var valid = true;
                    //if min validation is enabled
                    if(scope.rangeMin) {
                        if(viewValue < scope.rangeMin) {
                            valid = false;
                        }
                    }

                    //if max validation is enabled
                    if(scope.rangeMax) {
                        if(viewValue > scope.rangeMax) {
                            valid = false;
                        }
                    }

                    ctrl.$setValidity('timeRange', valid);
                    return viewValue;

                });
            }
        };
    });

})();
(function() { 'use strict';

    var app = angular.module("JhHub");

    app.directive('timeStep', function() {
        return {
            require: 'ngModel',
            link: function(scope, elm, attrs, ctrl) {
                ctrl.$parsers.unshift(function(viewValue) {

                    if(viewValue === undefined) {
                        ctrl.$setValidity('timeStep', false);
                        return undefined;
                    }

                    var parts   = viewValue.split(":");
                    var minutes = parts[1];

                    //Check the time is a valid minutes step
                    if (minutes % 15 === 0) {
                        // it is valid
                        ctrl.$setValidity('timeStep', true);
                        return viewValue;
                    } else {
                        // it is invalid, return undefined (no model update)
                        ctrl.$setValidity('timeStep', false);
                        return undefined;
                    }
                });
            }
        };
    });

})();
(function(angular) { 'use strict';

    var app = angular.module("JhHub");

    app.service('BookingService', function(BookingResource) {

        this.bookingResource = BookingResource;

        this.processBookingsInToWeeks = function(result) {

            var bookings = result.bookings.records.dates;
            var weeks       = [];
            var weekCounter = 0;
            var lastDayNum  = 0;
            for(var timestamp in bookings) {
                var booking = new BookingResource(bookings[timestamp]);
                var date    = new Date(timestamp * 1000);
                var dayNum  = date.getDay();

                //remember the last highest day
                //in this week
                if (dayNum > lastDayNum) {
                    lastDayNum = dayNum;
                }

                //if the day is lower than the highest
                //last recorded high day number
                //must be a new week
                if (lastDayNum > dayNum) {
                    weekCounter++;
                    lastDayNum = 0;
                }

                if (typeof weeks[weekCounter] === 'undefined' ){
                    weeks[weekCounter] = [booking];
                } else {
                    weeks[weekCounter].push(booking);
                }
            }

            return weeks;
        };

        this.getBookings = function() {
            return this.bookingResource.get()
                .$promise
                .then(this.processBookingsInToWeeks);
        };

        this.saveBooking = function(booking) {
            if (booking.id) {
                //update
                return booking.update()
                    .then(this.updateSuccess);
            } else {
                return booking.save().then(this.createSuccess);
            }
        };

        this.updateSuccess = function(result) {
            return new BookingResource(result.booking);
        };

        this.createSuccess = function(result) {
            return new BookingResource(result.booking);
        };

        this.deleteBooking = function(booking) {
            return booking.$delete();
        };
    });

})(angular);
(function() { 'use strict';

    var app = angular.module("JhHub");

    var today = new Date();
    var timeStep = 15;

    var timeSettings = {
        startTime: {
            min: '07:00',
            max: '10:00'
        },
        endTime: {
            min: '16:00',
            max: '19:00'
        }
    };

    app.factory('BookingResource', ['$resource',
        function($resource){
            return $resource('/flexi-time-rest/:id', { 'id': '@id'}, {
                'update' : {'method' : 'PUT'}
            });
        }]
    );

    app.config(function($provide) {
        $provide.provider('today', function() {
            this.$get = function() {
                return today;
            };
        });
    });

    app.config(function($provide) {
        $provide.provider('timeStep', function() {
            this.$get = function() {
                return timeStep;
            };
        });
    });

    app.config(function($provide) {
        $provide.provider('timeSettings', function() {
            this.$get = function() {
                return timeSettings;
            };
        });
    });

})();