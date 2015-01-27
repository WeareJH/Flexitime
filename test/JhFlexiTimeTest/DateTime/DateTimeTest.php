<?php

namespace JhFlexiTimeTest\DateTime;

use PHPUnit_Framework_TestCase;
use JhFlexiTime\DateTime\DateTime;

/**
 * Class DateTimeTest
 * @package JhFlexiTimeTest\DateTime
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class DateTimeTest extends PHPUnit_Framework_TestCase
{

    /**
     * @param string $start
     * @param DateTime$end
     * @param DateTime[] $expected
     *
     * @dataProvider monthBetweenProvider
     */
    public function testGetMonthsBetween($start, DateTime $end, array $expected)
    {
        $date = new DateTime($start);
        $period = $date->getMonthsBetween($end);

        $this->assertEquals($expected, $period);
    }

    /**
     * @return array
     */
    public function monthBetweenProvider()
    {
        return [
            [
                'start'     => "3 November 2014 00:00:00",
                'end'       => new DateTime("2 March 2015"),
                'expected'  => [
                    new DateTime("1 November 2014 00:00:00"),
                    new DateTime("1 December 2014 00:00:00"),
                    new DateTime("1 January 2015 00:00:00"),
                    new DateTime("1 February 2015 00:00:00"),
                    new DateTime("1 March 2015 00:00:00"),
                ]
            ],
            [
                'start'     => "3 November 2014 00:00:00",
                'end'       => new DateTime("31 March 2015"),
                'expected'  => [
                    new DateTime("1 November 2014 00:00:00"),
                    new DateTime("1 December 2014 00:00:00"),
                    new DateTime("1 January 2015 00:00:00"),
                    new DateTime("1 February 2015 00:00:00"),
                    new DateTime("1 March 2015 00:00:00"),
                ]
            ],
            [
                'start'     => "30 November 2014 23:59:59",
                'end'       => new DateTime("31 March 2015"),
                'expected'  => [
                    new DateTime("1 November 2014 00:00:00"),
                    new DateTime("1 December 2014 00:00:00"),
                    new DateTime("1 January 2015 00:00:00"),
                    new DateTime("1 February 2015 00:00:00"),
                    new DateTime("1 March 2015 00:00:00"),
                ]
            ]
        ];
    }

    public function testToString()
    {
        $d1 = new \DateTime("11 November 1947 00:00:00");
        $d2 = new DateTime("11 November 1947 00:00:00");

        $this->assertEquals($d1->format('U'), $d2->__toString());
    }

    /**
     * @param DateTime $date
     * @param bool $expected
     * @dataProvider sameMonthYearProvider
     */
    public function testIsSameMonthAndYear(DateTime $date, $expected)
    {
        $subject = new DateTime("11 November 1947 00:00:00");

        $this->assertEquals($expected, $subject->isSameMonthAndYear($date));
    }

    /**
     * @return array
     */
    public function sameMonthYearProvider()
    {
        return [
            [new DateTime("1 November 1947 00:00:00"), true],
            [new DateTime("30 November 1947 23:59:59"), true],
            [new DateTime("1 November 1948 00:00:00"), false],
            [new DateTime("30 November 1948 23:59:59"), false],
            [new DateTime("31 November 1947 23:59:59"), false],
        ];
    }

    public function testStartOfMonth()
    {
        $d = new DateTime("11 November 1947 00:00:00");
        $new = $d->startOfMonth();

        $this->assertNotSame($d, $new);
        $this->assertEquals('01-11-1947 00:00:00', $new->format('d-m-Y H:i:s'));
    }

    public function testEndOfMonth()
    {
        $d = new DateTime("11 November 1947 00:00:00");
        $new = $d->endOfMonth();

        $this->assertNotSame($d, $new);
        $this->assertEquals('30-11-1947 23:59:59', $new->format('d-m-Y H:i:s'));
    }
}
