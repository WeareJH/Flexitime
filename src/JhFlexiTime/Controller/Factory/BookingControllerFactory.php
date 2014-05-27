<?php

namespace JhFlexiTime\Controller\Factory;

use JhFlexiTime\Controller\BookingController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BookingControllerFactory
 * @package JhFlexiTime\Controller\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return BookingAdminController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //get parent locator
        $serviceLocator = $serviceLocator->getServiceLocator();

        return new BookingController(
            $serviceLocator->get('JhFlexiTime\Service\BookingService'),
            $serviceLocator->get('JhFlexiTime\Service\TimeCalculatorService'),
            $serviceLocator->get('FormElementManager')->get('JhFlexiTime\Form\BookingForm')
        );
    }
}
