<?php

namespace JhFlexiTimeTest\Options;

use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Options\NotificationOptions;
use PHPUnit_Framework_TestCase;

/**
 * Class NotificationOptionsTest
 * @package JhFlexiTimeTest\Options
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class NotificationOptionsTest extends PHPUnit_Framework_TestCase
{
    public function testSetRemindStartThrowsExceptionIfNotDateStringInvalid()
    {
        $this->setExpectedException('Exception');
        $options = new NotificationOptions;
        $options->setRemindStart('lolnotadate');
    }

    public function testGetSetRemindStart()
    {
        $options = new NotificationOptions;
        $options->setRemindStart('3 days ago');
        $this->assertEquals(new DateTime('3 days ago 00:00:00'), $options->getRemindStart());
    }

    public function testSetRemindDaysThrowsExceptionIfStringInvalid()
    {
        $this->setExpectedException('InvalidArgumentException', 'remind_days should be like: "7 days"');
        $options = new NotificationOptions;
        $options->setRemindDays('invalid');
    }

    public function testGetSetRemindDays()
    {
        $options = new NotificationOptions;
        $options->setRemindDays('3 days');
        $this->assertEquals('3 days', $options->getRemindDays());
    }

    public function testGetRemindPeriod()
    {
        $options = new NotificationOptions;
        $options->setRemindDays('3 days');
        $options->setRemindStart('11 November 2014');

        $result = $options->getRemindPeriod();

        $expected = [
            new DateTime('7 November 2014'),
            //8 - 9 Is weekend
            new DateTime('10 November 2014'),
            new DateTime('11 November 2014'),
        ];

        $this->assertEquals($expected, $result);
    }
}
