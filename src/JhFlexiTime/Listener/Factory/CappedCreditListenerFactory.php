<?php

namespace JhFlexiTime\Listener\Factory;

use JhFlexiTime\Listener\CappedCreditListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CappedCreditListenerFactory
 * @package JhFlexiTime\Controller\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class CappedCreditListenerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return CappedCreditListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CappedCreditListener(
            $serviceLocator->get('JhFlexiTime\Service\CappedCreditService'),
            $serviceLocator->get('FlexiOptions')
        );
    }
}
