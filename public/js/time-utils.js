(function () {

    window.timeUtils = function(time, range, step) {
        var errors = [];

        if(!time.match(/^\d{1,2}:\d\d$/)){
            errors.push("Time should be in the format hh:mm or h:mm");
            return errors;
        }

        //convert single digits to double
        //eg 9:00 - 09:00
        var parts = time.split(":");

        var hours = parts[0];
        var minutes = parts[1];

        if(hours.length == 1) {
            hours = "0" + hours;
        }

        if(minutes % step !== 0) {
            errors.push("Time should be booked in " + step + " minute increments");
        }

        time = hours + ":" + minutes;

        //if min validation is enabled
        if(range.min) {
            if(time < range.min) {
                errors.push("Time should not be less than " + range.min);
            }
        }

        //if max validation is enabled
        if(range.max) {
            if(time > range.max) {
                errors.push("Time should not be greater than " + range.max);
            }
        }

        if(time === "24:00") {
            errors.push("Invalid Time " + time);
        }

        if(errors.length) {
            return errors;
        }

        return time;
    }

})();