<?php

namespace JhFlexiTimeTest\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Entity\Booking;
use JhFlexiTime\Entity\UserSettings;
use JhUser\Entity\User;

/**
 * Class MultiUserMultiMonthBookings
 * @package JhUserTest\Fixture
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MultiUserMultiMonthBookings extends AbstractFixture
{
    /**
     * @var Booking[]
     */
    protected $bookingsForUser = [];

    /**
     * @var User
     */
    protected $user;

    /**
     * @var \DateTime[]
     */
    protected $dates = [];

    /**
     * @var \DateTime
     */
    protected $expectedDate;

    /**
     * @param User $user
     * @param \DateTime $month
     * @throws \InvalidArgumentException
     */
    public function __construct(User $user, \DateTime $month)
    {
        $this->user = $user;

        $this->dates = [
            new \DateTime("February 2014"),
            new \DateTime("March 2014"),
            new \DateTime("April 2014"),
        ];

        foreach ($this->dates as $date) {
            if ($date->format('m') === $month->format('m') &&
                $date->format('y') === $month->format('y')
            ) {
                throw new \InvalidArgumentException("Month must be unique");
            }
        }

        $this->dates[] = $month;
        $this->expectedDate = $month;
    }

    /**
     * {inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->user);
        $manager->flush();

        for ($i = 0; $i < 10; $i++) {
            $booking = new Booking();
            $booking->setUser($this->user);
            $manager->persist($booking);
            $booking->setDate($this->dates[array_rand($this->dates)]);
            $this->bookingsForUser[] = $booking;
        }

        $user = new User;
        $user
            ->setEmail('test1@test.co.uk')
            ->setPassword("password");
        $manager->persist($user);
        $manager->flush();

        for ($i = 0; $i < 10; $i++) {
            $booking = new Booking();
            $booking->setUser($user);
            $booking->setDate($this->dates[array_rand($this->dates)]);
            $manager->persist($booking);
        }

        $manager->flush();
    }

    /**
     * @return Booking[]
     */
    public function getBookingsForUser()
    {
        return $this->bookingsForUser;
    }

    /**
     * @return int
     */
    public function getTotalBookingsForDate()
    {
        return array_reduce($this->bookingsForUser, function ($total, Booking $booking) {
            if ($booking->getDate() === $this->expectedDate) {
                ++$total;
            }
            return $total;
        }, 0);
    }
}
