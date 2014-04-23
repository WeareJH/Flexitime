<?php

namespace JhFlexiTime;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\EventManager\EventInterface;

/**
 * JhFlexiTime Module
 * 
 * @author Ben Lill <ben@wearejh.com>
 */
class Module implements
    ConfigProviderInterface,
    AutoloaderProviderInterface
{

    public function onBootstrap(EventInterface $e)
    {
        $sl             = $e->getApplication()->getServiceManager();
        $balanceService = $sl->get('JhFlexiTime\Service\BalanceService');
        $eventManager   = $e->getTarget()->getEventManager();
        $eventManager->attach($sl->get('JhFlexiTime\Listener\BookingSaveListener'));

        $sharedEvm = $e->getApplication()->getEventManager()->getSharedManager();
        $sharedEvm->attach('ZfcUser\Service\User', 'register.post', function ($e) use ($balanceService) {
            $balanceService->setupInitialRunningBalance($e->getParam('user'));
        });

        $sharedEvm->attach('ScnSocialAuth\Authentication\Adapter\HybridAuth', 'registerViaProvider.post', function ($e) use ($balanceService) {
            $balanceService->setupInitialRunningBalance($e->getParam('user'));
        });
    }


    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
 
    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
