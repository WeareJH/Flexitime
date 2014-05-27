<?php

namespace JhFlexiTimeTest\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Entity\UserSettings;
use JhUser\Entity\User;

/**
 * Class SingleSettings
 * @package JhUserTest\Fixture
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SingleSettings extends AbstractFixture
{
    /**
     * @var UserSettings
     */
    protected $settings;

    /**
     * {inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->settings = new UserSettings();

        $user = new User();
        $user->setEmail("aydin@hotmail.co.uk")
             ->setPassword('password');

        $this->settings
            ->setFlexStartDate(new \DateTime("today"))
            ->setDefaultStartTime(new \DateTime("9:00"))
            ->setDefaultEndTime(new \DateTime("17:00"))
            ->setUser($user);

        $manager->persist($user);
        $manager->flush();
        $manager->persist($this->settings);
        $manager->flush();
    }

    /**
     * @return UserSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
