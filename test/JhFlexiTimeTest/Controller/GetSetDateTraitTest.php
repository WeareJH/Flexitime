<?php

namespace JhFlexiTimeTest\Controller;

use JhFlexiTime\DateTime\DateTime;

class GetSetDateTraitTest extends \PHPUnit_Framework_TestCase
{
    protected $traitObject;

    public function setUp()
    {
        $this->traitObject = $this->getObjectForTrait('JhFlexiTime\Controller\GetSetDateTrait');
    }

    /**
     * @dataProvider invalidDateParamsProvider
     */
    public function testGetDateReturnsCurrentDateTmeObjectWhenParamsInvalid($month, $year, $notExpected)
    {
        $date = $this->traitObject->getDate($month, $year);
        $this->assertNotEquals($date, $notExpected);
        $this->assertInstanceOf('\DateTime', $date);

        $today = new \DateTime;
        $this->assertEquals($date->format('d-m-Y'), $today->format('d-m-Y'));
    }

    public function invalidDateParamsProvider()
    {
        return [
            ["not-a-month", 20145, new \DateTime("last day of March 2014 23:59:59")],
            ["Apr", '1988v', new \DateTime("last day of April 1988 23:59:59")],
        ];
    }

    /**
     * @dataProvider validDateParamsProvider
     */
    public function testGetDateReturnsDateTimeObjectBasedOnParams($month, $year, $expected)
    {
        $this->assertEquals($expected, $this->traitObject->getDate($month, $year));
    }

    public function validDateParamsProvider()
    {
        return [
            ["Mar", 2014, new \DateTime("last day of March 2014 23:59:59")],
            ["Apr", 1988, new \DateTime("last day of April 1988 23:59:59")],
        ];
    }

    public function testGetDateReturnsCurrentlySetObjectIfSet()
    {
        $date = new DateTime();
        $this->traitObject->setDate($date);
        $this->assertEquals($date, $this->traitObject->getDate());
        $this->assertEquals($date, $this->traitObject->getDate("Mar", 2014));
    }
}
