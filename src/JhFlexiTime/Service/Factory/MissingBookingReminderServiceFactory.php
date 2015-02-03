<?php

namespace JhFlexiTime\Service\Factory;

use JhFlexiTime\Service\MissingBookingReminderService;
use JhFlexiTime\Service\RunningBalanceService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JhFlexiTime\DateTime\DateTime;

/**
 * Class MissingBookingReminderServiceFactory
 * @package JhFlexiTime\Service\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MissingBookingReminderServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return RunningBalanceService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MissingBookingReminderService(
            $serviceLocator->get('JhHubBase\Notification\NotificationService'),
            $serviceLocator->get('JhUser\Repository\UserRepository'),
            $serviceLocator->get('JhFlexiTime\Repository\UserSettingsRepository'),
            $serviceLocator->get('JhFlexiTime\Repository\BookingRepository'),
            $serviceLocator->get('JhFlexiTime\Options\NotificationOptions')
        );
    }
}
