<?php

namespace JhFlexiTime\Service\Factory;

use JhFlexiTime\Service\BalanceService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BalanceServiceFactory
 * @package JhFlexiTime\Service\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BalanceServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return BalanceService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new BalanceService(
            $serviceLocator->get('FlexiOptions'),
            $serviceLocator->get('JhFlexiTime\Repository\BalanceRepository'),
            $serviceLocator->get('JhFlexiTime\ObjectManager'),
            $serviceLocator->get('JhFlexiTime\Service\PeriodService')
        );
    }
}
