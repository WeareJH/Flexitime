<?php

namespace JhFlexiTime\Entity\Factory;

use JhFlexiTime\Entity\UserSettings;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class USerSettingsFactory
 * @package JhFlexiTime\Repository\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class UserSettingsFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \JhFlexiTime\Entity\UserSettings
     * @throws \InvalidArgumentException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $userService = $serviceLocator->get('zfcuser_auth_service');

        if(!$userService->hasIdentity()) {
            throw new \InvalidArgumentException("User is not authenticated");
        }

        $userSettingsRepository = $serviceLocator->get('JhFlexiTime\Repository\UserSettingsRepository');
        $userSettings           = $userSettingsRepository->findOneByUser($userService->getIdentity());

        if(!$userSettings) {
            throw new \InvalidArgumentException("User does not have a settings row");
        }

        return $userSettings;
    }
}