<?php

namespace JhFlexiTimeTest\Controller;

use JhFlexiTime\Controller\UserReminderCliController;
use PHPUnit_Framework_TestCase;

/**
 * Class UserReminderCliControllerTest
 * @package JhFlexiTimeTest\Controller
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserReminderCliControllerTest extends PHPUnit_Framework_TestCase
{

    public function testFindAndNotifyAction()
    {
        $service = $this->getMockBuilder('\JhFlexiTime\Service\MissingBookingReminderService')
            ->disableOriginalConstructor()
            ->getMock();

        $console = $this->getMock('Zend\Console\Adapter\AdapterInterface');

        $controller = new UserReminderCliController($service, $console);

        $console
            ->expects($this->once())
            ->method('writeLine')
            ->with('Finished! ', 3);

        $service
            ->expects($this->once())
            ->method('findAndNotifyMissingBookings');

        $controller->findAndNotifyMissingBookingsAction();
    }
}
