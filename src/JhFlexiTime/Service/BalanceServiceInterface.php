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
    public function updateBalance(Booking $booking);

    /**
     * @param Booking $booking
     */
    public function updateFromPreviousMonth(Booking $booking);

    /**
     * @param UserInterface $user
     * @return \JhFlexiTime\Entity\RunningBalance
     */
    public function getRunningBalance(UserInterface $user);

}
