<?php


namespace Peregrinus\Calendars\SharePoint;


use Carbon\Carbon;
use Thybag\SharePointAPI;

class SharePointCalendarItem
{

    protected $ID = 0;

    /** @var Carbon Start date */
    protected $eventdate = null;

    /** @var Carbon End date */
    protected $enddate = null;

    /** @var int Recurrence */
    protected $fRecurrence = 0;

    /** @var int Event type */
    protected $eventType = 0;

    /** @var string Title */
    protected $title = '';

    /** @var string Location */
    protected $location = '';

    /** @var bool All day event */
    protected $fAllDayEvent = false;

    /** @var string Description */
    protected $description = '';

    /** @var string Category */
    protected $category = '';

    protected $exports = [
        'EventDate' => 'eventdate',
        'EndDate' => 'enddate',
        'fRecurrence' => 'fRecurrence',
        'EventType' => 'eventType',
        'Title' => 'title',
        'Location' => 'location',
        'fAllDayEvent' => 'fAllDayEvent',
        'Description' => 'description',
        'Category' => 'category',
    ];

    protected $dates = [
        'eventdate',
        'enddate'
    ];

    /** @var SharePointCalendar */
    protected $calendar = null;

    public function __construct($data = [], $calendar = null)
    {
        $this->setPropertiesFromArray($data);
        if (null !== $calendar) $this->setCalendar($calendar);
    }

    protected function setPropertiesFromArray($data) {
        foreach ($data as $key => $datum) {
            if (property_exists($this, $key)) {
                $this->setProperty($key, $datum);
            } else {
                foreach ($this->exports as $spKey => $myKey) {
                    if ($spKey == $key) {
                        $this->setProperty($myKey, $datum);
                    }
                }
            }
        }
        if (isset($data['id'])) {
            $this->setID($data['id']);
        }
    }

    protected function setProperty($property, $datum)
    {
        if (!in_array($property, $this->dates)) {
            $this->$property = $datum;
        } else {
            $this->$property = new Carbon($datum);
        }
    }

    public function toArray()
    {
        $data = [];
        foreach ($this->exports as $spKey => $key) {
            if (is_bool($this->$key)) {
                $data[$spKey] = $this->$key ? 1 : 0;
            } elseif (is_a($this->$key, Carbon::class)) {
                $data[$spKey] = SharePointAPI::dateTime($this->$key->format('Y-m-d H:i:s'));
            } else {
                $data[$spKey] = $this->$key;
            }
        }
        return $data;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->ID;
    }

    /**
     * @param int $ID
     */
    public function setID(
        int $ID
    ): void {
        $this->ID = $ID;
    }

    /**
     * @return Carbon
     */
    public function getEventdate(): Carbon
    {
        return $this->eventdate;
    }

    /**
     * @param Carbon $eventdate
     */
    public function setEventdate(
        Carbon $eventdate
    ): void {
        $this->eventdate = $eventdate;
    }

    /**
     * @return Carbon
     */
    public function getEnddate(): Carbon
    {
        return $this->enddate;
    }

    /**
     * @param Carbon $enddate
     */
    public function setEnddate(
        Carbon $enddate
    ): void {
        $this->enddate = $enddate;
    }

    /**
     * @return int
     */
    public function getFRecurrence(): int
    {
        return $this->fRecurrence;
    }

    /**
     * @param int $fRecurrence
     */
    public function setFRecurrence(
        int $fRecurrence
    ): void {
        $this->fRecurrence = $fRecurrence;
    }

    /**
     * @return int
     */
    public function getEventType(): int
    {
        return $this->eventType;
    }

    /**
     * @param int $eventType
     */
    public function setEventType(
        int $eventType
    ): void {
        $this->eventType = $eventType;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(
        string $title
    ): void {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(
        string $location
    ): void {
        $this->location = $location;
    }

    /**
     * @return bool
     */
    public function isFAllDayEvent(): bool
    {
        return $this->fAllDayEvent;
    }

    /**
     * @param bool $fAllDayEvent
     */
    public function setFAllDayEvent(
        bool $fAllDayEvent
    ): void {
        $this->fAllDayEvent = $fAllDayEvent;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(
        string $description
    ): void {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory(
        string $category
    ): void {
        $this->category = $category;
    }

    /**
     * @return string[]
     */
    public function getExports(): array
    {
        return $this->exports;
    }

    /**
     * @param string[] $exports
     */
    public function setExports(array $exports): void
    {
        $this->exports = $exports;
    }

    /**
     * @return string[]
     */
    public function getDates(): array
    {
        return $this->dates;
    }

    /**
     * @param string[] $dates
     */
    public function setDates(array $dates): void
    {
        $this->dates = $dates;
    }

    /**
     * @return SharePointCalendar
     */
    public function getCalendar(): SharePointCalendar
    {
        return $this->calendar;
    }

    /**
     * @param SharePointCalendar $calendar
     */
    public function setCalendar(SharePointCalendar $calendar): void
    {
        $this->calendar = $calendar;
    }

    public function update(array$data) {
        $this->setPropertiesFromArray($data);
        if (null !== $this->calendar) return $this->calendar->update($this);
        return $this;
    }

    public function delete() {
        if (null !== $this->calendar) $this->calendar->delete($this);
    }

}