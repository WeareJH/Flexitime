<?php

namespace JhFlexiTimeTest\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Entity\BookedCredit;
use JhFlexiTime\Entity\BookedCreditType;
use JhFlexiTime\Entity\Booking;
use JhFlexiTime\Entity\CappedCredit;
use JhUser\Entity\User;
use JhFlexiTime\DateTime\DateTime;

/**
 * Class MultiUserBookedCredit
 * @package JhUserTest\Fixture
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MultiUserBookedCredit extends AbstractFixture
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

        $bookingType = new BookedCreditType;
        $bookingType->setLabel('Overtime');
        $bookingType->setShortname('ot');
        $manager->persist($bookingType);

        $dateFormat = "1-%s-2014";
        for ($i = 1; $i <= 10; $i++) {
            $date = new DateTime(sprintf($dateFormat, $i));

            $bookedCredit1 = new BookedCredit;
            $bookedCredit1->setUser($this->user1);
            $bookedCredit1->setDate($date);
            $bookedCredit1->setAmount(10);
            $bookedCredit1->setType($bookingType);

            $this->userRecords[] = $bookedCredit1;

            $bookedCredit2 = new BookedCredit;
            $bookedCredit2->setUser($this->user2);
            $bookedCredit2->setDate($date);
            $bookedCredit2->setAmount(10);
            $bookedCredit2->setType($bookingType);


            $manager->persist($bookedCredit1);
            $manager->persist($bookedCredit2);
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
