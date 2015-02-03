<?php

namespace JhFlexiTime\Controller\Factory;

use JhFlexiTime\Controller\UserReminderCliController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class UserReminderCliControllerFactory
 * @package JhFlexiTime\Controller\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserReminderCliControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return UserReminderCliController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //get parent locator
        $serviceLocator = $serviceLocator->getServiceLocator();

        return new UserReminderCliController(
            $serviceLocator->get('JhFlexiTime\Service\MissingBookingReminderService'),
            $serviceLocator->get('Console')
        );
    }
}
