<?php

namespace JhFlexiTime;

use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\EventManager\EventInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use JhInstaller\Install\Installable;
use JhInstaller\Install\Exception as InstallException;

/**
 * JhFlexiTime Module
 * 
 * @author Ben Lill <ben@wearejh.com>
 */
class Module implements
    ConfigProviderInterface,
    AutoloaderProviderInterface,
    Installable
{

    public function onBootstrap(EventInterface $e)
    {
        $sl             = $e->getTarget()->getServiceManager();
        $eventManager   = $e->getTarget()->getEventManager();
        $eventManager->attach($sl->get('JhFlexiTime\Listener\BookingSaveListener'));
        $eventManager->attach($sl->get('JhFlexiTime\Listener\CappedCreditListener'));

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
        $sl             = $application->getServiceManager();
        $installer      = $sl->get('JhFlexiTime\Install\Installer');
        $user           = $e->getParam('user');

        try {
            $installer->createSettingsRow($user);
            $installer->createRunningBalanceRow($user);
        } catch (InstallException $e) {
            //will only happen if database schema not created
            //log here
        }
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
            'set user init-balance <userEmail> <balance>' =>
                "Set a user's starting balance"
        ];
    }

    /**
     * @return string
     */
    public function getInstallService()
    {
        return 'JhFlexiTime\Install\Installer';
    }
}
