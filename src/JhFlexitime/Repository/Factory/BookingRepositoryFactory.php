<?php
namespace JhFlexiTime\Repository\Factory;
 
use JhFlexiTime\Repository\BookingRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
 
/**
 * Booking repository Factory
 * 
 * @author Ben Lill <ben@wearejh.com>
 */
class BookingRepositoryFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \JhFlexiTime\Repository\BookingRepository
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new BookingRepository(
            $serviceLocator->get('JhFlexiTime\ObjectManager')->getRepository('JhFlexiTime\Entity\Booking')
        );
    }
}
