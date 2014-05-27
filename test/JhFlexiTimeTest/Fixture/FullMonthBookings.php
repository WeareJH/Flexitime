<?php

namespace JhFlexiTimeTest\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Entity\Booking;
use JhFlexiTime\Entity\UserSettings;
use JhUser\Entity\User;

/**
 * Class FullMonthBookings
 * @package JhUserTest\Fixture
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FullMonthBookings extends AbstractFixture
{
    /**
     * @var \DateTime[]
     */
    protected $dates;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Booking[]
     */
    protected $bookings;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->dates = [
            new \DateTime("1 May 2014"),
            new \DateTime("2 May 2014"),
            new \DateTime("3 May 2014"),
            new \DateTime("4 May 2014"),
            new \DateTime("5 May 2014"),
            new \DateTime("6 May 2014"),
            new \DateTime("7 May 2014"),
            new \DateTime("8 May 2014"),
            new \DateTime("9 May 2014"),
            new \DateTime("10 May 2014"),
            new \DateTime("11 May 2014"),
            new \DateTime("12 May 2014"),
            new \DateTime("13 May 2014"),
            new \DateTime("14 May 2014"),
            new \DateTime("15 May 2014"),
            new \DateTime("16 May 2014"),
            new \DateTime("17 May 2014"),
            new \DateTime("18 May 2014"),
            new \DateTime("19 May 2014"),
            new \DateTime("20 May 2014"),
            new \DateTime("21 May 2014"),
            new \DateTime("22 May 2014"),
            new \DateTime("23 May 2014"),
            new \DateTime("24 May 2014"),
            new \DateTime("25 May 2014"),
            new \DateTime("26 May 2014"),
            new \DateTime("27 May 2014"),
            new \DateTime("28 May 2014"),
            new \DateTime("30 May 2014"),
            new \DateTime("31 May 2014"),
        ];
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
            $booking->setTotal(rand(0, 20));
            $booking->setDate($date);
            $manager->persist($booking);
            $this->bookings[] = $booking;
        }

        $manager->flush();
    }

    /**
     * @return \DateTime[]
     */
    public function getDates()
    {
        return $this->dates;
    }

    /**
     * @return \DateTime
     */
    public function getMonth()
    {
        return new \DateTime("May 2014");
    }

    /**
     * Bookings[]
     */
    public function getBookings()
    {
        return $this->bookings;
    }
}
