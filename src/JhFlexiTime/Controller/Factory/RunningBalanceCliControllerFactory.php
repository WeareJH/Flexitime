<?php

namespace JhFlexiTime\Controller\Factory;

use JhFlexiTime\Controller\RunningBalanceCliController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RunningBalanceCliControllerFactory
 * @package JhFlexiTime\Controller\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunningBalanceCliControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return RunningBalanceCliController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //get parent locator
        $serviceLocator = $serviceLocator->getServiceLocator();

        return new RunningBalanceCliController(
            $serviceLocator->get('JhUser\Repository\UserRepository'),
            $serviceLocator->get('JhFlexiTime\Service\RunningBalanceService'),
            $serviceLocator->get('Console')
        );
    }
}
