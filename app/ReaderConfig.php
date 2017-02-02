<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Martindevnow\Smartshelf\Engineering\Reader;

class ReaderConfig {

    public $reader_mac;

    /** @var  Carbon $time */
    public $time;

    public $inventoryData = [];
    public $pushers = [];

    private $reader;

    public function __construct($reader_mac, $timestamp = null)
    {
        if ($timestamp == null)
            $this->time = Carbon::createFromTimestamp(time())
                ->startOfDay()
                ->subDays(1)
                ->addHours(8);
        else
            $this->time = Carbon::createFromTimestamp($timestamp);


        $this->reader_mac = $reader_mac;
    }

    public function addPusherLine($tray_tag, $shelf_no = "FS01", $upc = "053000549231", $location_no = "01", $tottag = 6)
    {
        $this->pushers [] = compact('tray_tag', 'shelf_no', 'upc', 'location_no', 'tottag');
    }

    public function exportReaderPOG()
    {
        $reader_mac = $this->reader_mac;
        $timestamp = $this->time->timestamp;
        $header [] = compact('reader_mac', 'timestamp');
        $body = $this->pushers;

        return array_merge($header, $body);
    }

    public function addInventoryData($tag, $tags_blocked, $paddle_exposed)
    {
        $this->inventoryData[$tag] = compact('tags_blocked', 'paddle_exposed');
    }

    public function exportInventoryData()
    {
        $reader_mac = $this->reader_mac;
        $timestamp = $this->time->timestamp;
        $header [] = compact('reader_mac', 'timestamp');

        $body = array();
        foreach ($this->inventoryData as $tag => $vals)
        {
            $body[] = [
                'tray_tag'          => $tag,
                'data_TAGSBLKED'    => $vals['tags_blocked'],
                'paddle_exposed'    => $vals['paddle_exposed'],
            ];
        }
        return array_merge($header, $body);
    }

    /**
     * Return the Eloquent Model of the Reader
     *
     * @return Reader
     */
    public function getReader() {
        if ($this->reader instanceof Reader)
            return $this->reader;

        return $this->reader = Reader::findByMacAddress($this->reader_mac);
    }

    /**
     * Return the Collection of Pushers associated to this Reader
     *
     * @return Collection
     */
    public function getPushers() {
        return $this->getReader()->pushers;
    }

//    public function uploadPlanogram($uri = '/api/reader/setup')
//    {
//        $jsonData = json_encode($this->exportReaderPOG());
//        $ch = curl_init( url($uri) );
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//                'Content-Type: application/json',
//                'Content-Length: ' . strlen($jsonData))
//        );
//        return curl_exec($ch);
//    }



}