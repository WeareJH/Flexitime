<?php

namespace JhFlexiTime\Controller;

use JhFlexiTime\Service\MissingBookingReminderService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;

/**
 * Class UserReminderCliController
 * @package JhUser\Controller
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class UserReminderCliController extends AbstractActionController
{
    /**
     * @var MissingBookingReminderService
     */
    private $missingBookingService;

    /**
     * @param MissingBookingReminderService $missingBookingService
     * @param AdapterInterface $console
     */
    public function __construct(MissingBookingReminderService $missingBookingService, AdapterInterface $console)
    {
        $this->missingBookingService = $missingBookingService;
        $this->console               = $console;
    }

    /**
     * Remind user's about missing bookings
     */
    public function findAndNotifyMissingBookingsAction()
    {
        $this->missingBookingService->findAndNotifyMissingBookings();
        $this->console->writeLine("Finished! ", ColorInterface::GREEN);
    }
}
