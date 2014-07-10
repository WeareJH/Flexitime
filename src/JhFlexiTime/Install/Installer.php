<?php

namespace JhFlexiTime\Install;

use Doctrine\DBAL\DBALException;
use JhFlexiTime\Entity\UserSettings;
use JhFlexiTime\Repository\UserSettingsRepositoryInterface;
use JhUser\Repository\UserRepositoryInterface;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface as Color;
use Doctrine\Common\Persistence\ObjectManager;
use JhInstaller\Install\Exception as InstallException;
use JhInstaller\Install\InstallerInterface;

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
     * @param ObjectManager $objectManager
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        UserSettingsRepositoryInterface $userSettingsRepository,
        ObjectManager $objectManager
    ) {
        $this->userRepository           = $userRepository;
        $this->userSettingsRepository   = $userSettingsRepository;
        $this->objectManager            = $objectManager;
    }

    /**
     * @param AdapterInterface $console
     * @throws \JhInstaller\Install\Exception
     */
    public function install(AdapterInterface $console)
    {

        foreach ($this->userRepository->findAll() as $user) {
            $console->writeLine(
                sprintf("Checking if user '%s' has a user_flex_settings row..", $user->getEmail()),
                Color::YELLOW
            );
            //try locate user settings

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
                $console->writeLine("Row does not exist. Creating... ", Color::YELLOW);
                $userSettings = new UserSettings();
                $userSettings
                    ->setUser($user)
                    ->setDefaultStartTime(new \DateTime("09:00"))
                    ->setDefaultEndTime(new \DateTime("17:00"))
                    ->setFlexStartDate(new \DateTime);

                $this->objectManager->persist($userSettings);

            } else {
                $console->writeLine("Row exists. Skipping", Color::YELLOW);
            }
        }

        $this->objectManager->flush();

    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
