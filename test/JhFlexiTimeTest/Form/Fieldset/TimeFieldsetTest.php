<?php

namespace JhFlexiTimeTest\Form\Fieldset;

use JhFlexiTime\Form\Fieldset\BookingFieldset;

/**
 * Class BookingFieldsetTest
 * @package JhFlexiTimeTest\Form\Fieldset
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingFieldsetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test elements exist
     */
    public function testFieldsetHasAllElements()
    {
        $objectManager  = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $user           = $this->getMock('ZfcUser\Entity\UserInterface');
        $fieldset = new BookingFieldset($objectManager, $user);

        $this->assertTrue($fieldset->has("id"));
        $this->assertTrue($fieldset->has("date"));
        $this->assertTrue($fieldset->has("startTime"));
        $this->assertTrue($fieldset->has("endTime"));
        $this->assertTrue($fieldset->has("notes"));
        $this->assertTrue($fieldset->has("user"));
    }
}
