<?php

namespace JhFlexiTime\Controller\Factory;

use JhFlexiTime\Controller\BookingAdminController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BookingAdminControllerFactory
 * @package JhFlexiTime\Controller\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingAdminControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return BookingAdminController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //get parent locator
        $serviceLocator = $serviceLocator->getServiceLocator();

        return new BookingAdminController(
            $serviceLocator->get('JhFlexiTime\Service\BookingService'),
            $serviceLocator->get('JhFlexiTime\Service\TimeCalculatorService'),
            $serviceLocator->get('JhUser\Repository\UserRepository')
        );
    }
}
