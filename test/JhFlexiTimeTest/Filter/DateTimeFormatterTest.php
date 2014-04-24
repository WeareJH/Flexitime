<?php

namespace JhFlexiTimeTest\Filter;

use JhFlexiTime\Filter\DateTimeFormatter;
use DateTime;

/**
 * Class DateTimeFormatterTest
 * @package JhFlexiTimeTest\Filter
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class DateTimeFormatterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DateTimeFormatter
     */
    protected $formatter;

    /**
     * SetUp
     */
    public function setUp()
    {
        $this->formatter = new DateTimeFormatter();
    }

    /**
     * @param $value
     * @param $expected
     *
     * @dataProvider filterValuesProvider
     */
    public function testDateTimeFormatterFilter($value, $expected)
    {
        $filteredValue = $this->formatter->filter($value);
        $this->assertEquals($expected, $filteredValue);
    }

    /**
     * @return array
     */
    public function filterValuesProvider()
    {
        return array(
            "valid-input-1"     => array("23 March 2014"    , new DateTime("23 March 2014")),
            "valid-input-2"     => array("23 Mar 2014"      , new DateTime("23 March 2014")),
            "invalid-input-1"   => array("23/03/2014"       , "23/03/2014"),
            "invalid-input-2"   => array("not-a-date"       , "not-a-date"),
            "no-input"          => array(""                 , ""),
            "timestamp"         => array(1359739801         , new DateTime("01 February 2013 17:30:01 +00:00"))
        );
    }

}
 