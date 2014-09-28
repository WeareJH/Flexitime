<?php

namespace JhFlexiTimeTest\Entity;

use JhFlexiTime\Entity\RunningBalance;
use JhUser\Entity\User;
use ReflectionClass;
use DateTime;

class RunningBalanceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var RunningBalance
     */
    protected $runningBalance;

    /**
     * SetUp
     */
    public function setUp()
    {
        $this->runningBalance = new RunningBalance();
    }

    /**
     * @param RunningBalance $runningBalance
     * @param $id
     */
    public function setId(RunningBalance $runningBalance, $id)
    {
        $reflector = new ReflectionClass($runningBalance);
        $property  = $reflector->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($runningBalance, $id);
    }

    public function testId()
    {
        $this->assertNull($this->runningBalance->getId());
        $this->setId($this->runningBalance, 1);
        $this->assertEquals(1, $this->runningBalance->getId());
    }

    public function testSetGetUser()
    {
        $user = $this->getMock('ZfcUser\Entity\UserInterface');

        $this->assertNull($this->runningBalance->getUser());
        $this->runningBalance->setUser($user);
        $this->assertSame($user, $this->runningBalance->getUser());
    }

    public function testSetGetBalance()
    {
        $this->assertEquals(0, $this->runningBalance->getBalance());
        $this->runningBalance->setBalance(10);
        $this->assertSame(10, $this->runningBalance->getBalance());
    }

    /**
     * @param float $originalBalance
     * @param float $addition
     * @param float $expected
     *
     * @dataProvider addBalanceProvider
     */
    public function testAddBalance($originalBalance, $addition, $expected)
    {
        $this->runningBalance->setBalance($originalBalance);
        $this->runningBalance->addBalance($addition);
        $this->assertEquals($expected, $this->runningBalance->getBalance());
    }

    /**
     * @return array
     */
    public function addBalanceProvider()
    {
        return [
            [0,    2,      2],
            [100,  10,     110],
            [230,  -10,    220],
            [-20,  20,     0],
            [-20,  -20,    -40],
        ];
    }

    /**
     * @param float $originalBalance
     * @param float $subtraction
     * @param float $expected
     *
     * @dataProvider subtractBalanceProvider
     */
    public function testSubtractBalance($originalBalance, $subtraction, $expected)
    {
        $this->runningBalance->setBalance($originalBalance);
        $this->runningBalance->subtractBalance($subtraction);
        $this->assertEquals($expected, $this->runningBalance->getBalance());
    }

    /**
     * @return array
     */
    public function subtractBalanceProvider()
    {
        return [
            [0,    2,      -2],
            [100,  10,     90],
            [230,  -10,    240],
            [-20,  20,     -40],
            [-20,  -20,    0],
        ];
    }


    public function testJsonSerializeWithDefaultValuesThrowsException()
    {
        $this->setExpectedException('Exception', 'User Must be an instance of \ZfcUser\Entity\UserInterface');
        $this->runningBalance->jsonSerialize();
    }

    public function testJsonSerializeWithModifiedValues()
    {
        $user = $this->getMock('ZfcUser\Entity\UserInterface');
        $user->expects($this->once())
             ->method('getId')
             ->will($this->returnValue(1));

        $expected = [
            'id'        => 1,
            'user'      => 1,
            'balance'   => 2,
        ];

        $this->setId($this->runningBalance, 1);
        $this->runningBalance
            ->setUser($user)
            ->setBalance(2);

        $this->assertEquals($expected, $this->runningBalance->jsonSerialize());
    }
}
