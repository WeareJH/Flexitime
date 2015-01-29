<?php

namespace JhFlexiTimeTest\InputFilter;

use JhFlexiTime\InputFilter\BaseInputFilter as InputFilter;
use PHPUnit_Framework_TestCase;
use Zend\Filter\StringTrim;
use JhFlexiTime\InputFilter\Input;
use Zend\Validator;

/**
 * Class BaseInputFilterTest
 * @package JhFlexiTimeTest\InputFilter
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class InputTest extends PHPUnit_Framework_TestCase
{
    public function testValidatorReceivesFilteredValueInContext()
    {
        $input1 = new Input('input1');
        $input1->getFilterChain()->attach(new StringTrim());

        $validator = $this->getMock('Zend\Validator\ValidatorInterface');
        $validator
            ->expects($this->once())
            ->method('isValid')
            ->with('value1', ['input1' => 'value1']);

        $validator
            ->expects($this->once())
            ->method('getMessages')
            ->will($this->returnValue([]));

        $input1->getValidatorChain()->attach($validator);

        $input1->setValue('   value1    ');
        $input1->isValid(['input1' => '   value1    ']);
    }
}
