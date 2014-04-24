<?php

namespace JhFlexiTimeTest\Form;

use JhFlexiTime\Form\BookingForm;

/**
 * Class BookingFormTest
 * @package JhFlexiTimeTest\Form
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Form Elements
     */
    public function testFormElements()
    {
        $objectManager  = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $fieldset = $this->getMockBuilder('\JhFlexiTime\Form\Fieldset\BookingFieldset')
            ->setMethods(array('__construct', 'getName'))
            ->setConstructorArgs(array($objectManager))
            ->getMock();

        $fieldset->expects($this->once())
                 ->method('getName')
                 ->will($this->returnValue('time'));


        $form = new BookingForm($objectManager, $fieldset);

        $this->assertTrue($form->has("time"));
        $this->assertTrue($form->has("submit"));
    }
} 
