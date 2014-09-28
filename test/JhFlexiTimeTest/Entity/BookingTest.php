<?php

namespace JhFlexiTimeTest\Entity;

use JhFlexiTime\Entity\Booking;
use JhFlexiTime\DateTime\DateTime;

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
        return [
            ['date',       new DateTime("today"),          new DateTime("24 March 2014")],
            ['startTime',  new DateTime("today 09:00"),    new DateTime("24 March 2014 10:00")],
            ['endTime',    new DateTime("today 17:30"),    new DateTime("24 March 2014 18:30")],
        ];
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
        return [
            ['total',      0,  7.5],
            ['balance',    0,  0],
        ];
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
        return [
            ['user',       $this->getMock('ZfcUser\Entity\UserInterface')],
            ['notes',      'Why You even Unit Testing bro?'],
        ];
    }

    public function testJsonSerializeWithModifiedValues()
    {
        $date = new DateTime("24 March 2014");

        $expected = [
            'id'        => $date->getTimestamp() . "-2",
            'date'      => $date->format('d-m-Y'),
            'startTime' => '11:00',
            'endTime'   => '19:30',
            'total'     => 10,
            'balance'   => 2.5,
            'notes'     => 'Just point and click and see if it works. deploy?!',
            'user'      => 2
        ];

        $user = $this->getMock('ZfcUser\Entity\UserInterface');
        $user->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue(2));

        $this->booking
            ->setDate($date)
            ->setStartTime(new DateTime("11:00"))
            ->setEndTime(new DateTime("19:30"))
            ->setTotal(10)
            ->setBalance(2.5)
            ->setNotes('Just point and click and see if it works. deploy?!')
            ->setUser($user);

        $this->assertEquals($expected, $this->booking->jsonSerialize());
    }

    public function testGetIdThrowsExceptionIfUserNotSet()
    {
        $this->setExpectedException('\RuntimeException', 'No User is set. Needed to generate ID');
        $this->booking->getId();
    }

    public function testGetIdContainsUSerIdAndDate()
    {
        $user = $this->getMock('ZfcUser\Entity\UserInterface');
        $user->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));
        $date = new DateTime("24 March 2014");

        $this->booking->setUser($user);
        $this->booking->setDate($date);
        $id     = $this->booking->getId();
        $this->assertEquals($date->getTimestamp() . "-2", $id);
    }
}
