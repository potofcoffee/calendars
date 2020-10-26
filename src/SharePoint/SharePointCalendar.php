<?php


namespace Peregrinus\Calendars\SharePoint;


use Thybag\SharePointAPI;

class SharePointCalendar extends \Peregrinus\Calendars\AbstractCalendar
{

    /** @var SharePointAPI Internal Sharepoint API */
    protected $api = null;

    /** @var string Url */
    protected $url = '';

    /** @var string User name */
    protected $user = '';

    /** @var string Password */
    protected $password = '';

    /** @var string Cached WSDL file */
    protected $wsdlFile = '';

    /** @var string List name */
    protected $listName = '';

    public function __construct($url, $user = '', $password = '')
    {
        $this->setUrl($url);
        $this->setUser($user);
        $this->setPassword($password);
        $this->loadWsdl();
        $this->setApi(new SharePointAPI($user, $password, $this->wsdlFile, 'NTLM'));
    }

    public function __destruct()
    {
        if (file_exists($this->wsdlFile)) unlink ($this->wsdlFile);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return SharePointAPI
     */
    public function getApi(): SharePointAPI
    {
        return $this->api;
    }

    /**
     * @param SharePointAPI $api
     */
    public function setApi(SharePointAPI $api): void
    {
        $this->api = $api;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    protected function getBaseUrl()
    {
        $url = $this->getUrl();
        if (false !== ($x = strpos($url, '/Lists'))) {
            $url = substr($url, 0, $x);
        }
        return $url;
    }

    protected function getWsdlUrl()
    {
        return $this->getBaseUrl() . '/_vti_bin/Lists.asmx?WSDL';
    }

    protected function loadWsdl() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getWsdlUrl());
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, $this->getUser().':'.$this->getPassword());
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CAINFO, "./cacert.pem");
        $return = curl_exec($ch);
        $info = curl_getinfo($ch);
        if ($info['http_code'] != 200) echo print_r($info,1 ).PHP_EOL;
        curl_close($ch);


        $tmpFile = tempnam(sys_get_temp_dir(), 'wsdl');
        file_put_contents($tmpFile, $return);
        $this->wsdlFile = $tmpFile;
        return $tmpFile;
    }

    /**
     * @return string
     */
    public function getWsdlFile(): string
    {
        return $this->wsdlFile;
    }

    /**
     * @param string $wsdlFile
     */
    public function setWsdlFile(string $wsdlFile): void
    {
        $this->wsdlFile = $wsdlFile;
    }


    public function getListName() {
        if ($this->listName != '') return $this->listName;
        $url = $this->getUrl();
        if (false !== ($x = strpos($url, '/Lists/'))) {
            $url = substr($url, $x+7);
            $url = substr($url, 0, strpos($url.'/', '/'));
        }
        $this->listName = $url;
        return $url;
    }

    public function query() {
        return $this->api->query($this->getListName());
    }

    public function all() {
        $results = $this->query()->get();
        if (isset($results[0])) {
            $events = [];
            foreach ($results as $result) {
                $events[] = new SharePointCalendarItem($result, $this);
            }
            return $events;
        } else return null;
    }

    public function find($id) {
        $result = $this->query()->where('ID', '=', $id)->get();
        return isset($result[0]) ? new SharePointCalendarItem($result[0], $this) : null;
    }

    public function create($data) {
        $event = new SharePointCalendarItem($data);
        $result = $this->api->write($this->getListName(), $event->toArray());
        return isset($result[0]) ? new SharePointCalendarItem($result[0], $this) : null;
    }

    public function update(SharePointCalendarItem $event) {
        $result = $this->api->update($this->getListName(), $event->getID(), $event->toArray());
        return isset($result[0]) ? new SharePointCalendarItem($result[0], $this) : null;
    }

    public function delete(SharePointCalendarItem $event) {
        $this->api->delete($this->getListName(), $event->getID());
    }

    public function getMetaData() {
        return $this->api->readListMeta($this->getListName());
    }

    public function getColumns() {
        $columns = [];
        foreach ($this->api->readListMeta($this->getListName(), true, false) as $data) {
            $columns[] = $data['name'];
        }
        return $columns;
    }

}