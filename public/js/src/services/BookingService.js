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