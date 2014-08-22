<?php
require_once dirname(__FILE__) . '/../TestConfiguration.php';

class models_PlacesTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        TestConfiguration::setupDatabase();  
    }

    public function testGetAllNames() {
        $achievementFinder = new Achievements();
        $names = $achievementFinder->getAllNames(); 
        
        $this->assertSame(2, count($names));
        $this->assertSame($names[1], 'Core');
    }
}
