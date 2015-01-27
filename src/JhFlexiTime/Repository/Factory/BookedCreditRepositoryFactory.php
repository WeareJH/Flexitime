<?php
namespace JhFlexiTime\Repository\Factory;
 
use JhFlexiTime\Repository\BookedCreditRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BookedCreditRepositoryFactory
 * @package JhFlexiTime\Repository\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookedCreditRepositoryFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \JhFlexiTime\Repository\BalanceRepository
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $em     = $serviceLocator->get('Doctrine\ORM\EntityManager');
        $meta   = $em->getClassMetadata('\JhFlexiTime\Entity\BookedCredit');
        return new BookedCreditRepository($em, $meta);
    }
}
