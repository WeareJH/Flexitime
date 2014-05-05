<?php
namespace JhFlexiTime\Repository\Factory;
 
use JhFlexiTime\Repository\UserSettingsRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class UserSettingsRepositoryFactory
 * @package JhFlexiTime\Repository\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class UserSettingsRepositoryFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \JhFlexiTime\Repository\USerSettingsRepository
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new UserSettingsRepository(
            $serviceLocator->get('JhFlexiTime\ObjectManager')->getRepository('JhFlexiTime\Entity\UserSettings')
        );
    }
}
