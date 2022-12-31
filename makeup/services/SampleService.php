<?php

namespace makeUp\services;

use makeUp\lib\attributes\Data;
use makeUp\lib\Service;
use makeUp\lib\ServiceItem;


#[Data(
    table: 'sampledata', 
    key: 'uid', 
    columns: 'uid, name, year, city, country'
)]
class SampleService extends Service
{
    public function __construct()
    {
        parent::__construct();
    }
}


class SampleServiceItem extends ServiceItem
{

}
