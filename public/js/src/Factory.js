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