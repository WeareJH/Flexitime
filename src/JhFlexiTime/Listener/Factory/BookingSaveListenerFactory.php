<?php

namespace JhFlexiTime\Listener\Factory;

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
            $serviceLocator->get('JhFlexiTime\ObjectManager'),
            $serviceLocator->get('JhFlexiTime\Repository\BalanceRepository'),
            new \DateTime,
            $serviceLocator->get('FlexiOptions')
        );
    }
}
