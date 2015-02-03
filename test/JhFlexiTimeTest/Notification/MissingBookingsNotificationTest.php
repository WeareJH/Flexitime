<?php

namespace JhFlexiTime\Notification;

use PHPUnit_Framework_TestCase;

/**
 * Class MissingBookingsNotificationTest
 * @package JhFlexiTime\Notification
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MissingBookingsNotificationTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $event = new MissingBookingsNotification([], []);

        $this->assertSame('missing-bookings', $event->getName());
        $this->assertSame([], $event->getPeriod());
        $this->assertSame([], $event->getMissingBookings());
    }
}
