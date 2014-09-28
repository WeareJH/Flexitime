<?php

namespace JhFlexiTime\Controller;

use JhFlexiTime\Service\BookingService;
use JhFlexiTime\Service\TimeCalculatorService;
use JhFlexiTime\Validator\UniqueBooking;
use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Validator\Date as DateValidator;

/**
 * Class BookingController
 * @package JhFlexiTime\Controller
 * @author Aydin Hassan <aydin@wearejh.com>s
 */
class BookingController extends AbstractActionController
{
    use GetSetDateTrait;

    /**
     * @var \JhFlexiTime\Form\BookingForm
     */
    protected $bookingForm;

    /**
     * @var \JhFlexiTime\Service\BookingService
     */
    protected $bookingService;

    /**
     * @var \JhFlexiTime\Service\TimeCalculatorService
     */
    protected $timeCalculatorService;

    /**
     * @param BookingService $bookingService
     * @param TimeCalculatorService $timeCalculatorService
     * @param FormInterface $bookingForm
     */
    public function __construct(
        BookingService $bookingService,
        TimeCalculatorService $timeCalculatorService,
        FormInterface $bookingForm
    ) {
        $this->bookingService           = $bookingService;
        $this->bookingForm              = $bookingForm;
        $this->timeCalculatorService    = $timeCalculatorService;
    }

    /**
     * @return ViewModel
     */
    public function listAction()
    {

        $month  = (string) $this->params()->fromQuery('m');
        $year   = (string) $this->params()->fromQuery('y');
        $period = $this->getDate($month, $year);

        $user           = $this->zfcUserAuthentication()->getIdentity();
        $records        = $this->bookingService->getUserBookingsForMonth($user, $period);
        $pagination     = $this->bookingService->getPagination($period);
        $totals         = $this->timeCalculatorService->getTotals($user, $period);

        return new ViewModel([
            'bookings' => [
                'records'       => $records,
                'totals'        => $totals,
                'user'          => $user,
            ],
            'pagination' => $pagination,
            'date'       => $period,
            'today'      => new \DateTime("today"),
            'form'       => $this->bookingForm,
        ]);
    }
}
