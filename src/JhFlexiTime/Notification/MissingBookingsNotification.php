<?php

namespace JhFlexiTime\Notification;

use JhHubBase\Notification\Notification;

/**
 * Class MissingBookingsNotification
 * @package JhFlexiTime\Notification
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class MissingBookingsNotification extends Notification
{
    /**
     * @var array
     */
    protected $period;

    /**
     * @var array
     */
    protected $missingBookings;

    /**
     * @param array $period
     * @param array $missingBookings
     */
    public function __construct(array $period, array $missingBookings)
    {
        parent::__construct('missing-bookings', [
            'period'            => $period,
            'missingBookings'   => $missingBookings
        ]);
        $this->period           = $period;
        $this->missingBookings  = $missingBookings;
    }

    /**
     * @return array
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @return array
     */
    public function getMissingBookings()
    {
        return $this->missingBookings;
    }
}
