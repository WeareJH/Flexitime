<?php

namespace JhFlexiTime\Form\Fieldset;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use JhFlexiTime\Entity\Booking;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class BookingFieldset
 * @package JhFlexiTime\Form\Fieldset
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingFieldset extends Fieldset implements InputFilterProviderInterface
{

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
        parent::__construct('booking');
        $this->addElements();
    }

    /**
     * Add elements
     */
    public function addElements()
    {

        $this->setHydrator(new DoctrineHydrator($this->objectManager, 'JhFlexiTime\Entity\Booking'))
            ->setObject(new Booking());

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id',
            'attributes' => array(
                'id' => 'book-id',
            )
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'date',
            'options' => array(
                'label' => 'Date',
                'label_attributes' => array(
                    'class' => 'col-sm-4 control-label',
                ),
            ),
            'attributes' => array(
                'id'        => 'book-date',
                'step'      => '1',
                'required'  => 'required',
                'class'     => 'form-control input-sm',
                //'value'     => new \DateTime(),
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Time',
            'name'    => 'startTime',
            'options' => array(
                'label' => 'Start Time',
                'label_attributes' => array(
                    'class' => 'col-sm-4 control-label',
                ),
            ),
            'attributes' => array(
                'id'    => 'book-starttime',
                'min'   => '07:00:00',
                'max'   => '10:00:00',
                'step'  => '900',   //15 mins, 60 x 15
                'class' => 'form-control input-sm',
                'value' => '07:00',
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Time',
            'name'    => 'endTime',
            'options' => array(
                'label' => 'End Time',
                'label_attributes' => array(
                    'class' => 'col-sm-4 control-label',
                ),
            ),
            'attributes' => array(
                'id'    => 'book-endtime',
                'min'   => '16:00:00',
                'max'   => '19:00:00',
                'step'  => '900',   //15 mins, 60 x 15
                'class' => 'form-control input-sm',
                'value' => '16:00',
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Textarea',
            'name'    => 'notes',
            'options' => array(
                'label' => 'Notes',
                'label_attributes' => array(
                    'class' => 'col-sm-4 control-label',
                ),
            ),
            'attributes' => array(
                'id'    => 'book-notes',
                'class' => 'form-control input-sm',
            )
        ));
    }

    /**
     * Input Specification
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array(
            /*'date' => array(
                'validators' => array(
                    array(
                        'name' => 'JhFlexiTime\Validator\DoctrineUniqueObject',
                        'options' => array(
                            'user'              => $this->getUser(),
                            'object_repository' => $this->getEntityManager()->getRepository('JhFlexiTime\Entity\Booking'),
                            'object_manager'    => $this->getEntityManager(),
                            'fields' => array('date', 'user'),
                            'messages' => array(
                                'objectNotUnique' => 'Sorry, You have already booked time for this day, Try editing it instead'
                            ),
                        ),
                    ),
                )
            ),*/

            'notes' => array(
                'required'  => false,
                'filters'   => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 512,
                        ),
                    ),
                ),
            ),
        );
    }
}