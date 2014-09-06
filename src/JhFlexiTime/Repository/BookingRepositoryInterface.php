<?php

namespace JhFlexiTime\Repository;

use ZfcUser\Entity\UserInterface;
use JhFlexiTime\DateTime\DateTime;

/**
 * Interface BookingRepositoryInterface
 * @package JhFlexiTime\Repository
 * @author Aydin Hassan <aydin@wearejh.com>
 */
interface BookingRepositoryInterface
{
    /**
     * @param array $criteria
     * @return \JhFlexiTime\Entity\Booking
     */
    public function findOneBy(array $criteria);

    /**
     * @param UserInterface $user
     * @return array
     */
    public function findAllByUser(UserInterface $user);

    /**
     * @param UserInterface $user
     * @param DateTime $date
     * @return array
     */
    public function findByUserAndMonth(UserInterface $user, DateTime $date);

    /**
     * @param UserInterface $user
     * @param DateTime $month
     * @return bool
     */
    public function isUsersFirstBookingForMonth(UserInterface $user, DateTime $month);

    /**
     * @param UserInterface $user
     * @param DateTime $date
     * @return float
     */
    public function getMonthBookedTotalByUser(UserInterface $user, DateTime $date);

    /**
     * @param UserInterface $user
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return float
     */
    public function getTotalBookedBetweenByUser(UserInterface $user, DateTime $startDate, DateTime $endDate);

    /**
     * @param UserInterface $user
     * @param DateTime $date
     * @return float
     */
    public function getMonthBookedToDateTotalByUser(UserInterface $user, DateTime $date);
}
