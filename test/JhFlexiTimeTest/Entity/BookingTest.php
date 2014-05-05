<?php

namespace JhFlexiTimeTest\Entity;

use JhFlexiTime\Entity\Booking;
use ReflectionClass;
use DateTime;

class BookingTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Booking
     */
    protected $booking;

    /**
     * SetUp
     */
    public function setUp()
    {
        $this->booking = new Booking;
    }

    /**
     * @param Booking $booking
     * @param $id
     */
    public function setId(Booking $booking, $id)
    {
        $reflector = new ReflectionClass($booking);
        $property  = $reflector->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($booking, $id);
    }

    public function testId()
    {
        $this->assertNull($this->booking->getId());
        $this->setId($this->booking, 1);
        $this->assertEquals(1, $this->booking->getId());
    }

    /**
     * Test The Date/Time setter/getters and default values
     *
     * @param string $name
     * @param DateTime $default
     * @param DateTime $newValue
     *
     * @dataProvider dateSetterGetterProvider
     */
    public function testDateSetterGetter($name, $default, $newValue)
    {
        $getMethod = 'get' . ucfirst($name);
        $setMethod = 'set' . ucfirst($name);

        $this->assertInstanceOf('DateTime', $this->booking->$getMethod());
        $this->assertEquals($default, $this->booking->$getMethod());

        $this->booking->$setMethod($newValue);
        $this->assertSame($newValue, $this->booking->$getMethod());
    }

    /**
     * @return array
     */
    public function dateSetterGetterProvider()
    {
        return array(
            array('date',       new DateTime("today"),          new DateTime("24 March 2014")),
            array('startTime',  new DateTime("today 09:00"),    new DateTime("24 March 2014 10:00")),
            array('endTime',    new DateTime("today 17:30"),    new DateTime("24 March 2014 18:30")),
        );
    }

    /**
     * test the Number setter/getters and default values
     *
     * @param $name
     * @param $default
     * @param $newValue
     *
     * @dataProvider numberSetterGetterProvider
     */
    public function testNumberSetterGetter($name, $default, $newValue)
    {
        $getMethod = 'get' . ucfirst($name);
        $setMethod = 'set' . ucfirst($name);

        $this->assertEquals($default, $this->booking->$getMethod());

        $this->booking->$setMethod($newValue);
        $this->assertEquals($newValue, $this->booking->$getMethod());
    }

    /**
     * @return array
     */
    public function numberSetterGetterProvider()
    {
        return array(
            array('total',      0,  7.5),
            array('balance',    0,  0),
        );
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

        $this->assertNull($this->booking->$getMethod());
        $this->booking->$setMethod($value);
        $this->assertSame($value, $this->booking->$getMethod());
    }

    /**
     * @return array
     */
    public function setterGetterProvider()
    {
        return array(
            array('user',       $this->getMock('ZfcUser\Entity\UserInterface')),
            array('notes',      'Why You even Unit Testing bro?'),
        );
    }

    public function testJsonSerializeWithDefaultValues()
    {
        $date = new DateTime("today");

        $expected = array(
            'id'        => null,
            'date'      => $date->format('d-m-Y'),
            'startTime' => '09:00',
            'endTime'   => '17:30',
            'total'     => 0,
            'balance'   => 0,
            'notes'     => null
        );

        $this->assertEquals($expected, $this->booking->jsonSerialize());
    }

    public function testJsonSerializeWithModifiedValues()
    {
        $date = new DateTime("24 March 2014");

        $expected = array(
            'id'        => 1,
            'date'      => $date->format('d-m-Y'),
            'startTime' => '11:00',
            'endTime'   => '19:30',
            'total'     => 10,
            'balance'   => 2.5,
            'notes'     => 'Just point and click and see if it works. deploy?!'
        );

        $this->setId($this->booking, 1);
        $this->booking
            ->setDate($date)
            ->setStartTime(new DateTime("11:00"))
            ->setEndTime(new DateTime("19:30"))
            ->setTotal(10)
            ->setBalance(2.5)
            ->setNotes('Just point and click and see if it works. deploy?!');

        $this->assertEquals($expected, $this->booking->jsonSerialize());
    }
}
