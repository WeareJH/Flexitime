<?php

namespace JhFlexiTime\Options\Factory;

use JhFlexiTime\Options\BookingOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BookingOptionsFactory
 * @package JhFlexiTime\Options\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingOptionsFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return ModuleOptions
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        return new BookingOptions(
            isset($config['flexi']['booking_options']) ? $config['flexi']['booking_options'] : array()
        );
    }
}
