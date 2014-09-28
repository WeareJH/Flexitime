<?php

namespace JhFlexiTime\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Class BookingClasses
 * @package JhFlexiTime\View\Helper
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingClasses extends AbstractHelper
{

    /**
     * Classes for various conditions
     *
     * @var array
     */
    protected $classes = [
        'noBooking'         => 'no-booking',
        'pastNoBooking'     => 'no-booking-past',
        'futureNoBooking'   => 'no-booking-future',
        'currentDay'        => 'today',
    ];

    /**
     * @param \DateTime $date
     * @param bool $bookingExists
     * @return string
     */
    public function __invoke(\DateTime $date, $bookingExists)
    {
        $classes = [];

        $today = new \DateTime('today');
        if ($today->format('d-m-y') == $date->format('d-m-y')) {
            $classes[] = $this->getClass('currentDay');
        }

        //if there is no booking for this day then we add a class
        //for past or future missing bookings
        if (!$bookingExists) {

            $classes[] = $this->getClass('noBooking');
            $diff = $today->diff($date);
            //get number of days diff with sign = -15, +10
            $days   = (int) $diff->format('%r%a');

            if ($days > 0) {
                //day is in future
                $classes[] = $this->getClass('futureNoBooking');
            } elseif ($days < 0) {
                //day is in past
                $classes[] = $this->getClass('pastNoBooking');
            }
        }

        return implode(' ', $classes);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getClass($type)
    {
        return $this->classes[$type];
    }
}
