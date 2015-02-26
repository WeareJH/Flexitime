<?php

namespace JhFlexiTimeTest\NotificationHandler\Factory;

use JhFlexiTime\NotificationHandler\Factory\MissedBookingEmailNotificationHandlerFactory;
use JhHubBase\Options\ModuleOptions;
use PHPUnit_Framework_TestCase;

/**
 * Class MissedBookingEmailNotificationHandlerFactoryTest
 * @package JhFlexiTime\NotificationHandler\Factory
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class MissedBookingEmailNotificationHandlerFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $services         = [
            'AcMailer\Service\MailService'      => $this->getMock('AcMailer\Service\MailServiceInterface'),
            'JhHubBase\Options\ModuleOptions'   => new ModuleOptions
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

        $factory = new MissedBookingEmailNotificationHandlerFactory();
        $this->assertInstanceOf(
            'JhFlexiTime\NotificationHandler\MissedBookingEmailNotificationHandler',
            $factory->createService($serviceLocator)
        );
    }
}
