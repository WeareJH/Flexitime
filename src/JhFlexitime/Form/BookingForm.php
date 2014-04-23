<?php

namespace JhFlexiTime\Form;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Form\FieldsetInterface;

/**
 * Class BookingForm
 * @package JhFlexiTime\Form
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingForm extends Form
{

    /**
     * @param ObjectManager $objectManager
     * @param FieldsetInterface $bookingFieldset
     */
    public function __construct(ObjectManager $objectManager, FieldsetInterface $bookingFieldset)
    {
        parent::__construct('booking-form');

        $this->setHydrator(new DoctrineHydrator($objectManager, 'JhFlexiTime\Entity\Booking'))
            ->setInputFilter(new InputFilter())
            ->setAttribute('method', 'post')
            ->setAttribute('class', 'form-horizontal')
            ->setAttribute('id', 'booking-form');

        // Add the sample request fieldset
        $bookingFieldset->setUseAsBaseFieldset(true);
        $this->add($bookingFieldset);

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Submit',
                'id' => 'submitbutton',
                'class' => 'btn btn-danger',
            )
        ));

    }
}
