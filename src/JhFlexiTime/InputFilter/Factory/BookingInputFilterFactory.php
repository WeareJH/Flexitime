<?php

namespace JhFlexiTime\InputFilter\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JhFlexiTime\InputFilter\BookingInputFilter;
use JhFlexiTime\Validator\UniqueUserObject;

/**
 * Class BookingInputFilterFactory
 * @package JhFlexiTime\InputFilter\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingInputFilterFactory implements FactoryInterface
{

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return BalanceService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $parentLocator      = $serviceLocator->getServiceLocator();
        $objectManager      = $parentLocator->get('JhFlexiTime\ObjectManager');
        $bookingRepository  = $parentLocator->get('JhFlexiTime\Repository\BookingRepository');
        $user               = $parentLocator->get('zfcuser_auth_service')->getIdentity();
        $bookingOptions     = $parentLocator->get('BookingOptions');

        $uniqueValidator = new UniqueUserObject(array(
            'object_manager'    => $objectManager,
            'object_repository' => $bookingRepository,
            'user'              => $user,
            'fields' => array(
                'date',
                'user',
            )
        ));

        return new BookingInputFilter(
            $uniqueValidator,
            $bookingOptions
        );
    }
}
