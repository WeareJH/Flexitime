<?php

namespace JhFlexiTimeTest\Install;

use JhFlexiTime\Entity\UserSettings;
use JhFlexiTime\Install\Installer;

/**
 * Class InstallerTest
 * @package JhFlexiTimeTest\Install\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
use JhUser\Entity\User;class InstallerTest extends \PHPUnit_Framework_TestCase
{
    protected $installer;
    protected $userRepository;
    protected $userSettingsRepository;
    protected $objectManager;
    protected $console;


    public function setUp()
    {
        $this->userRepository = $this->getMock('Jhuser\Repository\UserRepositoryInterface');
        $this->userSettingsRepository = $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface');
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->console = $this->getMock('Zend\Console\Adapter\AdapterInterface');

        $this->installer = new Installer(
            $this->userRepository,
            $this->userSettingsRepository,
            $this->objectManager
        );
    }

    public function testExceptionIsThrownIfUserFlexSettingsTableDoesNotExist()
    {
        $users = $this->getUsers();

        $this->userRepository
             ->expects($this->once())
             ->method('findAll')
             ->with(false)
             ->will($this->returnValue($users));

        $e = new \Doctrine\DBAL\DBALException("Some Message");

        $this->console
             ->expects($this->at(0))
             ->method('writeLine')
             ->with("Checking if user 'test1@test.com' has a user_flex_settings row..", 4);

        $this->userSettingsRepository
             ->expects($this->once())
             ->method('findOneByUser')
             ->with($users[0])
             ->will($this->throwException($e));

        $this->setExpectedException('JhInstaller\Install\Exception');
        $this->installer->install($this->console);

        $errors = [
            "The Database table has not been created. Be sure to run the schema tool. Message: Some Message"
        ];

        $this->assertEquals($errors, $this->installer->getErrors());
    }

    public function testUserSettingsRowCreationIsSkippedIfRowExists()
    {
        $users = $this->getUsers();

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(false)
            ->will($this->returnValue($users));

        $userSettings = new UserSettings();

        $this->console
            ->expects($this->at(0))
            ->method('writeLine')
            ->with("Checking if user 'test1@test.com' has a user_flex_settings row..", 4);

        $this->console
            ->expects($this->at(2))
            ->method('writeLine')
            ->with("Checking if user 'test2@test.com' has a user_flex_settings row..", 4);

        $this->userSettingsRepository
            ->expects($this->at(0))
            ->method('findOneByUser')
            ->with($users[0])
            ->will($this->returnValue($userSettings));

        $this->userSettingsRepository
            ->expects($this->at(1))
            ->method('findOneByUser')
            ->with($users[1])
            ->will($this->returnValue($userSettings));

        $this->console
             ->expects($this->at(1))
             ->method('writeLine')
             ->with('Row exists. Skipping', 4);

        $this->console
            ->expects($this->at(3))
            ->method('writeLine')
            ->with('Row exists. Skipping', 4);

        $this->installer->install($this->console);
    }

    public function testUserSettingsRowIsCreatedIfNotExist()
    {
        $user = new User();
        $user->setEmail("test1@test.com");

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(false)
            ->will($this->returnValue([$user]));

        $this->console
            ->expects($this->at(0))
            ->method('writeLine')
            ->with("Checking if user 'test1@test.com' has a user_flex_settings row..", 4);

        $this->userSettingsRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($user)
            ->will($this->returnValue(null));

        $this->console
            ->expects($this->at(1))
            ->method('writeLine')
            ->with("Row does not exist. Creating... ", 4);

        $this->objectManager
             ->expects($this->once())
             ->method('persist')
             ->with($this->isInstanceOf('JhFlexiTime\Entity\UserSettings'));

        $this->objectManager
            ->expects($this->once())
            ->method('flush');

        $this->installer->install($this->console);
    }

    public function testGetErrorsReturnsEmptyArray()
    {
        $errors = $this->installer->getErrors();
        $this->assertEquals([], $errors);
    }

    public function getUsers()
    {
        $user1 = new User();
        $user2 = new User();

        $user1->setEmail("test1@test.com");
        $user2->setEmail("test2@test.com");

        return [$user1, $user2];
    }


}
