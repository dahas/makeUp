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
    private int $uid;
    private string $name;
    private int $year;
    private string $city;
    private string $country;
    private int $deleted;
    

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function setDeleted(int $deleted): void
    {
        $this->deleted = $deleted;
    }


    public function getUid(): string
    {
        return $this->uid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
}
