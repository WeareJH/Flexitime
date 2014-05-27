<?php

namespace JhFlexiTimeTest\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Entity\RunningBalance;
use JhUser\Entity\User;

/**
 * Class SingleRunningBalance
 * @package JhUserTest\Fixture
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SingleRunningBalance extends AbstractFixture
{
    /**
     * @var RunningBalance
     */
    protected $runningBalance;

    /**
     * {inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->runningBalance = new RunningBalance();

        $user = new User();
        $user->setEmail("aydin@hotmail.co.uk")
             ->setPassword('password');
        $this->runningBalance
            ->setUser($user)
            ->setBalance('10');

        $manager->persist($user);
        $manager->persist($this->runningBalance);
        $manager->flush();
    }

    /**
     * @return RunningBalance
     */
    public function getBalance()
    {
        return $this->runningBalance;
    }
}
