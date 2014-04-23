<?php

namespace JhFlexiTimeTest\Options;

use JhFlexiTime\Options\ModuleOptions;

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
        $options = new ModuleOptions(array(
            'skip_weekends'     => false,
            'hours_in_day'      => 10,
            'lunch_duration'    => 0.5,
        ));

        $this->assertFalse($options->getSkipWeekends(), 'skip_weekends must be false');
        $this->assertFalse($options->skipWeekends(), 'skip_weekends must be false');
        $this->assertEquals(10, $options->getHoursInDay(), 'hours_in_day must be equal to 10');
        $this->assertEquals(0.5, $options->getLunchDuration(), 'lunch_duration must be equal to 0.5');
    }
}
