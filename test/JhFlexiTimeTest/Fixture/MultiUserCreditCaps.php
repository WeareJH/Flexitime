<?php

namespace JhFlexiTimeTest\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Entity\Booking;
use JhFlexiTime\Entity\CappedCredit;
use JhUser\Entity\User;
use JhFlexiTime\DateTime\DateTime;

/**
 * Class MultiUserCreditCaps
 * @package JhUserTest\Fixture
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MultiUserCreditCaps extends AbstractFixture
{
    /**
     * @var User
     */
    protected $user1;

    /**
     * @var User
     */
    protected $user2;

    /**
     * @var array
     */
    protected $userRecords = [];

    /**
     * @param User $user1
     */
    public function __construct(User $user1)
    {
        $this->user1 = $user1;
        $this->user2 = new User;
        $this->user2->setEmail('test1@test.co.uk')->setPassword("password");
    }

    /**
     * {inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->user1);
        $manager->persist($this->user2);
        $manager->flush();

        $dateFormat = "1-%s-2014";
        for ($i = 1; $i <= 10; $i++) {
            $date = new DateTime(sprintf($dateFormat, $i));

            $creditCap1 = new CappedCredit;
            $creditCap1->setUser($this->user1);
            $creditCap1->setDate($date);
            $creditCap1->setCappedCredit(10);

            $this->userRecords[] = $creditCap1;

            $creditCap2 = new CappedCredit;
            $creditCap2->setUser($this->user2);
            $creditCap2->setDate($date);
            $creditCap2->setCappedCredit(10);

            $manager->persist($creditCap1);
            $manager->persist($creditCap2);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    public function getUserRecords()
    {
        return $this->userRecords;
    }

    /**
     * @return User
     */
    public function getUser2()
    {
        return $this->user2;
    }
}
