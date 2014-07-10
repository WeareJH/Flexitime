<?php
namespace JhFlexiTime\Repository\Factory;
 
use JhFlexiTime\Repository\BalanceRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BalanceRepositoryFactory
 * @package JhFlexiTime\Repository\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BalanceRepositoryFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \JhFlexiTime\Repository\BalanceRepository
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new BalanceRepository(
            $serviceLocator->get('JhFlexiTime\ObjectManager')->getRepository('JhFlexiTime\Entity\RunningBalance'),
            $serviceLocator->get('JhFlexiTime\ObjectManager')
        );
    }
}
