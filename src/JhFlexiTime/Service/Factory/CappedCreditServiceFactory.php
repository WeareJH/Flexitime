<?php

namespace JhFlexiTime\Service\Factory;

use JhFlexiTime\Service\BookingService;
use JhFlexiTime\Service\CappedCreditService;
use JhFlexiTime\Stdlib\Hydrator\Strategy\UserStrategy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JhFlexiTime\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * Class CappedCreditServiceFactory
 * @package JhFlexiTime\Service\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CappedCreditServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return BookingService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CappedCreditService(
            $serviceLocator->get('JhFlexiTime\Repository\CappedCreditRepository'),
            $serviceLocator->get('JhFlexiTime\ObjectManager')
        );
    }
}
