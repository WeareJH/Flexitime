<?php

namespace JhFlexiTime\Options\Factory;

use JhFlexiTime\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ModuleOptionsFactory
 * @package JhFlexiTime\Options\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class ModuleOptionsFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return ModuleOptions
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        return new ModuleOptions(
            isset($config['flexi']['policy_options']) ? $config['flexi']['policy_options'] : []
        );
    }
}
