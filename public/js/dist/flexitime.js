(function(angular) {
    'use strict';

    angular.module("JhHub", [
        'ui.bootstrap',
        'ngResource',
        'ngAnimate'
    ]);
})(angular);
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
(function(angular) { 'use strict';

    var app = angular.module("JhHub");

    app.filter('bookingClasses', function(today) {
        return function(booking) {

            var date = booking.getDate();

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

})(angular);
(function(angular) { 'use strict';

    var app = angular.module("JhHub");

    app.filter('isoDate', function() {
        return function(input) {

            input = new Date(input).toISOString();
            return input;
        };
    });

})(angular);
(function(angular) { 'use strict';

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

})(angular);
(function(angular) { 'use strict';
    var app = angular.module("JhHub");

    app.directive('animate', function($animate) {
        return function(scope, elem, attr) {
            scope.$watch(attr.animate, function(nv, ov) {

                if (nv) {

                    var stateClasses = {
                        updated: 'success',
                        removed: 'danger'
                    }

                    var c = '';
                    if (stateClasses.hasOwnProperty(nv)) {
                        c = stateClasses[nv];
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
(function(angular) { 'use strict';
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

})(angular);
(function(angular) { 'use strict';

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

})(angular);
(function(angular) { 'use strict';

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

})(angular);
(function(angular) { 'use strict';

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

})(angular);
(function(angular) { 'use strict';

    var app = angular.module("JhHub");

    app.service('BookingService', function(BookingResource) {

        this.bookingResource = BookingResource;

        this.timeSettings = {
            defaultStartTime: "09:00",
            defaultEndTime: "17:30"
        };

        this.processBookingsInToWeeks = function(result) {

            var bookings = result.bookings;
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

            var data = {
                weeks       : weeks,
                date        : new Date(result.date * 1000),
                pagination  : result.pagination,
                totals      : result.totals
            };

            return data;
        };

        this.getBookings = function(data) {
            return this.bookingResource.get(data.params)
                .$promise
                .then(this.processBookingsInToWeeks);
        };

        this.saveBooking = function(booking) {
            if (booking.id) {
                //update
                return booking.$update()
                    .then(this.updateSuccess);
            } else {
                return booking.$save().then(this.createSuccess);
            }
        };

        this.updateSuccess = function(result) {
            return {
                booking : new BookingResource(result.booking),
                totals  : result.totals
            };
        };

        this.createSuccess = function(result) {
            return {
                booking : new BookingResource(result.booking),
                totals  : result.totals
            };
        };

        this.newBooking = function(date) {
            return new BookingResource({
                date        : date,
                startTime   : this.timeSettings.defaultStartTime,
                endTime     : this.timeSettings.defaultEndTime
            });
        };

        this.deleteBooking = function(booking) {
            var date = booking.date;
            var newBooking = this.newBooking(date);

            return booking.$delete()
                .then(function(result) {
                    return {
                        booking : newBooking,
                        totals  : result.totals
                    };
                });
        };
    });

})(angular);
(function(angular) { 'use strict';

    var app = angular.module("JhHub");

    app.service('TotalsService', function() {

        this.totals = {
            balanceForward          : 0,
            monthBalance            : 0,
            monthRemainingHours     : 0,
            monthTotalHours         : 0,
            monthTotalWorkedHours   : 0,
            runningBalance          : 0
        };

        this.setTotals = function(totals) {
            this.totals = totals;
        }
    });

})(angular);
(function(angular) { 'use strict';

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

            var bookingResource = $resource('/flexi-time-rest/:id', { 'id': '@id'}, {
                'update' : {'method' : 'PUT'}
            });

            bookingResource.prototype.getIsoDate = function() {
                var dateStr     = this.date;
                var dateParts   = dateStr.split("-");
                var date        = new Date(
                    dateParts[2],
                    dateParts[1] - 1,
                    dateParts[0]
                );

                return date.toISOString();
            };

            bookingResource.prototype.getDate = function() {
                var dateStr     = this.date;
                var dateParts   = dateStr.split("-");
                var date        = new Date(
                    dateParts[2],
                    dateParts[1] - 1,
                    dateParts[0]
                );

                return date;
            };

            return bookingResource;
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

})(angular);