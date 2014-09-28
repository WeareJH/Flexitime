<?php

namespace JhFlexiTimeTest\Options;

use JhFlexiTime\Options\BookingOptions;

/**
 * Class BookingOptionsTest
 * @package JhFlexiTimeTest\Options
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test The default options are correct
     */
    public function testDefaults()
    {
        $options = new BookingOptions();
        $this->assertFalse($options->getMinStartTime(), 'min_start_time must default to false');
        $this->assertFalse($options->getMaxStartTime(), 'max_start_time must default to false');
        $this->assertFalse($options->getMinEndTime(), 'min_end_time must default to false');
        $this->assertFalse($options->getMaxEndTime(), 'max_end_time must default to false');
    }

    public function testSetValues()
    {
        $options = new BookingOptions([
            'min_start_time'    => '08:00',
            'max_start_time'    => '10:00',
            'min_end_time'      => '16:00',
            'max_end_time'      => false,
        ]);

        $this->assertEquals($options->getMinStartTime(), '08:00');
        $this->assertEquals($options->getMaxStartTime(), '10:00');
        $this->assertEquals($options->getMinEndTime(), '16:00');
        $this->assertEquals($options->getMaxEndTime(), false);
    }

    /**
     * @dataProvider invalidTimeProvider
     */
    public function testSetTimeThrowsExceptionIfNotValid24HourTime($field, $value = 'not-a-date')
    {
        $this->setExpectedException(
            '\InvalidArgumentException',
            sprintf('%s should be a 24 hour time in the format HH:MM', $field)
        );
        $options = new BookingOptions([$field => $value]);
    }

    public function invalidTimeProvider()
    {
        return [
            ['min_start_time', 'not-a-date'],
            ['max_start_time', 'not-a-date'],
            ['min_end_time',   'not-a-date'],
            ['max_end_time',   'not-a-date'],
        ];
    }

    /**
     * @param string $value
     * @param bool $expected
     * @dataProvider timeProvider
     */
    public function testValidateTime($value, $expected)
    {
        $options = new BookingOptions();
        $this->assertEquals($options->validateTime($value), $expected);
    }

    public function timeProvider()
    {
        return [
            ["10:00"           ,true],
            ["25:00"           ,false],
            ["1000"            ,false],
            ["not-a-time"      ,false],
            ["24:00"           ,false],
            ["23:59"           ,true],
            ["00:00"           ,true],
        ];
    }

    public function testSetInvalidPropertyThrowsException()
    {
        $this->setExpectedException(
            'BadMethodCallException',
            'The option "not-a-valid-property" is not a valid property'
        );
        $options = new BookingOptions(['not-a-valid-property' => 'some-value']);
    }
}
