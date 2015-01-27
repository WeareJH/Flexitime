<?php

namespace JhFlexiTimeTest\Options;

use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Options\ModuleOptions;
use Zend\ModuleManager\ModuleManager;

/**
 * Class ModuleOptionsTest
 * @package JhFlexiTime\ModuleOptions\ModuleOptionsTest
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class ModuleOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test The default options are correct
     */
    public function testDefaults()
    {
        $options = new ModuleOptions();
        $this->assertTrue($options->getSkipWeekends(), 'skip_weekends must default to true');
        $this->assertTrue($options->skipWeekends(), 'skip_weekends must default to true');
        $this->assertEquals(7.5, $options->getHoursInDay(), 'hours_in_day must default to 7.5');
        $this->assertEquals(1, $options->getLunchDuration(), 'lunch_duration must default to 1');
    }

    public function testSetValues()
    {
        $options = new ModuleOptions([
            'skip_weekends'     => false,
            'hours_in_day'      => 10,
            'lunch_duration'    => 0.5,
        ]);

        $this->assertFalse($options->getSkipWeekends(), 'skip_weekends must be false');
        $this->assertFalse($options->skipWeekends(), 'skip_weekends must be false');
        $this->assertEquals(10, $options->getHoursInDay(), 'hours_in_day must be equal to 10');
        $this->assertEquals(0.5, $options->getLunchDuration(), 'lunch_duration must be equal to 0.5');
    }

    public function testCreditCaps()
    {
        $options = new ModuleOptions;
        $this->assertEquals([], $options->getCreditCaps());

        $options = new ModuleOptions([
            'credit_caps' => [
                '10-2015' => 7.5,
                '07-2014' => 20,
                '11-2015' => 6,
            ],
        ]);

        $expectsCaps = [
            [
                'month' => new DateTime('01-07-2014 00:00:00'),
                'limit' => 20,
            ],
            [
                'month' => new DateTime('01-10-2015 00:00:00'),
                'limit' => 7.5,
            ],
            [
                'month' => new DateTime('01-11-2015 00:00:00'),
                'limit' => 6,
            ],
        ];

        $this->assertEquals($expectsCaps, $options->getCreditCaps());

        $this->assertEquals(6, $options->getCreditCapForDate(new DateTime("12-12-2015")));
        $this->assertEquals(7.5, $options->getCreditCapForDate(new DateTime("31-10-2015")));
        $this->assertEquals(20, $options->getCreditCapForDate(new DateTime("01-07-2014")));
        $this->assertEquals(20, $options->getCreditCapForDate(new DateTime("30-09-2014")));
        $this->assertEquals(7.5, $options->getCreditCapForDate(new DateTime("01-10-2015")));
        $this->assertNull($options->getCreditCapForDate(new DateTime('01-06-2014')));
    }
}
