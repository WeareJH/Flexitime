<?php

namespace JhFlexiTimeTest\Entity;

use JhFlexiTime\Entity\UserSettings;
use JhUser\Entity\User;
use ReflectionClass;
use DateTime;

class UserSettingsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var UserSettings
     */
    protected $userSettings;

    /**
     * SetUp
     */
    public function setUp()
    {
        $this->userSettings = new UserSettings();
    }

    public function testSetGetUser()
    {
        $user = $this->getMock('ZfcUser\Entity\UserInterface');

        $this->assertNull($this->userSettings->getUser());
        $this->userSettings->setUser($user);
        $this->assertSame($user, $this->userSettings->getUser());
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

        $this->assertEquals($default, $this->userSettings->$getMethod());

        $this->userSettings->$setMethod($newValue);
        $this->assertSame($newValue, $this->userSettings->$getMethod());
        $this->assertInstanceOf('DateTime', $this->userSettings->$getMethod());
    }

    /**
     * @return array
     */
    public function dateSetterGetterProvider()
    {
        return [
            ['flexStartDate',     null,    new DateTime("24 March 2014")],
            ['defaultStartTime',  null,    new DateTime("10:00")],
            ['defaultEndTime',    null,    new DateTime("18:30")],
        ];
    }

    public function testGetSetStartingBalance()
    {
        $this->assertEquals(0, $this->userSettings->getStartingBalance());
        $this->userSettings->setStartingBalance(5.5);
        $this->assertEquals(5.5, $this->userSettings->getStartingBalance());
    }

    public function testJsonSerialize()
    {
        $this->assertEquals([], $this->userSettings->jsonSerialize());
    }
}
