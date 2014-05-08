<?php

namespace JhFlexiTime\Install;


use JhFlexiTime\Entity\UserSettings;
use JhFlexiTime\Repository\UserSettingsRepositoryInterface;
use JhHub\Install\InstallerInterface;
use JhUser\Repository\UserRepositoryInterface;
use Zend\Console\Adapter\AdapterInterface;

class Installer implements InstallerInterface
{
    protected $userRepository;

    protected $userSettingsRepository;

    public function __construct(UserRepositoryInterface $userRepository, UserSettingsRepositoryInterface $userSettingsRepository)
    {
        $this->userRepository = $userRepository;
        $this->userSettingsRepository = $userSettingsRepository;
    }

    /**
     * @param AdapterInterface $console
     * @return void
     */
    public function install(AdapterInterface $console)
    {
        $console->writeLine(sprintf("Hellow from %s", __CLASS__));
        foreach($this->userRepository->findAll() as $user) {
            //try locate user settings
            $userSettings = $this->userSettingsRepository->findOneByUser($user);

            if(!$userSettings) {
                $userSettings = new UserSettings();
                $userSettings
                    ->setUser($user)
                    ->setDefaultStartTime("09:00")
                    ->setDefaultEndTime("17:00")
                    ->setFlexStartDate(new \DateTime);
            }
        }
    }


} 