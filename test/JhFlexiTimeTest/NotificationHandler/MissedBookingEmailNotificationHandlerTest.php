<?php

namespace JhFlexiTime\NotificationHandler;

use JhFlexiTime\Notification\MissingBookingsNotification;
use JhHubBase\Module;
use JhHubBase\Notification\Notification;
use JhHubBase\Options\ModuleOptions;
use JhUser\Entity\User;
use PHPUnit_Framework_TestCase;

class MissedBookingEmailNotificationHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $handler;
    protected $mailService;

    public function setUp()
    {
        $this->mailService = $this->getMock('\AcMailer\Service\MailServiceInterface');
        $this->handler = new MissedBookingEmailNotificationHandler($this->mailService, new ModuleOptions);
    }

    public function testShouldHandle()
    {
        $notification = new MissingBookingsNotification([], []);
        $this->assertTrue($this->handler->shouldHandle($notification));

        $notification = new Notification('missing-bookings', []);
        $this->assertFalse($this->handler->shouldHandle($notification));

        $notification = new Notification('different-notification', []);
        $this->assertFalse($this->handler->shouldHandle($notification));
    }

    public function testHandle()
    {
        $user = new User;
        $user->setEmail('aydin@hotmail.co.uk');
        $notification = new MissingBookingsNotification([], []);

        $this->mailService
            ->expects($this->once())
            ->method('setSubject')
            ->with('Missing Bookings');

        $message = $this->getMock('Zend\Mail\Message');
        $this->mailService
            ->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue($message));

        $message
            ->expects($this->once())
            ->method('setTo')
            ->with(['aydin@hotmail.co.uk']);

        $this->mailService
            ->expects($this->once())
            ->method('setTemplate')
            ->with($this->isInstanceOf('Zend\View\Model\ViewModel'));

        $this->mailService
            ->expects($this->once())
            ->method('send');

        $this->handler->handle($notification, $user);
    }
}
