<?php

namespace JhFlexiTime\Service;

use JhFlexiTime\Entity\Booking;
use ZfcUser\Entity\UserInterface;

/**
 * Interface BalanceServiceInterface
 * @package JhFlexiTimeTest\Service
 * @author Aydin Hassan <aydin@wearejh.com>
 */
interface BalanceServiceInterface
{

    /**
     * @param Booking $booking
     */
    public function update(Booking $booking);

    /**
     * @param Booking $booking
     * @return array
     */
    public function getBalanceDiff(Booking $booking);

    /**
     * @param UserInterface $user
     * @return \JhFlexiTime\Entity\RunningBalance
     */
    public function getRunningBalance(UserInterface $user);

    /**
     * @param UserInterface $user
     * @return \JhFlexiTime\Entity\RunningBalance
     */
    public function setupInitialRunningBalance(UserInterface $user);

    /**
     * @param Booking $booking
     */
    public function firstBookingOfTheMonth(Booking $booking);

    /**
     * @param Booking $booking
     */
    public function create(Booking $booking);

    /**
     * @param Booking $booking
     */
    public function remove(Booking $booking);
}
