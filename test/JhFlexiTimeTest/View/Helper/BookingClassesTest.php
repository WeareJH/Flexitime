<?php

namespace JhFlexiTimeTest\View\Helper;

use JhFlexiTime\View\Helper\BookingClasses;

/**
 * Class BookingClassesTest
 * @package JhFlexiTimeTest\View\Helper
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingClassesTest extends \PHPUnit_Framework_TestCase
{
    protected $helper;

    public function setUp()
    {
        $this->helper = new BookingClasses();
    }

    public function testTodayReturnsCurrentDayClassWithBooking()
    {
        $this->assertEquals('today', $this->helper->__invoke(new \DateTime("today"), true));
    }

    public function testAnyOtherDayReturnsNothingWithBooking()
    {
        $this->assertEquals('', $this->helper->__invoke(new \DateTime("yesterday"), true));
    }

    public function testTodayReturnsCurrentDayClassAndNoBooking()
    {
        $this->assertEquals('today no-booking', $this->helper->__invoke(new \DateTime("today"), false));
    }

    public function testPreviousDayReturnsNoPastBooking()
    {
        $this->assertEquals('no-booking no-booking-past', $this->helper->__invoke(new \DateTime("yesterday"), false));
    }

    public function testFutureDayReturnsNoFutureBooking()
    {
        $this->assertEquals('no-booking no-booking-future', $this->helper->__invoke(new \DateTime("tomorrow"), false));
    }
}
