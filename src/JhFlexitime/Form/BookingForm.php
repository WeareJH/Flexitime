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
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Zend\Form\FieldsetInterface
     */
    protected $bookingFieldset;

    /**
     * @param ObjectManager $objectManager
     * @param FieldsetInterface $bookingFieldset
     */
    public function __construct(ObjectManager $objectManager, FieldsetInterface $bookingFieldset)
    {
        $this->objectManager    = $objectManager;
        $this->bookingFieldset  = $bookingFieldset;
        parent::__construct('new-time-booking-form');
        $this->addElements();
    }

    /**
     * Add elements
     */
    public function addElements()
    {
        $this->setHydrator(new DoctrineHydrator($this->objectManager, 'JhFlexiTime\Entity\Booking'))
            ->setInputFilter(new InputFilter())
            ->setAttribute('method', 'post')
            ->setAttribute('class', 'form-horizontal')
            ->setAttribute('id', 'new-time-booking-form');

        // Add the sample request fieldset
        $bookingFieldset = $this->bookingFieldset;
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
