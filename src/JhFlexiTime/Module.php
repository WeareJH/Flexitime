<?php

namespace JhFlexiTime;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\EventManager\EventInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use JhFlexiTime\Entity\UserSettings;

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

        $sharedEvm = $eventManager->getSharedManager();

        //add roles to users created via HybridAuth
        $sharedEvm->attach(
            'ScnSocialAuth\Authentication\Adapter\HybridAuth',
            'registerViaProvider.post',
            [$this, 'onRegister']
        );

        //add roles to users created via ZfcUser
        $sharedEvm->attach('ZfcUser\Service\User', 'register.post', [$this, 'onRegister']);
    }

    /**
     * Create User FlexiTime Settings
     * @param EventInterface $e
     */
    public function onRegister(EventInterface $e)
    {
        $application    = $e->getTarget();
        $sm             = $application->getServiceManager();
        $objectManager  = $sm->get('JhFlexiTime\ObjectManager');
        $user           = $e->getParam('user');

        //TODO: Pull default start + end time from config
        $userSettings = new UserSettings();
        $userSettings->setDefaultStartTime(new \DateTime("09:00"));
        $userSettings->setDefaultEndTime(new \DateTime("17:30"));
        $userSettings->setFlexStartDate(new \DateTime());
        $userSettings->setUser($user);

        $objectManager->persist($userSettings);
        $objectManager->flush();
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
            're-calc-balance-user <userEmail>' =>
                "Recalculate a User's running balance",
            're-calc-balance-all ' =>
                "Recalculate all User's running balance",
            'calc-prev-month-balance' =>
                "Calculate the previous month balance for all users and add it on to their running balance",
        ];
    }
}
