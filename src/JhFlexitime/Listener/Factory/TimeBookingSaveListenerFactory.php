<?php

namespace JhFlexiTime\Listener\Factory;

use JhFlexiTime\Listener\BookingSaveListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BookingSaveListenerFactory.php
 * @package JhFlexiTime\Controller\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingSaveListenerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return BookingSaveListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new BookingSaveListener(
            $serviceLocator->get('JhFlexiTime\Service\BalanceService'),
            $serviceLocator->get('JhFlexiTime\Repository\BookingRepository')
        );
    }
}
