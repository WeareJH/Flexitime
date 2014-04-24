<?php

namespace JhFlexiTime\Service\Factory;

use JhFlexiTime\Service\BookingService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * Class BookingServiceFactory
 * @package JhFlexiTime\Service\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return BookingService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $objectManager = $serviceLocator->get('JhFlexiTime\ObjectManager');

        return new BookingService(
            $serviceLocator->get('FlexiOptions'),
            $serviceLocator->get('JhFlexiTime\Repository\BookingRepository'),
            $objectManager,
            $serviceLocator->get('JhFlexiTime\Service\PeriodService'),
            new DoctrineHydrator($objectManager, 'JhFlexiTime\Entity\Booking'),
            $serviceLocator->get('InputFilterManager')->get('JhFlexiTime\InputFilter\BookingInputFilter')
        );
    }
}
