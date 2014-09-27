<?php

namespace JhFlexiTimeTest\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Entity\Booking;
use JhUser\Entity\User;
use JhFlexiTime\DateTime\DateTime;

/**
 * Class BookingsNotInMonth
 * @package JhUserTest\Fixture
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingsNotInMonth extends AbstractFixture
{
    /**
     * @var DateTime[]
     */
    protected $dates;

    /**
     * @var User
     */
    protected $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;

        $this->dates = [
            new DateTime("1 April 2014"),
            new DateTime("2 April 2014"),
            new DateTime("3 April 2014"),
            new DateTime("4 April 2014"),
            new DateTime("5 April 2014"),
            new DateTime("6 May 2014"),
            new DateTime("7 May 2014"),
            new DateTime("8 May 2014"),
            new DateTime("9 May 2014"),
            new DateTime("10 May 2014"),
            new DateTime("1 September 2014"),
            new DateTime("2 September 2014"),
            new DateTime("3 September 2014"),
            new DateTime("4 September 2014"),
            new DateTime("5 September 2014"),
        ];

        $this->monthWithNoBookings = new DateTime("1 October 2014");
    }

    /**
     * {inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->user);
        $manager->flush();

        foreach ($this->dates as $date) {
            $booking = new Booking();
            $booking->setUser($this->user);
            $booking->setDate($date);
            $manager->persist($booking);
        }

        $manager->flush();
    }

    /**
     * @return DateTime
     */
    public function getMonthWithNoBookings()
    {
        return $this->monthWithNoBookings;
    }
}
