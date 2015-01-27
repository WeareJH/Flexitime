<?php
namespace JhFlexiTime\Repository\Factory;

use JhFlexiTime\Repository\CappedCreditRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CappedCreditRepositoryFactory
 * @package JhFlexiTime\Repository\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CappedCreditRepositoryFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \JhFlexiTime\Repository\CappedCreditRepository
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $em     = $serviceLocator->get('Doctrine\ORM\EntityManager');
        $meta   = $em->getClassMetadata('JhFlexiTime\Entity\CappedCredit');
        return new CappedCreditRepository($em, $meta);
    }
}
