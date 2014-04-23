/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
$(function() {

    $(".fx-tooltip").tooltip();

    //default settings -
    //TODO: Get these from server, and be per-user
    var defaultSettings = {
        startTime: {
            min    : "07:00",
            max    : "10:00",
            "default": "09:00"
        },
        endTime : {
            min    : "16:00",
            max    : "19:00",
            "default": "17:30"
        },
        notes : {
            "default" : ""
        },
        step : 15
    };

    $.ajax({
        type    : "GET",
        url     : "/settings"
    }).fail(function(jqXHR, textStatus, errorThrown) {
        //do something
    }).done(function(data) {
        handleAjaxSettingsSuccess(data);
    });

    /**
     * Set Settings from server
     * @param data
     */
    var handleAjaxSettingsSuccess = function(data) {
        if(data.success) {
            var s = data.settings;
            defaultSettings.startTime.min       = s.min_start_time;
            defaultSettings.startTime.max       = s.max_start_time;
            defaultSettings.endTime.min         = s.min_end_time;
            defaultSettings.endTime.max         = s.max_end_time;
            //defaultSettings.step                = s.step;
            //defaultSettings.startTime.default   = s.default_start_time;
            //defaultSettings.endTime.default     = s.default_end_time;
        }
    }

    /**
     * Utils Class
     * @type {{fadeout: fadeout, addError: addError}}
     */
    var utils = {

        /**
         * Fadeout a class
         * @param elem
         */
        fadeout: function(elem, className) {
            window.setTimeout(function() {
                elem.addClass("fadeout");
                window.setTimeout(function() {
                    elem.removeClass(className);
                    elem.removeClass("fadeout");
                }, 1000);
            }, 1000);
        },

        /**
         * Add multiple Errors
         *
         * @param obj
         */
        addErrors: function(elemName, errors, row) {
            for(var prop in errors) {
                if(errors.hasOwnProperty(prop)) {
                    this.addError(elemName, errors[prop], row);
                }
            }
        },

        /**
         * Add an error to the form error list
         * @param elemName
         * @param error
         */
        addError: function(elemName, error, row) {
            var errors = row.find(".time-book-errors");
            var errorList = row.find(".form-errors");

            errors.removeClass("hidden");
            errorList.append("<li><strong>" + elemName + ": </strong>" + error + "</li>");
        },

        resetErrors: function(row) {
            var errors = row.find(".time-book-errors");
            var errorList = row.find(".form-errors");
            errors.addClass("hidden");
            errorList.empty();

            $('.input-group').each(function(i, elem) {
                $(elem).removeClass("has-error");
            });
        },

        compareDateToday: function(date) {
            var today   = new Date();
            today.setHours(0,0,0,0);

            var datePieces = date.split("-");
            var date = new Date(datePieces[2], (datePieces[1] - 1), datePieces[0]);

            if(date > today) {
                return 1;
            } else if(date < today) {
                return -1;
            } else {
                return 0;
            }
        },

        addClassesToRow: function(date, row, type) {
            var compareResult = utils.compareDateToday(date);
            var classes = ["no-booking"];

            switch(compareResult) {
                case 1:
                    classes.push("no-booking-future");
                    break;
                case -1:
                    classes.push("no-booking-past");
                    break;
            }

            if(type == "add") {
                row.removeClass(classes.join(" "));
            } else if(type == "remove") {
                row.addClass(classes.join(" "));
            }
        },

        hideEditRow: function(row) {
            row.prev().removeClass("editing");
            row.addClass("hidden").removeClass("open");
        },

        showEditRow: function(row) {
            row.prev().addClass("editing");
            row.addClass("open").removeClass("hidden");
        }
    };


    /**
     * Confirm Remove booking event
     */
    $(document).on('click', '.remove-booking', function(e) {
        e.preventDefault();
        var row = $(this).parents(".edit-booking");
        var date    = row.data("date");
        var id      = row.data("id");
        var button  = $(this);

        button.toggleClass('active');

        $.ajax({
            type    : "DELETE",
            url     : "/time-rest/" + id
        }).fail(function(jqXHR, textStatus, errorThrown) {
            handleAjaxFail(jqXHR, textStatus, errorThrown, button, row);
        }).done(function(data) {
            handleAjaxDeleteSuccess(data, date, button, row);
        });
    });

    $(document).on('click', '.cancel-edit', function(e) {
        e.preventDefault();
        var row = $(this).parents(".edit-booking");
        utils.hideEditRow(row);
    });

    $(document).on('click', 'tr.day', function(e) {
        e.stopPropagation();
        e.preventDefault();

        $(".editing").removeClass("editing");

        var editRow = $(this).next();
        var type = "edit";
        if($(this).hasClass("no-booking")) {
            type = "add";
            editRow.find(".remove-booking").attr("disabled", "disabled");

            //add defaults
            editRow.find("#book-starttime").val(defaultSettings.startTime.default);
            editRow.find("#book-endtime").val(defaultSettings.endTime.default);
            editRow.find("#book-notes").val(defaultSettings.notes.default);
        } else {
            editRow.find(".remove-booking").removeAttr("disabled");

            var time = $(this).find(".col-time").text().split(" - ");
            editRow.find("#book-starttime").val(time[0]);
            editRow.find("#book-endtime").val(time[1]);
            editRow.find("#book-notes").val($(this).find(".col-notes span").attr("data-original-title"));
        }

        if(editRow.hasClass("open")) {
            utils.hideEditRow(editRow);
            return false;
        }
        $(".edit-booking.open").addClass("hidden").removeClass("open");
        $(this).addClass("editing");

        var bookdate    = editRow.find("#book-date");
        var date        = editRow.data("date");
        editRow.data("type", type);
        editRow.data("id", $(this).data("id"));

        bookdate.val(date);
        utils.showEditRow(editRow);
    });

    /**
     *
     * @param data
     * @param date
     * @param button
     * @param row
     */
    var handleAjaxDeleteSuccess = function(data, date, button, row)
    {
        if(!data.success) {
            button.toggleClass('active');
            return;
        }

        var dataRow = row.prev();
        dataRow.find(".col-total").empty();
        dataRow.find(".col-notes").empty();

        var addBookingTemplate = $("#add-book-action-template").clone();

        dataRow.find(".col-time").empty();
        dataRow.find(".col-actions").html(addBookingTemplate.html());

        //empty for elements
        row.find("#book-notes").val("");
        row.find("#book-starttime").val(defaultSettings.startTime.default);
        row.find("#book-endtime").val(defaultSettings.endTime.default);
        dataRow.removeAttr("data-id");

        //add the correct class to row
        utils.addClassesToRow(date, dataRow, "remove");

        updateSideTotals(data.totals);
        dataRow.addClass("danger");
        utils.fadeout(dataRow, "danger");

        button.toggleClass('active');
        utils.hideEditRow(row);
    }

    /**
     * Add/Edit booking event
     */
    $(document).on('click', '.save-booking', function(e ){
        e.preventDefault();

        var submitBtn   = $(this);
        var row         = $(this).parents('tr');
        utils.resetErrors(row);
        var date        = row.data("date");
        var id          = row.data("id");
        var type        = row.data("type");
        var startTime   = row.find("#book-starttime");
        var endTime     = row.find("#book-endtime");

        var validStart  = window.timeUtils(startTime.val(), defaultSettings.startTime, defaultSettings.step);
        var validEnd    = window.timeUtils(endTime.val(), defaultSettings.endTime, defaultSettings.step);

        //if not string, then it is an array of errors
        if(typeof validStart !== "string" || typeof validEnd !== "string") {
            //add the errors
            if(typeof validStart !== "string") {
                utils.addErrors("Start Time", validStart, row);
                startTime.parent().addClass("has-error");
            }

            if(typeof validEnd !== "string") {
                endTime.parent().addClass("has-error");
                utils.addErrors("End Time", validEnd, row);
            }

            return false;
        }

        startTime.val(validStart);
        endTime.val(validEnd);

        submitBtn.toggleClass('active');

        var data = {
            'date'      : date,
            'startTime' : row.find("#book-starttime").val(),
            'endTime'   : row.find("#book-endtime").val(),
            'notes'     : row.find("#book-notes").val()
        }

        if(type == "edit") {
            var url = "/time-rest/" + id;
            var method = 'PUT';
        } else if(type == "add") {
            var url = "/time-rest";
            var method = 'POST';
        }

        $.ajax({
            type    : "POST",
            method  : method,
            url     : url,
            data    : data
        }).fail(function(jqXHR, textStatus, errorThrown) {
            handleAjaxFail(jqXHR, textStatus, errorThrown, submitBtn, row);
        }).done(function(data) {
            handleAjaxSuccess(data, date, submitBtn, row , type);
        });
    });



    /**
     * AJAX Success
     *
     * @param data
     * @param submitBtn
     * @param popover
     */
    var handleAjaxSuccess = function (data, date, submitBtn, rowElem, type)
    {
        if(!data.success) {

            if(typeof data.messages.startTime != 'undefined') {
                utils.addErrors('Start Time' ,data.messages.startTime, rowElem);
            }

            if(typeof data.messages.endTime != 'undefined') {
                utils.addErrors('End Time' ,data.messages.endTime, rowElem);
            }

            if(typeof data.messages.date != 'undefined') {
                utils.addErrors('Date' ,data.messages.date, rowElem);
            }

            submitBtn.toggleClass('active');
            return;
        }

        var booking = data.booking;
        var row = $("#row-"+ date);
        row.data("id", data.booking.id);
        var time = booking.startTime + " - " + booking.endTime;
        row.find(".col-time").text(time);
        row.find(".col-total").text(booking.total);

        if(booking.notes.length > 0) {
            var notesContent = $("#notepop-template").clone();
            notesContent.attr("title", booking.notes);
            notesContent.removeClass("hidden");
            row.find(".col-notes").html(notesContent);

            //trigger note popups again
            $(".fx-tooltip").tooltip();
        } else {
            row.find(".col-notes").empty();
        }

        if(type === "add") {
            var actionsTemplate = $("#edit-book-action-template").clone();
            row.find(".col-actions").html(actionsTemplate.html());
            utils.addClassesToRow(date, row, "add");
        }

        updateSideTotals(data.totals);

        console.log(row.closest(".week-total"));

        row.addClass("success");
        utils.fadeout(row, "success");

        submitBtn.toggleClass('active');

        utils.hideEditRow(rowElem);
    };

    var updateSideTotals = function(totals) {

        var sideTotals      = $("#totals");
        var workedToDate    = sideTotals.find(".worked-to-date");
        var balanceMonth    = sideTotals.find(".balance-month span b");
        var runningBalance  = $("#running-balance").find(".balance-running span b");

        workedToDate.text(totals.monthTotalWorkedHours + " / " + totals.monthTotalHours);
        signTotal(totals.monthBalance, balanceMonth, true);
        signTotal(totals.runningBalance, runningBalance, true);

        var totalsBrief     = $("#totals-brief");
        var workedToDate    = totalsBrief.find(".worked-to-date");
        var balanceMonth    = totalsBrief.find(".balance-month");
        var runningBalance  = totalsBrief.find(".balance-running");


        workedToDate.text(totals.monthTotalWorkedHours);
        signTotal(totals.monthBalance, balanceMonth, false);
        signTotal(totals.runningBalance, runningBalance, false);
    };

    var signTotal = function(total, elem, modifyClass) {
        total = '' + total; //turn into string
        var sign = total.charAt(0);

        if(sign == "-") {
            total = total.replace("-", "- ");
            if(modifyClass) {
                elem.addClass("balance-danger").removeClass("balance-success");
            }
        } else {
            total = '+ ' + total; //sign positive totals
            if(modifyClass) {
                elem.addClass("balance-success").removeClass("balance-danger");
            }
        }

        elem.text(total);
    }

    /**
     * AJAX Fail
     *
     * @param jqXHR
     * @param textStatus
     * @param errorThrown
     */
    var handleAjaxFail = function(jqXHR, textStatus, errorThrown, submitBtn, row) {
        submitBtn.toggleClass('active');
        utils.addError("Error", errorThrown, row);
        utils.addError("Error", textStatus, row);
        utils.addError("Error", jqXHR, row);
    };

    $(document).click(function (e) {
        if($(event.target).parents().index($('.edit-booking.open')) == -1) {
            $(".edit-booking.open").prev().removeClass("editing");
            $(".edit-booking.open").removeClass("open").addClass("hidden");
        }
    });

});
