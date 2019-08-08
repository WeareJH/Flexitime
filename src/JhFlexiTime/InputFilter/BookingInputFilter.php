<?php

namespace JhFlexiTime\InputFilter;

use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Validator\DateStep;
use Zend\Validator\GreaterThan;
use Zend\Validator\LessThan;
use Zend\Validator\StringLength;
use Zend\Validator\ValidatorInterface;
use JhFlexiTime\Options\BookingOptionsInterface;
use JhFlexiTime\Filter\DateTimeFormatter;
use Zend\Validator\Date;
use Zend\InputFilter\InputFilter;

/**
 * Class BookingInputFilter
 * @package JhFlexiTime\InputFilter
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingInputFilter extends InputFilter
{

    //TODO: Get from module config
    protected $config = [
        'date-format'   => 'd-m-Y',
        'step'          => '900', //15 x 60 - 15 Min's
    ];

    /**
     * @param ValidatorInterface $uniqueBookingValidator
     * @param ValidatorInterface $userExistsValidator
     * @param BookingOptionsInterface $bookingOptions
     */
    public function __construct(
        ValidatorInterface $uniqueBookingValidator,
        ValidatorInterface $userExistsValidator,
        BookingOptionsInterface $bookingOptions
    ) {

        //user
        $user = new Input('user');
        $user->setRequired(true);
        $user->getFilterChain()
            ->attach(new StripTags())
            ->attach(new StringTrim());

        $user->getValidatorChain()
            ->attach(new StringLength([
                'encoding' => 'UTF-8',
                'min'      => 1,
                'max'      => 512,
            ]))
            ->attach($userExistsValidator);

        $this->add($user);

        //date
        $date = new Input('date');
        $date->setRequired(true);
        $date->getFilterChain()
            ->attach(new DateTimeFormatter(['format' => 'd-m-Y']));
        $date->getValidatorChain()
            ->attach($uniqueBookingValidator)
            ->attach(new Date(['format' => 'd-m-Y']));

        $this->add($date);

        $startTimeValidators = $this->getStartTimeValidators();

        if ($bookingOptions->getMinStartTime()) {
            $startTimeValidators[] = new GreaterThan([
                'min'       => $bookingOptions->getMinStartTime(),
                'inclusive' => true,
            ]);
        }

        if ($bookingOptions->getMaxStartTime()) {
            $startTimeValidators[] = new LessThan([
                'max'       => $bookingOptions->getMaxStartTime(),
                'inclusive' => true,
            ]);
        }

        //start time
        $startTime = new Input('startTime');
        $startTime->setRequired(true);
        $startTime->getFilterChain()
            ->attach(new StringTrim());

        foreach ($startTimeValidators as $validator) {
            $startTime->getValidatorChain()->attach($validator);
        }
        $this->add($startTime);

        $endTimeValidators = $this->getEndTimeValidators();

        if ($bookingOptions->getMinEndTime()) {
            $endTimeValidators[] = new GreaterThan([
                'min'       => $bookingOptions->getMinEndTime(),
                'inclusive' => true,
            ]);
        }

        if ($bookingOptions->getMaxEndTime()) {
            $endTimeValidators[] = new LessThan([
                'max'       => $bookingOptions->getMaxEndTime(),
                'inclusive' => true,
            ]);
        }

        //end time
        $endTime = new Input('endTime');
        $endTime->setRequired(true);
        $endTime->getFilterChain()
            ->attach(new StringTrim());

        foreach ($endTimeValidators as $validator) {
            $endTime->getValidatorChain()->attach($validator);
        }
        $this->add($endTime);

        //work log type
        $logType = new Input('logType');
        $logType->setRequired(true);

        $this->add($logType);

        //notes
        $notes = new Input('notes');
        $notes->setRequired(false);
        $notes->getFilterChain()
            ->attach(new StripTags())
            ->attach(new StringTrim());

        $notes->getValidatorChain()
            ->attach(new StringLength([
                'encoding' => 'UTF-8',
                'min'      => 1,
                'max'      => 512,
            ]));

        $this->add($notes);
    }

    /**
     * @return ValidatorInterface[]
     */
    protected function getStartTimeValidators()
    {
        return [
            new Date([
                'format' => 'H:i'
            ]),
            new DateStep([
                'format'    => 'H:i',
                'baseValue' => '00:00',
                'step'      => new \DateInterval("PT{$this->config['step']}S"),
            ]),
        ];
    }

    /**
     * @return ValidatorInterface[]
     */
    protected function getEndTimeValidators()
    {
        return [
            new Date([
                'format' => 'H:i'
            ]),
            new DateStep([
                'format'    => 'H:i',
                'baseValue' => '00:00',
                'step'      => new \DateInterval("PT{$this->config['step']}S"),
            ]),
        ];
    }
}
