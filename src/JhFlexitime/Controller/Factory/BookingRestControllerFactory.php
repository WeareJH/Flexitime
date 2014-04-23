<?php

namespace JhFlexiTime\Controller\Factory;

use JhFlexiTime\Controller\BookingRestController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BookingRestControllerFactory
 * @package JhFlexiTime\Controller\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingRestControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return BookingRestController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //get parent locator
        $serviceLocator = $serviceLocator->getServiceLocator();

        return new BookingRestController(
            $serviceLocator->get('JhFlexiTime\Service\BookingService'),
            $serviceLocator->get('JhFlexiTime\Service\TimeCalculatorService')
        );
    }
}
