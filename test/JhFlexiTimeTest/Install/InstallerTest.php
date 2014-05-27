<?php

namespace JhFlexiTimeTest\Install;

use JhFlexiTime\Install\Installer;
use ZfcUser\Entity\User;
use JhFlexiTime\Entity\UserSettings;
use JhFlexiTime\Entity\RunningBalance;

/**
 * Class InstallerTest
 * @package JhFlexiTimeTest\Install\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class InstallerTest extends \PHPUnit_Framework_TestCase
{
    protected $installer;
    protected $userRepository;
    protected $userSettingsRepository;
    protected $balanceRepository;
    protected $objectManager;
    protected $console;


    public function setUp()
    {
        $this->userRepository = $this->getMock('Jhuser\Repository\UserRepositoryInterface');
        $this->userSettingsRepository = $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface');
        $this->balanceRepository = $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface');
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->console = $this->getMock('Zend\Console\Adapter\AdapterInterface');

        $this->installer = new Installer(
            $this->userRepository,
            $this->userSettingsRepository,
            $this->balanceRepository,
            $this->objectManager
        );
    }

    public function testInstallerCallsAllCreateFunctionsForEachUser()
    {
        $installer = $this->getMock(
            'JhFlexiTime\Install\Installer',
            ['createSettingsRow', 'createRunningBalanceRow'],
            [
                $this->userRepository,
                $this->userSettingsRepository,
                $this->balanceRepository,
                $this->objectManager
            ]
        );

        $users = $this->getUsers();

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($users));

        $installer
            ->expects($this->at(0))
            ->method('createSettingsRow')
            ->with($users[0], $this->console);

        $installer
            ->expects($this->at(1))
            ->method('createRunningBalanceRow')
            ->with($users[0], $this->console);

        $installer
            ->expects($this->at(2))
            ->method('createSettingsRow')
            ->with($users[1], $this->console);

        $installer
            ->expects($this->at(3))
            ->method('createRunningBalanceRow')
            ->with($users[1], $this->console);

        $this->objectManager
            ->expects($this->once())
            ->method('flush');

        $installer->install($this->console);

    }

    public function testUserSettingsRowCreationIsSkippedIfRowExists()
    {
        $user = new User();
        $user->setEmail("test1@test.com");

        $userSettings = new UserSettings();

        $this->console
            ->expects($this->at(0))
            ->method('writeLine')
            ->with("Checking if user 'test1@test.com' has a user_flex_settings row..", 4);

        $this->userSettingsRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($user)
            ->will($this->returnValue($userSettings));

        $this->console
             ->expects($this->at(1))
             ->method('writeLine')
             ->with('Settings row exists. Skipping', 4);

        $this->installer->createSettingsRow($user, $this->console);
    }

    public function testUserSettingsRowIsCreatedIfNotExist()
    {
        $user = new User();
        $user->setEmail("test1@test.com");

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
            ->with("Settings row does not exist. Creating... ", 4);

        $this->objectManager
             ->expects($this->once())
             ->method('persist')
             ->with($this->isInstanceOf('JhFlexiTime\Entity\UserSettings'));

        $this->installer->createSettingsRow($user, $this->console);
    }

    public function testExceptionIsThrownIfUserFlexSettingsTableDoesNotExist()
    {
        $user = new User();
        $user->setEmail("test1@test.com");

        $e = new \Doctrine\DBAL\DBALException("Some Message");

        $this->console
            ->expects($this->at(0))
            ->method('writeLine')
            ->with("Checking if user 'test1@test.com' has a user_flex_settings row..", 4);

        $this->userSettingsRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($user)
            ->will($this->throwException($e));

        $this->setExpectedException('JhInstaller\Install\Exception');
        $this->installer->createSettingsRow($user, $this->console);

        $errors = [
            "The Database table has not been created. Be sure to run the schema tool. Message: Some Message"
        ];

        $this->assertEquals($errors, $this->installer->getErrors());
    }

    public function testRunningBalanceRowCreationIsSkippedIfRowExists()
    {
        $user = new User();
        $user->setEmail("test1@test.com");

        $balance = new RunningBalance();

        $this->console
            ->expects($this->at(0))
            ->method('writeLine')
            ->with("Checking if user 'test1@test.com' has a running_balance row..", 4);

        $this->balanceRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($user)
            ->will($this->returnValue($balance));

        $this->console
            ->expects($this->at(1))
            ->method('writeLine')
            ->with('Running Balance row exists. Skipping', 4);

        $this->installer->createRunningBalanceRow($user, $this->console);
    }

    public function testRunningBalanceRowIsCreatedIfNotExist()
    {
        $user = new User();
        $user->setEmail("test1@test.com");

        $this->console
            ->expects($this->at(0))
            ->method('writeLine')
            ->with("Checking if user 'test1@test.com' has a running_balance row..", 4);

        $this->balanceRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($user)
            ->will($this->returnValue(null));

        $this->console
            ->expects($this->at(1))
            ->method('writeLine')
            ->with("Running Balance row does not exist. Creating... ", 4);

        $this->objectManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('JhFlexiTime\Entity\RunningBalance'));

        $this->installer->createRunningBalanceRow($user, $this->console);
    }

    public function testExceptionIsThrownIfRunningBalanceTableDoesNotExist()
    {
        $user = new User();
        $user->setEmail("test1@test.com");

        $e = new \Doctrine\DBAL\DBALException("Some Message");

        $this->console
            ->expects($this->at(0))
            ->method('writeLine')
            ->with("Checking if user 'test1@test.com' has a running_balance row..", 4);

        $this->balanceRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($user)
            ->will($this->throwException($e));

        $this->setExpectedException('JhInstaller\Install\Exception');
        $this->installer->createRunningBalanceRow($user, $this->console);

        $errors = [
            "The Database table has not been created. Be sure to run the schema tool. Message: Some Message"
        ];

        $this->assertEquals($errors, $this->installer->getErrors());
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
