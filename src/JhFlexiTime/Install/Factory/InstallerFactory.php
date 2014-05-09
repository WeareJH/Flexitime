<?php

namespace JhFlexiTime\Install\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JhFlexiTime\Install\Installer;

/**
 * Class InstallerFactory
 * @package JhFlexiTime\Installer\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class InstallerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return Installer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Installer(
            $serviceLocator->get('Jhuser\Repository\UserRepository'),
            $serviceLocator->get('JhFlexiTime\Repository\UserSettingsRepository'),
            $objectManager = $serviceLocator->get('JhFlexiTime\ObjectManager')
        );
    }
}
