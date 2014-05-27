<?php

namespace JhFlexiTimeTest\InputFilter;

use JhFlexiTime\InputFilter\BookingInputFilter;

/**
 * Class BookingFilterTest
 * @package JhFlexiTimeTest\InputFilter
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingInputFilterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param array $input
     * @param array $output
     * @param array $messages
     * @dataProvider formDataProvider
     */
    public function testInputFilter(array $input, array $output = null, array $messages = null)
    {

        $mockValidator  = $this->getMockValidator(true);
        $mockOptions    = $this->getMock('JhFlexiTime\Options\BookingOptionsInterface');
        $filter         = new BookingInputFilter($mockValidator, $mockOptions);
        $filter->setData($input);


        if ($output === null) {
            $this->assertFalse($filter->isValid(), 'Input must not be valid');
            $this->assertEquals($messages, $filter->getMessages());
        } else {
            $this->assertTrue($filter->isValid(), 'Input must be valid');
            $this->assertEquals($output, $filter->getValues());
        }
    }

    /**
     * @param bool $validates
     * @return \Zend\Validator\ValidatorInterface
     */
    protected function getMockValidator($validates)
    {
        $validator = $this->getMock('Zend\Validator\ValidatorInterface');
        $validator->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue($validates));

        if (!$validates) {
            $validator->expects($this->any())
                ->method('getMessages')
                ->will($this->returnValue(array()));
        }

        return $validator;
    }

    /**
     * @param array $input
     * @param array $messages
     * @param string $method
     * @param string $value
     * @dataProvider optionsProvider
     */
    public function testInputFilterFailsIfMinMaxTimeOptionsArePresent(
        array $input,
        array $messages,
        $method,
        $value
    ) {
        $mockValidator  = $this->getMockValidator(true);
        $mockOptions    = $this->getMock('JhFlexiTime\Options\BookingOptionsInterface');
        $mockOptions
            ->expects($this->exactly(2))
            ->method($method)
            ->will($this->returnValue($value));

        $filter = new BookingInputFilter($mockValidator, $mockOptions);
        $filter->setData($input);

        $this->assertFalse($filter->isValid(), 'Input must not be valid');
        $this->assertEquals($messages, $filter->getMessages());

    }

    public function optionsProvider()
    {
        $input = array(
            'date'      => '12-04-1988',
            'startTime' => '08:00',
            'endTime'   => '17:00',
            'notes'     => 'Some notes',
        );
        return array(
            'less-than-min-start-time' => array(
                $input,
                array(
                    'startTime' => array (
                        'notGreaterThanInclusive' => 'The input is not greater or equal than \'10:00\'',
                    ),
                ),
                'getMinStartTime',
                '10:00',
            ),
            'greater-than-max-start-time' =>array(
                $input,
                array(
                    'startTime' => array (
                        'notLessThanInclusive' => 'The input is not less or equal than \'07:00\'',
                    ),
                ),
                'getMaxStartTime',
                '07:00',
            ),
            'less-than-min-end-time' => array(
                $input,
                array(
                    'endTime' => array (
                        'notGreaterThanInclusive' => 'The input is not greater or equal than \'17:15\'',
                    ),
                ),
                'getMinEndTime',
                '17:15',
            ),
            'greater-than-max-end-time' =>array(
                $input,
                array(
                    'endTime' => array (
                        'notLessThanInclusive' => 'The input is not less or equal than \'16:45\'',
                    ),
                ),
                'getMaxEndTime',
                '16:45',
            ),
        );
    }

    /**
     * @param array $input
     * @param string $method
     * @param string $value
     * @dataProvider optionsValidProvider
     */
    public function testInputFilterValidatesIfMinMaxTimeOptionsArePresent(array $input, $method, $value)
    {
        $mockValidator  = $this->getMockValidator(true);
        $mockOptions    = $this->getMock('JhFlexiTime\Options\BookingOptionsInterface');
        $mockOptions
            ->expects($this->exactly(2))
            ->method($method)
            ->will($this->returnValue($value));

        $filter = new BookingInputFilter($mockValidator, $mockOptions);
        $filter->setData($input);

        $this->assertTrue($filter->isValid(), 'Input must not be valid');

    }

    public function optionsValidProvider()
    {
        $input = array(
            'date'      => '12-04-1988',
            'startTime' => '08:00',
            'endTime'   => '17:00',
            'notes'     => 'Some notes',
        );
        return array(
            'less-than-min-start-time' => array(
                $input,
                'getMinStartTime',
                '08:00',
            ),
            'greater-than-max-start-time' =>array(
                $input,
                'getMaxStartTime',
                '08:00',
            ),
            'less-than-min-end-time' => array(
                $input,
                'getMinEndTime',
                '17:00',
            ),
            'greater-than-max-end-time' =>array(
                $input,
                'getMaxEndTime',
                '17:00',
            ),
        );
    }

    /**
     * Valid & Invalid Datasets
     *
     * @return array
     */
    public function formDataProvider()
    {
        return array(
            'completely-valid-input' => array(
                array(
                    'date'      => '12-04-1988',
                    'startTime' => '08:00',
                    'endTime'   => '17:00',
                    'notes'     => 'Some notes',
                ),
                array(
                    'date'      => new \DateTime('12-04-1988'),
                    'startTime' => '08:00',
                    'endTime'   => '17:00',
                    'notes'     => 'Some notes',
                ),
                null,
            ),
            'valid-time-low-boundary' => array(
                array(
                    'date'      => '12-04-1988',
                    'startTime' => '07:00',
                    'endTime'   => '16:00',
                    'notes'     => 'Some notes',
                ),
                array(
                    'date'      => new \DateTime('12-04-1988'),
                    'startTime' => '07:00',
                    'endTime'   => '16:00',
                    'notes'     => 'Some notes',
                ),
                null,
            ),
            'valid-time-high-boundary' => array(
                array(
                    'date'      => '12-04-1988',
                    'startTime' => '10:00',
                    'endTime'   => '19:00',
                    'notes'     => 'Some notes',
                ),
                array(
                    'date'      => new \DateTime('12-04-1988'),
                    'startTime' => '10:00',
                    'endTime'   => '19:00',
                    'notes'     => 'Some notes',
                ),
                null,
            ),
            'space-padded-valid-input' => array(
                array(
                    'date'      => '12-04-1988',
                    'startTime' => '07:00',
                    'endTime'   => '17:00',
                    'notes'     => '    Some notes   ',
                ),
                array(
                    'date'      => new \DateTime('12-04-1988'),
                    'startTime' => '07:00',
                    'endTime'   => '17:00',
                    'notes'     => 'Some notes',
                ),
                null,
            ),
            'invalid-date-format' => array(
                array(
                    'date'      => '12-not-a-month-2014',
                    'startTime' => '07:00',
                    'endTime'   => '17:00',
                ),
                null,
                array(
                    'date' => array (
                        'dateInvalidDate' => 'The input does not appear to be a valid date',
                        'dateFalseFormat' => 'The input does not fit the date format \'d-m-Y\'',
                    ),
                ),
            ),
            'invalid-date' => array(
                array(
                    'date'      => 'not-a-date',
                    'startTime' => '07:00',
                    'endTime'   => '17:00',
                ),
                null,
                array(
                    'date' => array (
                        'dateInvalidDate' => 'The input does not appear to be a valid date',
                    ),
                ),
            ),
            'greater-than-time' => array(
                array(
                    'date'      => '12-04-1988',
                    'startTime' => '10:01',
                    'endTime'   => '19:01',
                ),
                null,
                array(
                    'startTime' => array (
                        //'notLessThanInclusive' => 'The input is not less or equal than \'10:00\'',
                        'dateStepNotStep' => 'The input is not a valid step',
                    ),
                    'endTime' => array (
                        //'notLessThanInclusive' => 'The input is not less or equal than \'19:00\'',
                        'dateStepNotStep' => 'The input is not a valid step',
                    ),
                ),
            ),
            'invalid-time' => array(
                array(
                    'date'      => '12-04-1988',
                    'startTime' => 'not-a-time',
                    'endTime'   => 'not-a-time',
                ),
                null,
                array(
                    'startTime' => array(
                        'dateInvalidDate' => 'The input does not appear to be a valid date',
                        //'notLessThanInclusive' => 'The input is not less or equal than \'10:00\'',
                    ),
                    'endTime' => array(
                        'dateInvalidDate' => 'The input does not appear to be a valid date',
                        //'notLessThanInclusive' => 'The input is not less or equal than \'19:00\'',
                    ),
                ),
            ),
            'message-to-long' => array(
                array(
                    'date'      => '12-04-1988',
                    'startTime' => '09:00',
                    'endTime'   => '17:00',
                    'notes'     => $this->getLongString()
                ),
                null,
                array(
                    'notes' => array(
                        'stringLengthTooLong' => 'The input is more than 512 characters long',
                    ),
                ),
            ),
            'invalid-time-step' => array(
                array(
                    'date'      => '12-04-1988',
                    'startTime' => '09:04',
                    'endTime'   => '17:07',
                ),
                null,
                array(
                    'startTime' => array(
                        'dateStepNotStep' => 'The input is not a valid step',
                    ),
                    'endTime' => array(
                        'dateStepNotStep' => 'The input is not a valid step',
                    ),
                ),
            ),
            'required-fields' => array(
                array(
                ),
                null,
                array(
                    'date' => array(
                        'isEmpty' => 'Value is required and can\'t be empty',
                    ),
                    'startTime' => array(
                        'isEmpty' => 'Value is required and can\'t be empty',
                    ),
                    'endTime' => array(
                        'isEmpty' => 'Value is required and can\'t be empty',
                    ),
                ),
            ),
        );
    }

    public function testStartTimeEndTimeSameTime()
    {
        $mockValidator  = $this->getMockValidator(true);
        $mockOptions    = $this->getMock('JhFlexiTime\Options\BookingOptionsInterface');


        $filter = new BookingInputFilter($mockValidator, $mockOptions);
        $filter->setData([
            'date'      => '12-04-1988',
            'startTime' => '00:00',
            'endTime'   => '00:00',
            'notes'     => '',
        ]);

        $this->assertTrue($filter->isValid(), 'Input must not be valid');
    }

    /**
     * @return string
     */
    protected function getLongString()
    {
        $text = <<<EOT
  Long string which should fail the string length validator,
  this string should be longer than 512 characters to fail the validation
  of this filter component.

  Long string which should fail the string length validator,
  this string should be longer than 512 characters to fail the validation
  of this filter component.

  Long string which should fail the string length validator,
  this string should be longer than 512 characters to fail the validation
  of this filter component.

  Long string which should fail the string length validator,
  this string should be longer than 512 characters to fail the validation
  of this filter component.
EOT;
        return $text;
    }
}
