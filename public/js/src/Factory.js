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