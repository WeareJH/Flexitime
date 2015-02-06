<?php

namespace JhFlexiTime\Options\Factory;

use JhFlexiTime\Options\NotificationOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class NotificationOptionsFactory
 * @package JhFlexiTime\Options\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class NotificationOptionsFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return NotificationOptions
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        return new NotificationOptions(
            isset($config['flexi']['notification_options']) ? $config['flexi']['notification_options'] : []
        );
    }
}
