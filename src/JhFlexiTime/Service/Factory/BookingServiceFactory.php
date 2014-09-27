<?php

namespace JhFlexiTime\Service\Factory;

use JhFlexiTime\Service\BookingService;
use JhFlexiTime\Stdlib\Hydrator\Strategy\UserStrategy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JhFlexiTime\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

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
        $objectManager  = $serviceLocator->get('JhFlexiTime\ObjectManager');
        $hydrator       = new DoctrineHydrator($objectManager, 'JhFlexiTime\Entity\Booking');
        $hydrator->addStrategy('user', new UserStrategy($serviceLocator->get('JhUser\Repository\UserRepository')));

        return new BookingService(
            $serviceLocator->get('FlexiOptions'),
            $serviceLocator->get('JhFlexiTime\Repository\BookingRepository'),
            $objectManager,
            $serviceLocator->get('JhFlexiTime\Service\PeriodService'),
            $hydrator,
            $serviceLocator->get('InputFilterManager')->get('JhFlexiTime\InputFilter\BookingInputFilter')
        );
    }
}
