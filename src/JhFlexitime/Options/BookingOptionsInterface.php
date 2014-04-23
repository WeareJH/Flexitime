<?php

namespace JhFlexiTime\Options;

/**
 * Interface BookingOptionsInterface
 * @package JhFlexiTime\Options
 * @author Aydin Hassan <aydin@wearejh.com>
 */
interface BookingOptionsInterface
{
    /**
     * @return bool|string
     */
    public function getMinStartTime();

    /**
     * @return bool|string
     */
    public function getMaxStartTime();

    /**
     * @return bool|string
     */
    public function getMinEndTime();

    /**
     * @return bool|string
     */
    public function getMaxEndTime();
}
