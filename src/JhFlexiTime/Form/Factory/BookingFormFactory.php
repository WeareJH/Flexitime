<?php

namespace JhFlexiTime\Form\Factory;

use JhFlexiTime\Form\Fieldset\BookingFieldset;
use JhFlexiTime\Form\BookingForm;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BookingFormFactory
 * @package JhFlexiTime\Form\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingFormFactory implements FactoryInterface
{

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return BookingForm
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $parentLocator      = $serviceLocator->getServiceLocator();
        $objectManager      = $parentLocator->get('JhFlexiTime\ObjectManager');
        $user               = $parentLocator->get('zfcuser_auth_service')->getIdentity();
        $bookingFieldset    = new BookingFieldset($objectManager, $user);
        return new BookingForm($objectManager, $bookingFieldset);
    }
}
