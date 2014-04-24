<?php

namespace JhFlexiTime\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\Validator\ValidatorInterface;
use JhFlexiTime\Options\BookingOptionsInterface;

/**
 * Class BookingInputFilter
 * @package JhFlexiTime\InputFilter
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingInputFilter extends InputFilter
{

    //TODO: Get from module config
    protected $config = array(
        'date-format'   => 'd-m-Y',
        'step'          => '900', //15 x 60 - 15 Min's
    );

    /**
     * @param ValidatorInterface $uniqueBookingValidator
     * @param BookingOptionsInterface $bookingOptions
     */
    public function __construct(
        ValidatorInterface $uniqueBookingValidator,
        BookingOptionsInterface $bookingOptions
    ) {

        $this->add(
            array(
                'name'      => 'date',
                'required'  => true,
                'filters'   => array(
                    array(
                        'name' => 'JhFlexiTime\Filter\DateTimeFormatter',
                        'options' => array(
                            'format' => 'd-m-Y',
                        ),
                    ),
                ),
                'validators' => array(
                    array(
                        'name'      => 'Date',
                        'options'   => array(
                            'format' => 'd-m-Y',
                        ),
                    ),
                    $uniqueBookingValidator,
                ),
            )
        );

        $startTimeValidators = array(
            array(
                'name'      => 'Date',
                'options'   => array(
                    'format' => 'H:i',
                ),
            ),
            array(
                'name'      => 'DateStep',
                'options'   => array(
                    'format'    => 'H:i',
                    'baseValue' => '00:00',
                    'step'      => new \DateInterval("PT{$this->config['step']}S"),
                ),
            ),
        );

        if($bookingOptions->getMinStartTime()) {
            $startTimeValidators[] = array(
                'name'      => 'GreaterThan',
                'options'   => array(
                    'min'       => $bookingOptions->getMinStartTime(),
                    'inclusive' => true,
                ),
            );
        }

        if($bookingOptions->getMaxStartTime()) {
            $startTimeValidators[] = array(
                'name'      => 'LessThan',
                'options'   => array(
                    'max'       => $bookingOptions->getMaxStartTime(),
                    'inclusive' => true,
                ),
            );
        }

        $this->add(
            array(
                'name'      => 'startTime',
                'required'  => true,
                'filters'   => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => $startTimeValidators,
            )
        );

        $endTimeValidators = array(
            array(
                'name'      => 'Date',
                'options'   => array(
                    'format' => 'H:i',
                ),
            ),
            array(
                'name'      => 'DateStep',
                'options'   => array(
                    'format'    => 'H:i',
                    'baseValue' => '00:00',
                    'step'      => new \DateInterval("PT{$this->config['step']}S"),
                ),
            ),
        );

        if($bookingOptions->getMinEndTime()) {
            $endTimeValidators[] = array(
                'name'      => 'GreaterThan',
                'options'   => array(
                    'min'       => $bookingOptions->getMinEndTime(),
                    'inclusive' => true,
                ),
            );
        }

        if($bookingOptions->getMaxEndTime()) {
            $endTimeValidators[] = array(
                'name'      => 'LessThan',
                'options'   => array(
                    'max'       => $bookingOptions->getMaxEndTime(),
                    'inclusive' => true,
                ),
            );
        }

        $this->add(
            array(
                'name'      => 'endTime',
                'required'  => true,
                'filters'   => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => $endTimeValidators,
            )
        );

        //notes
        $this->add(
            array(
                'name'      => 'notes',
                'required'  => false,
                'filters'   => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 512,
                        ),
                    ),
                ),
            )
        );
    }
}
