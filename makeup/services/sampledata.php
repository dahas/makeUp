<?php

namespace makeUp\services;

use makeUp\lib\attributes\Data;
use makeUp\lib\Service;
use makeUp\lib\ServiceItem;


#[Data(
    table: 'sampledata', 
    key: 'uid', 
    columns: 'name, age, city, country'
)]
class Sampledata extends Service
{
    public function __construct()
    {
        parent::__construct();
    }
}


class SampledataItem extends ServiceItem
{

}
