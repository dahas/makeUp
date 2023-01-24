<?php

namespace makeUp\services;

use makeUp\src\attributes\Data;
use makeUp\src\Service;
use makeUp\src\ServiceItem;


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
