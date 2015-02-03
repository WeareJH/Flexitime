<?php

namespace JhFlexiTimeTest\Service;

use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Options\NotificationOptions;
use JhFlexiTime\Service\MissingBookingReminderService;
use JhUser\Entity\User;
use PHPUnit_Framework_TestCase;

/**
 * Class MissingBookingReminderServiceTest
 * @package JhFlexiTimeTest\Service
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MissingBookingReminderServiceTest extends PHPUnit_Framework_TestCase
{
    protected $notificationService;
    protected $userRepository;
    protected $userSettingsRepository;
    protected $bookingRepository;

    /**
     * @var NotificationOptions
     */
    protected $options;

    /**
     * @var MissingBookingReminderService
     */
    protected $service;

    public function setUp()
    {
        $this->notificationService      = $this->getMock('JhHubBase\Notification\NotificationService');
        $this->userRepository           = $this->getMock('JhUser\Repository\UserRepositoryInterface');
        $this->userSettingsRepository   = $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface');
        $this->bookingRepository        = $this->getMock('JhFlexiTime\Repository\BookingRepositoryInterface');
        $this->options                  = new NotificationOptions;
        $this->service = new MissingBookingReminderService(
            $this->notificationService,
            $this->userRepository,
            $this->userSettingsRepository,
            $this->bookingRepository,
            $this->options
        );
    }

    public function testFindAndNotifyMissingBookings()
    {
        $user = new User;

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(true)
            ->will($this->returnValue([$user]));

        $this->options->setRemindStart('11 November 2014');
        $this->options->setRemindDays('2 days');

        $this->bookingRepository
            ->expects($this->at(0))
            ->method('findOneBy')
            ->with(['user' => $user, 'date' => new DateTime('10 November 2014 00:00:00')])
            ->will($this->returnValue(null));

        $this->bookingRepository
            ->expects($this->at(1))
            ->method('findOneBy')
            ->with(['user' => $user, 'date' => new DateTime('11 November 2014 00:00:00')])
            ->will($this->returnValue(null));

        $this->notificationService
            ->expects($this->once())
            ->method('notify')
            ->with($this->isInstanceOf('JhFlexiTime\Notification\MissingBookingsNotification', $user));

        $this->service->findAndNotifyMissingBookings();
    }
}
