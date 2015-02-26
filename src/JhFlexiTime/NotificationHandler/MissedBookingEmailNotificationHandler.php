<?php

namespace JhFlexiTime\NotificationHandler;

use AcMailer\Service\MailServiceInterface;
use JhFlexiTime\Notification\MissingBookingsNotification;
use JhHubBase\Notification\NotificationHandlerInterface;
use JhHubBase\Notification\NotificationInterface;
use JhHubBase\Options\ModuleOptions;
use rfreebern\Giphy;
use Zend\View\Model\ViewModel;
use ZfcUser\Entity\UserInterface;

/**
 * Class MissedBookingEmailNotificationHandler
 * @package JhHub\Notification
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class MissedBookingEmailNotificationHandler implements NotificationHandlerInterface
{

    /**
     * @var MailServiceInterface
     */
    protected $mailService;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var Giphy
     */
    protected $giphyApi;

    /**
     * @param MailServiceInterface $mailService
     * @param ModuleOptions        $options
     * @param Giphy                $giphyApi
     */
    public function __construct(MailServiceInterface $mailService, ModuleOptions $options, Giphy $giphyApi)
    {
        $this->mailService  = $mailService;
        $this->options      = $options;
        $this->giphyApi     = $giphyApi;
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return bool
     */
    public function shouldHandle(NotificationInterface $notification)
    {
        return 'missing-bookings' === $notification->getName()
            && $notification instanceof MissingBookingsNotification;
    }

    /**
     * @param NotificationInterface $notification
     * @param UserInterface         $user
     */
    public function handle(NotificationInterface $notification, UserInterface $user)
    {
        $this->mailService->setSubject('Missing Bookings');
        $this->mailService->getMessage()->setTo([$user->getEmail()]);

        $randomGifUrl   = '';
        $randomGifData  = $this->giphyApi->random('fail');
        if ($randomGifData instanceof \stdClass) {
            $randomGifUrl = $randomGifData->data->image_original_url;
        }

        $model = new ViewModel(array(
            'user'          => $user,
            'missedDates'   => $notification->getMissingBookings(),
            'datePeriod'    => $notification->getPeriod(),
            'appUrl'        => $this->options->getAppUrl(),
            'randomFailGif' => $randomGifUrl,
        ));
        $model->setTemplate('jh-flexi-time/emails/missed-bookings');

        $this->mailService->setTemplate($model);
        $this->mailService->send();
    }
}
