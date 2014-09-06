<?php

namespace JhFlexiTimeTest\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Entity\Booking;
use JhUser\Entity\User;
use JhFlexiTime\DateTime\DateTime;

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
     * @var User[]
     */
    protected $users;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var string[]
     */
    protected $months = [];

    /**
     * @var DateTime
     */
    protected $expectedDate;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
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

        //the user we are looking for
        $this->user = $user;

        $this->users = [
            $user,
        ];
    }

    /**
     * {inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $randomUser1 = new User;
        $randomUser1
            ->setEmail('test1@test.co.uk')
            ->setPassword("password");
        $this->users[] = $randomUser1;

        $randomUser2 = new User;
        $randomUser2
            ->setEmail('test2@test.co.uk')
            ->setPassword("password");
        $this->users[] = $randomUser2;

        foreach ($this->users as $user) {
            $manager->persist($user);
        }
        $manager->flush();


        foreach ($this->dates as $date) {
            foreach ($this->users as $user) {
                $booking = new Booking();
                $booking->setUser($user);
                $manager->persist($booking);
                $booking->setDate($date);

                if ($user === $this->user) {
                    $this->bookingsForUser[] = $booking;
                }
            }
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
