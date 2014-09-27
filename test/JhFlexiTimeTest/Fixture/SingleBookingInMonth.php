<?php

namespace JhFlexiTimeTest\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Entity\Booking;
use JhUser\Entity\User;
use JhFlexiTime\DateTime\DateTime;

/**
 * Class SingleBookingInMonth
 * @package JhUserTest\Fixture
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SingleBookingInMonth extends AbstractFixture
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
     * @var Booking
     */
    protected $booking;

    /**
     * @param User $user
     * @param DateTime $date
     */
    public function __construct(User $user, DateTime $date)
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

        $booking = new Booking();
        $booking->setUser($this->user);
        $booking->setDate($this->date);
        $manager->persist($booking);

        $manager->flush();

        $this->booking = $booking;
    }

    /**
     * @return Booking
     */
    public function getBooking()
    {
        return $this->booking;
    }
}
