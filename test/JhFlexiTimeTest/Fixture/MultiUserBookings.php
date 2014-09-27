<?php

namespace JhFlexiTimeTest\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Entity\Booking;
use JhUser\Entity\User;
use JhFlexiTime\DateTime\DateTime;

/**
 * Class MultiUserBookings
 * @package JhUserTest\Fixture
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MultiUserBookings extends AbstractFixture
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
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * {inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->user);
        $manager->flush();

        $user = new User;
        $user
            ->setEmail('test1@test.co.uk')
            ->setPassword("password");
        $manager->persist($user);
        $manager->flush();

        $dateFormat = "%s September 2014";
        for ($i = 1; $i <= 10; $i++) {
            $date = new DateTime(sprintf($dateFormat, $i));

            $booking = new Booking();
            $booking->setDate($date);
            $booking->setUser($this->user);
            $manager->persist($booking);
            $this->bookingsForUser[] = $booking;

            $booking = new Booking();
            $booking->setDate($date);
            $booking->setUser($user);
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
}
