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