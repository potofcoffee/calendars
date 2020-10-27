<?php

namespace Exchange;

use jamesiarmes\PhpEws\Client;
use Peregrinus\Calendars\Exchange\ExchangeCalendar;
use Peregrinus\Calendars\Exchange\ExchangeCalendarItem;
use PHPUnit\Framework\TestCase;

class ExchangeCalendarTest extends TestCase
{
    /** @var Client */
    protected $client = null;

    /** @var ExchangeCalendar */
    protected $calendar = null;

    protected function setUp(): void
    {
        parent::setUp();
        $break = false;
        foreach (['TEST_EX_SERVER', 'TEST_EX_USER', 'TEST_EX_PASSWORD', 'TEST_EX_FOLDER', 'TEST_EX_VERSION'] as $key) {
            if (getenv($key) == '') {
                echo 'Environment variable ' . $key . ' must be set for these tests to work.' . PHP_EOL;
            }
        }
        if ($break) {
            die();
        }
    }

    public function testConnection()
    {
        $ex = $this->getCachedClient();
        $this->assertNotNull($ex);
        unset ($ex);
    }

    public function testCreateObject()
    {
        $ex = $this->getCachedCalendar();
        $this->assertNotNull($ex);
    }

    public function testClientSetup()
    {
        $ex = $this->getCachedCalendar();
        $this->assertNotNull($ex->getClient());
    }


    public function testFindFolder()
    {
        $ex = $this->getCachedCalendar();
        $folder = $ex->findFolder();
        $this->assertNotNull($folder);
    }

    protected function getCachedClient()
    {
        if (null === $this->client) {
            $this->client = new Client(getenv('TEST_EX_SERVER'), getenv('TEST_EX_USER'), getenv('TEST_EX_PASSWORD'));
        }
        return $this->client;
    }

    public function testCreateItem() {
        $ex = $this->getCachedCalendar();
        $event = $ex->create(['startDate' => '2020-10-02 09:01:02', 'endDate' => '2020-10-02 10:15:18', 'title' => 'SP TEST', 'location' => 'TEST LOCATION']);
        $this->assertNotNull($event);
        $this->assertInstanceOf(ExchangeCalendarItem::class, $event);
        $this->assertEquals($event->getTitle(), 'SP TEST');

        $event->delete();
    }

    public function testDeleteItem() {
        $ex = $this->getCachedCalendar();
        $event = $ex->create(['startDate' => '2020-10-02 09:01:02', 'endDate' => '2020-10-02 10:15:18', 'title' => 'SP DEL TEST', 'location' => 'TEST LOCATION']);
        $this->assertNotNull($event);
        $this->assertInstanceOf(ExchangeCalendarItem::class, $event);

        $id = $event->getID();
        $result = $event->delete();
        $this->assertTrue($result);

        $result = $ex->find($id);
        $this->assertNull($result);
    }

    public function testUpdateItem()
    {
        $ex = $this->getCachedCalendar();
        $event = $ex->create(['startDate' => '2020-10-02 09:01:02', 'endDate' => '2020-10-02 10:15:18', 'title' => 'SP UPD TEST', 'location' => 'TEST LOCATION']);
        $this->assertNotNull($event);
        $this->assertInstanceOf(ExchangeCalendarItem::class, $event);

        $event = $event->update(['title' => 'SP UPDATED']);
        $this->assertEquals('SP UPDATED', $event->getTitle());

        $event->delete();
    }


    protected function getCachedCalendar()
    {
        if (null === $this->calendar) {
            $this->calendar = new ExchangeCalendar(
                getenv('TEST_EX_SERVER'),
                getenv('TEST_EX_USER'),
                getenv('TEST_EX_PASSWORD'),
                getenv('TEST_EX_VERSION'),
                getenv('TEST_EX_FOLDER')
            );
        }
        return $this->calendar;
    }


}
