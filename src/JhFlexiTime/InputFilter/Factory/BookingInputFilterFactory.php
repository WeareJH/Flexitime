<?php

namespace JhFlexiTime\InputFilter\Factory;

use DoctrineModule\Validator\ObjectExists;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JhFlexiTime\InputFilter\BookingInputFilter;
use JhFlexiTime\Validator\UniqueObject;

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
        $userRepository     = $parentLocator->get('JhUser\Repository\UserRepository');
        $bookingOptions     = $parentLocator->get('BookingOptions');

        $uniqueValidator = new UniqueObject([
            'object_manager'    => $objectManager,
            'object_repository' => $bookingRepository,
            'fields' => [
                'date',
                'user',
            ],
            'use_context' => true,
        ]);

        $userExistsValidator = new ObjectExists([
            'object_repository' => $userRepository,
            'fields'            => ['id']
        ]);

        return new BookingInputFilter(
            $uniqueValidator,
            $userExistsValidator,
            $bookingOptions
        );
    }
}
