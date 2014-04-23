describe("Date validation", function () {


    var validate;
    var range = {
        min    : "07:00",
        max    : "10:00",
        "default": "07:00"
    };

    var step = 15;

    beforeEach(function(){
        validate = window.timeUtils;
    });

    it("should be a function", function () {
        expect(typeof validate === "function").toBe(true);
    });

    it("should validate", function () {
        var actual = validate("08:00", range, step);
        var expected = "08:00";
        expect(actual).toBe(expected);
    });

    it("should validate 15 min increment", function () {
        var actual = validate("08:15", range, step);
        var expected = "08:15";
        expect(actual).toBe(expected);
    });

    it("should validate 15 min increment with non padded hour", function () {
        var actual = validate("8:45", range, step);
        var expected = "08:45";
        expect(actual).toBe(expected);
    });

    it("should pad hour", function () {
        var actual = validate("8:00", range, step);
        var expected = "08:00";
        expect(actual).toBe(expected);
    });

    it("should validate", function () {
        var actual = validate("07:00", range, step);
        var expected = "07:00";
        expect(actual).toBe(expected);
    });

    it("should fail increment and less than", function () {
        var actual = validate("06:59", range, step);
        var lessThanError = "Time should not be less than 07:00";
        var incrementError = "Time should be booked in 15 minute increments";
        expect(actual).toContain(lessThanError);
        expect(actual).toContain(incrementError);
    });

    it("should fail 15 increment check", function () {
        var actual = validate("08:35", range, step);
        var expected = ["Time should be booked in 15 minute increments"];
        expect(actual).toEqual(expected);
    });

    it("should fail time format check on hour", function () {
        var actual = validate("008:00", range, step);
        var expected = ["Time should be in the format hh:mm or h:mm"];
        expect(actual).toEqual(expected);
    });

    it("should fail time format check on minutes 1", function () {
        var actual = validate("08:000", range, step);
        var expected = ["Time should be in the format hh:mm or h:mm"];
        expect(actual).toEqual(expected);
    });

    it("should fail time format check on minutes 2", function () {
        var actual = validate("08:0", range, step);
        var expected = ["Time should be in the format hh:mm or h:mm"];
        expect(actual).toEqual(expected);
    });

    it("should validate when equal to max", function () {
        var actual = validate("10:00", range, step);
        var expected = "10:00";
        expect(actual).toBe(expected);
    });

    it("should fail greater than", function () {
        var actual = validate("10:15", range, step);
        var lessThanError = "Time should not be greater than 10:00";
        expect(actual).toContain(lessThanError);
    });

    it("should fail greater than and not 15 min increment", function () {
        var actual = validate("10:13", range, step);
        var lessThanError = "Time should not be greater than 10:00";
        var incrementError = "Time should be booked in 15 minute increments";
        expect(actual).toContain(lessThanError);
        expect(actual).toContain(incrementError);

    });
});