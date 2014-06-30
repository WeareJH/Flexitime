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