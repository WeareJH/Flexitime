<?php

namespace JhFlexiTime\NotificationHandler\Factory;

use JhFlexiTime\NotificationHandler\MissedBookingEmailNotificationHandler;
use rfreebern\Giphy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class MissedBookingEmailNotificationHandlerFactory
 * @package JhFlexiTime\NotificationHandler\Factory
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class MissedBookingEmailNotificationHandlerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return MissedBookingEmailNotificationHandler
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MissedBookingEmailNotificationHandler(
            $serviceLocator->get('AcMailer\Service\MailService'),
            $serviceLocator->get('JhHubBase\Options\ModuleOptions'),
            new Giphy
        );
    }
}
