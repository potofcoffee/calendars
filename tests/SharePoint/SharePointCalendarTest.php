<?php

namespace SharePoint;

use Peregrinus\Calendars\SharePoint\SharePointCalendar;
use Peregrinus\Calendars\SharePoint\SharePointCalendarItem;
use PHPUnit\Framework\TestCase;

class SharePointCalendarTest extends TestCase
{

    /** @var SharePointCalendar  */
    protected $calendar = null;

    protected function setUp(): void
    {
        parent::setUp();
        $break = false;
        foreach (['TEST_SP_URL', 'TEST_SP_USER', 'TEST_SP_PASSWORD', 'TEST_SP_LIST'] as $key) {
            if (getenv($key) == '') {
                echo 'Environment variable ' . $key . ' must be set for these tests to work.' . PHP_EOL;
            }
        }
        if ($break) {
            die();
        }
    }

    public function testCreateObject()
    {
        $sp = $this->getCachedCalendarObject();
        $this->assertIsObject($sp);
        $this->assertInstanceOf(SharePointCalendar::class, $sp);

    }

    public function testGetLists()
    {
        $sp = $this->getCachedCalendarObject();
        $lists = $sp->getCalendars();
        echo print_r($lists, 1).PHP_EOL;
        $this->assertIsArray($lists);
    }


    public function testWsdlPreloading() {
        $sp = $this->getCachedCalendarObject();
        $this->assertFileExists($sp->getWsdlFile());
        $wsdl = file_get_contents($sp->getWsdlFile());
        $this->assertNotEmpty($wsdl);
    }

    public function testDeleteTemporaryWsdlFile() {
        $sp = $this->createObject();
        $file = $sp->getWsdlFile();
        unset($sp);
        $this->assertFileNotExists($file);
    }

    public function testGetListName() {
        $sp = $this->getCachedCalendarObject();
        $this->assertNotEmpty($sp->getListName());
    }

    public function testGetList() {
        $sp = $this->getCachedCalendarObject();
        $columns = $sp->getColumns();
        $this->assertIsArray($columns);
        $this->assertNotEmpty($columns);
    }

    public function testCreateItem() {
        $sp = $this->getCachedCalendarObject();
        $event1 = $sp->create(['startDate' => '2020-10-02 09:01:02', 'endDate' => '2020-10-02 10:15:18', 'title' => 'SP TEST', 'location' => 'TEST LOCATION']);
        $this->assertNotNull($event1);

        $event2 = $sp->find($event1->getID());
        $this->assertNotNull($event2);
        $this->assertEquals($event1->getID(), $event2->getID());

        $id = $event2->getID();
        $event2->delete();
        $this->assertNull($sp->find($id));
    }

    public function testUpdateItem() {
        $sp = $this->getCachedCalendarObject();
        $event1 = $sp->create(['startDate' => '2020-10-02 09:01:02', 'endDate' => '2020-10-02 10:15:18', 'title' => 'SP UPD TEST', 'location' => 'TEST LOCATION']);
        $this->assertNotNull($event1);

        $event2 = $event1->update(['title' => 'SP TEST 1']);
        $this->assertEquals('SP TEST 1', $event2->getTitle());

        $event3 = $sp->find($event1->getID());
        $this->assertEquals('SP TEST 1', $event3->getTitle());

        $event3->delete();
    }

    protected function dump($v) {
        echo print_r($v, 1).PHP_EOL;
    }

    protected function createObject() {
        // try creating SharePointCalendarObject
        $calendar = new SharePointCalendar(getenv('TEST_SP_URL'), getenv('TEST_SP_USER'), getenv('TEST_SP_PASSWORD'));
        $calendar->setListName(getenv('TEST_SP_LIST'));
        return $calendar;
    }

    protected function getCachedCalendarObject() {
        if (null === $this->calendar) $this->calendar = $this->createObject();
        return $this->calendar;
    }


}
