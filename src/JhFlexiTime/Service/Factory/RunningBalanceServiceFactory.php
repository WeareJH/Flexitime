<?php

namespace JhFlexiTime\Service\Factory;

use JhFlexiTime\Service\RunningBalanceService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RunningBalanceServiceFactory
 * @package JhFlexiTime\Service\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunningBalanceServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return RunningBalanceService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new RunningBalanceService(
            $serviceLocator->get('JhUser\Repository\UserRepository'),
            $serviceLocator->get('JhFlexiTime\Repository\UserSettingsRepository'),
            $serviceLocator->get('JhFlexiTime\Repository\BookingRepository'),
            $serviceLocator->get('JhFlexiTime\Repository\BalanceRepository'),
            $serviceLocator->get('JhFlexiTime\Service\PeriodService'),
            $serviceLocator->get('JhFlexiTime\ObjectManager'),
            new \DateTime('today')
        );
    }
}
