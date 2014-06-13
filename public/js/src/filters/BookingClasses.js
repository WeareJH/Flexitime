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