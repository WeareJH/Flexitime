<?php

namespace JhFlexiTime\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\Validator\ValidatorInterface;
use JhFlexiTime\Options\BookingOptionsInterface;
use Zend\InputFilter\Input;
use JhFlexiTime\Filter\DateTimeFormatter;
use Zend\Validator\Date;

/**
 * Class BookingInputFilter
 * @package JhFlexiTime\InputFilter
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingInputFilter extends BaseInputFilter
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

        $this->add(
            [
                'name'      => 'user',
                'required'  => true,
                'filters'   => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 512,
                        ],
                    ],
                    $userExistsValidator
                ],
            ]
        );


        $date = new Input('date');
        $date->setRequired(true);
        $date->getFilterChain()->attach(new DateTimeFormatter(['format' => 'd-m-Y']));
        $date->getValidatorChain()->attach($uniqueBookingValidator);
        $date->getValidatorChain()->attach(new Date(['format' => 'd-m-Y']));


        $this->add($date);

        $startTimeValidators = [
            [
                'name'      => 'Date',
                'options'   => [
                    'format' => 'H:i',
                ],
            ],
            [
                'name'      => 'DateStep',
                'options'   => [
                    'format'    => 'H:i',
                    'baseValue' => '00:00',
                    'step'      => new \DateInterval("PT{$this->config['step']}S"),
                ],
            ],
        ];

        if ($bookingOptions->getMinStartTime()) {
            $startTimeValidators[] = [
                'name'      => 'GreaterThan',
                'options'   => [
                    'min'       => $bookingOptions->getMinStartTime(),
                    'inclusive' => true,
                ],
            ];
        }

        if ($bookingOptions->getMaxStartTime()) {
            $startTimeValidators[] = [
                'name'      => 'LessThan',
                'options'   => [
                    'max'       => $bookingOptions->getMaxStartTime(),
                    'inclusive' => true,
                ],
            ];
        }

        $this->add(
            [
                'name'      => 'startTime',
                'required'  => true,
                'filters'   => [
                    ['name' => 'StringTrim']
                ],
                'validators' => $startTimeValidators,
            ]
        );

        $endTimeValidators = [
            [
                'name'      => 'Date',
                'options'   => [
                    'format' => 'H:i',
                ],
            ],
            [
                'name'      => 'DateStep',
                'options'   => [
                    'format'    => 'H:i',
                    'baseValue' => '00:00',
                    'step'      => new \DateInterval("PT{$this->config['step']}S"),
                ],
            ],
        ];

        if ($bookingOptions->getMinEndTime()) {
            $endTimeValidators[] = [
                'name'      => 'GreaterThan',
                'options'   => [
                    'min'       => $bookingOptions->getMinEndTime(),
                    'inclusive' => true,
                ],
            ];
        }

        if ($bookingOptions->getMaxEndTime()) {
            $endTimeValidators[] = [
                'name'      => 'LessThan',
                'options'   => [
                    'max'       => $bookingOptions->getMaxEndTime(),
                    'inclusive' => true,
                ],
            ];
        }

        $this->add(
            [
                'name'      => 'endTime',
                'required'  => true,
                'filters'   => [
                    ['name' => 'StringTrim']
                ],
                'validators' => $endTimeValidators,
            ]
        );

        //notes
        $this->add(
            [
                'name'      => 'notes',
                'required'  => false,
                'filters'   => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 512,
                        ],
                    ],
                ],
            ]
        );
    }
}
