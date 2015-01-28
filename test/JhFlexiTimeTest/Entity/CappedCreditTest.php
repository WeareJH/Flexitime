<?php

namespace JhFlexiTimeTest\Entity;

use JhFlexiTime\Entity\Booking;
use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Entity\CappedCredit;
use JhUser\Entity\User;
use PHPUnit_Framework_TestCase;

/**
 * Class CappedCreditTest
 * @package JhFlexiTimeTest\Entity
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CappedCreditTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var CappedCredit
     */
    protected $cappedCredit;

    /**
     * SetUp
     */
    public function setUp()
    {
        $this->cappedCredit = new CappedCredit;
    }

    public function testGetSetDate()
    {
        $this->assertNull($this->cappedCredit->getdate());
        $date = new DateTime('11 November 2013');
        $this->cappedCredit->setDate($date);
        $this->assertSame($date, $this->cappedCredit->getDate());
    }

    /**
     * Test the other setter/getters which have no default values
     *
     * @param        string $name
     * @param        mixed  $value
     *
     * @dataProvider setterGetterProvider
     */
    public function testSetterGetter($name, $value)
    {
        $getMethod = 'get' . ucfirst($name);
        $setMethod = 'set' . ucfirst($name);

        $this->assertNull($this->cappedCredit->$getMethod());
        $this->cappedCredit->$setMethod($value);
        $this->assertSame($value, $this->cappedCredit->$getMethod());
    }

    /**
     * @return array
     */
    public function setterGetterProvider()
    {
        return [
            ['user',            new User],
            ['cappedCredit',    100],
        ];
    }

    public function testGetIdThrowsExceptionIfUserNotSet()
    {
        $this->setExpectedException('\RuntimeException', 'No User is set. Needed to generate ID');
        $this->cappedCredit->getId();
    }

    public function testGetIdContainsUserIdAndDate()
    {
        $user = new User;
        $user->setId(2);
        $date = new DateTime("24 March 2014");

        $this->cappedCredit->setUser($user);
        $this->cappedCredit->setDate($date);
        $id = $this->cappedCredit->getId();
        $this->assertEquals($date->getTimestamp() . "-2", $id);
    }
}
