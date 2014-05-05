<?php

namespace JhFlexiTime\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use JhFlexiTime\Options\BookingOptions;

/**
 * Class SettingsController
 * @package JhFlexiTime\Controller
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class SettingsController extends AbstractActionController
{
    /**
     * @var BookingOptions
     */
    protected $bookingOptions;

    /**
     * @param BookingOptions $bookingOptions
     */
    public function __construct(BookingOptions $bookingOptions)
    {
        $this->bookingOptions = $bookingOptions;
    }

    /**
     * Get Settings
     *
     * @return JsonModel
     */
    public function getAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();

        return new JsonModel(array(
            'success'   => true,
            'settings'  => $this->bookingOptions,
        ));
    }
}
