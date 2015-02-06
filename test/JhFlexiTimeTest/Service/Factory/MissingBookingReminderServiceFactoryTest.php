<?php

namespace JhFlexiTimeTest\Service\Factory;

use JhFlexiTime\Options\NotificationOptions;
use JhFlexiTime\Service\Factory\MissingBookingReminderServiceFactory;
use PHPUnit_Framework_TestCase;

/**
 * Class MissingBookingReminderServiceFactoryTest
 * @package JhFlexiTimeTest\Service\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MissingBookingReminderServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $services         = [
            'JhHubBase\Notification\NotificationService'
                => $this->getMock('JhHubBase\Notification\NotificationService'),
            'JhUser\Repository\UserRepository'
                => $this->getMock('JhUser\Repository\UserRepositoryInterface'),
            'JhFlexiTime\Repository\UserSettingsRepository'
                => $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface'),
            'JhFlexiTime\Repository\BookingRepository'
                => $this->getMock('JhFlexiTime\Repository\BookingRepositoryInterface'),
            'JhFlexiTime\Options\NotificationOptions'
                => new NotificationOptions

        ];

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($serviceName) use ($services) {
                        return $services[$serviceName];
                    }
                )
            );

        $factory = new MissingBookingReminderServiceFactory();
        $this->assertInstanceOf(
            'JhFlexiTime\Service\MissingBookingReminderService',
            $factory->createService($serviceLocator)
        );
    }
}
