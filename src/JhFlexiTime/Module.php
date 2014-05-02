<?php

namespace JhFlexiTime;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\EventManager\EventInterface;
use Zend\Console\Adapter\AdapterInterface as Console;


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
        $sl             = $e->getTarget()->getServiceManager();
        $eventManager   = $e->getTarget()->getEventManager();
        $eventManager->attach($sl->get('JhFlexiTime\Listener\BookingSaveListener'));
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
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    /**
     * @param Console $console
     * @return array|null|string
     */
    public function getConsoleUsage(Console $console)
    {
        return [
            're-calc-balance-user <userEmail>'      => "Recalculate a User's running balance",
            're-calc-balance-all '                  => "Recalculate all User's running balance",
            'calc-prev-month-balance'               => "Calculate the previous month balance for all users and add it on to their running balance",
        ];
    }
}
