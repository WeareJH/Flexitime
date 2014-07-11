<?php

namespace JhFlexiTime\Install;

use Doctrine\DBAL\DBALException;
use JhFlexiTime\Entity\RunningBalance;
use JhFlexiTime\Entity\UserSettings;
use JhFlexiTime\Repository\UserSettingsRepositoryInterface;
use JhFlexiTime\Repository\BalanceRepositoryInterface;
use JhUser\Repository\UserRepositoryInterface;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Doctrine\Common\Persistence\ObjectManager;
use JhInstaller\Install\Exception as InstallException;
use JhInstaller\Install\InstallerInterface;
use ZfcUser\Entity\UserInterface;

/**
 * Class Installer
 * @package JhFlexiTime\Install
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Installer implements InstallerInterface
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var UserSettingsRepositoryInterface
     */
    protected $userSettingsRepository;

    /**
     * @var BalanceRepositoryInterface
     */
    protected $balanceRepository;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param UserRepositoryInterface $userRepository
     * @param UserSettingsRepositoryInterface $userSettingsRepository
     * @param BalanceRepositoryInterface $balanceRepository
     * @param ObjectManager $objectManager
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        UserSettingsRepositoryInterface $userSettingsRepository,
        BalanceRepositoryInterface $balanceRepository,
        ObjectManager $objectManager
    ) {
        $this->userRepository           = $userRepository;
        $this->userSettingsRepository   = $userSettingsRepository;
        $this->balanceRepository        = $balanceRepository;
        $this->objectManager            = $objectManager;
    }

    /**
     * @param AdapterInterface $console
     * @throws \JhInstaller\Install\Exception
     */
    public function install(AdapterInterface $console)
    {

        foreach ($this->userRepository->findAll() as $user) {
            $this->createSettingsRow($user, $console);
            $this->createRunningBalanceRow($user, $console);
        }

        $this->objectManager->flush();

    }

    /**
     * @param UserInterface $user
     * @param AdapterInterface $console
     * @throws \JhInstaller\Install\Exception
     */
    public function createSettingsRow(UserInterface $user, AdapterInterface $console = null)
    {
        if ($console) {
            $console->writeLine(
                sprintf("Checking if user '%s' has a user_flex_settings row..", $user->getEmail()),
                Color::YELLOW
            );
        }

        try {
            $userSettings = $this->userSettingsRepository->findOneByUser($user);
        } catch (DBALException $e) {
            $this->errors[] = sprintf(
                "The Database table has not been created. Be sure to run the schema tool. Message: %s",
                $e->getMessage()
            );
            throw new InstallException();
        }

        if (!$userSettings) {
            if ($console) {
                $console->writeLine("Settings row does not exist. Creating... ", Color::YELLOW);
            }
            $userSettings = new UserSettings();
            $userSettings
                ->setUser($user)
                ->setDefaultStartTime(new \DateTime("09:00"))
                ->setDefaultEndTime(new \DateTime("17:00"))
                ->setFlexStartDate(new \DateTime);

            $this->objectManager->persist($userSettings);

        } else {
            if ($console) {
                $console->writeLine("Settings row exists. Skipping", Color::YELLOW);
            }
        }
    }

    /**
     * @param UserInterface $user
     * @param AdapterInterface $console
     * @throws \JhInstaller\Install\Exception
     */
    public function createRunningBalanceRow(UserInterface $user, AdapterInterface $console = null)
    {

        if ($console) {
            $console->writeLine(
                sprintf("Checking if user '%s' has a running_balance row..", $user->getEmail()),
                Color::YELLOW
            );
        }

        try {
            $runningBalance = $this->balanceRepository->findOneByUser($user);
        } catch (DBALException $e) {
            $this->errors[] = sprintf(
                "The Database table has not been created. Be sure to run the schema tool. Message: %s",
                $e->getMessage()
            );
            throw new InstallException();
        }

        if (!$runningBalance) {

            if ($console) {
                $console->writeLine("Running Balance row does not exist. Creating... ", Color::YELLOW);
            }
            $runningBalance = new RunningBalance();
            $runningBalance->setUser($user);

            $this->objectManager->persist($runningBalance);

        } else {
            if ($console) {
                $console->writeLine("Running Balance row exists. Skipping", Color::YELLOW);
            }
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
