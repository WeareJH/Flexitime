<?php

namespace JhFlexiTime\Service\Factory;

use JhFlexiTime\Service\TimeCalculatorService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JhFlexiTime\DateTime\DateTime;

/**
 * Class TimeCalculatorServiceFactory
 * @package JhFlexiTime\Service\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class TimeCalculatorServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return TimeCalculatorService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        return new TimeCalculatorService(
            $serviceLocator->get('FlexiOptions'),
            $serviceLocator->get('JhFlexiTime\Repository\BookingRepository'),
            $serviceLocator->get('JhFlexiTime\Repository\BalanceRepository'),
            $serviceLocator->get('JhFlexiTime\Service\PeriodService'),
            new DateTime('today')
        );
    }
}
