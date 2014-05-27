<?php

namespace JhFlexiTimeTest\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Entity\Booking;
use JhFlexiTime\Entity\UserSettings;
use JhUser\Entity\User;

/**
 * Class BookingsNotInMonth
 * @package JhUserTest\Fixture
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingsNotInMonth extends AbstractFixture
{
    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var User
     */
    protected $user;

    /**
     * @param User $user
     * @param \DateTime $date
     */
    public function __construct(User $user, \DateTime $date)
    {
        $this->user = $user;
        $this->date = $date;
    }

    /**
     * {inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->user);
        $manager->flush();

        $ops = ["+", "-"];
        for ($i = 0; $i < 10; $i++) {
            $booking = new Booking();

            $date = clone $this->date;

            //create date which is between +1 and +10 months in future
            //or -1 and -10 months in past
            $date->modify(sprintf("%s %s month", $ops[array_rand($ops)], rand(1, 10)));

            $booking->setUser($this->user);
            $booking->setDate($date);
            $manager->persist($booking);
        }

        $manager->flush();
    }
}
