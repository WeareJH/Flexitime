<?php

namespace JhFlexiTime\Listener\Factory;

use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Listener\BookingSaveListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BookingSaveListenerFactory
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
            new DateTime,
            $serviceLocator->get('JhFlexiTime\Service\RunningBalanceService')
        );
    }
}
