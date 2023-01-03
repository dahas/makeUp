<?php

use makeup\lib\Module;
use makeUp\lib\attributes\Inject;
use makeUp\lib\Template;
use makeUp\services\SampleServiceItem;


class SampleData extends Module {

    #[Inject('SampleService')]
    protected $SampleService;


    protected function build(): string
    {
        $m = [];

        $count = $this->SampleService->read(where: "deleted=0");

        if ($count) {
            $template = $this->getTemplate("SampleData.html");
            $m["[[DATA-MOD]]"] = "SampleData";
            $m["[[LIST]]"] = $this->list();
            $html = $template->parse($m);
        } else {
            $template = $this->getTemplate("SampleData.nodb.html");
            $html = $template->parse();
        }

        return $this->render($html);
    }


    public function list(): string
    {
        $html = "";
        $Template = $this->getTemplate("SampleData.list.html");

        $this->SampleService->read(where: "deleted=0", orderBy: "year DESC");
        if ($this->SampleService->count() > 0) {
            while ($Data = $this->SampleService->next()) {
                $html .= $this->renderRow(
                    $Template,
                    $Data->getProperty("uid"),
                    $Data->getProperty("year"),
                    $Data->getProperty("name"),
                    $Data->getProperty("city"),
                    $Data->getProperty("country")
                );
            }
        }

        return $html;
    }


    public function renderRow(Template $Template, int $uid, int $year, string $name, string $city, string $country, bool $insert = false): string
    {
        $m["[[INSERT_CLASS]]"] = "";

        $m["[[UID]]"] = $uid;
        $m["[[YEAR]]"] = $year;
        $m["[[NAME]]"] = $name;
        $m["[[CITY]]"] = $city;
        $m["[[COUNTRY]]"] = $country;

        if($insert) {
            $m["[[INSERT_CLASS]]"] = ' class="anim highlight"';
        }

        return $Template->parse($m);
    }


    public function insert(): string
    {
        $data = $this->requestData();

        $SampleItem = $this->SampleService->create();
        $SampleItem->setProperty("year", $data['year']);
        $SampleItem->setProperty("name", $data['name']);
        $SampleItem->setProperty("city", $data['city']);
        $SampleItem->setProperty("country", $data['country']);
        $uid = $SampleItem->store();

        $Template = $this->getTemplate("SampleData.list.html");
        $rowHTML = $this->renderRow($Template, $uid, $data['year'], $data['name'], $data['city'], $data['country'], true);

        return json_encode([
            "uid" => $uid,
            "name" => $SampleItem->getProperty("name"),
            "rowHTML" => $rowHTML
        ]);
    }


    public function update(): string
    {
        $data = $this->requestData();

        $SampleItem = $this->SampleService->getByUniqueId($data['uid']);
        if ($SampleItem) {
            $SampleItem->setProperty("year", $data['year']);
            $SampleItem->setProperty("name", $data['name']);
            $SampleItem->setProperty("city", $data['city']);
            $SampleItem->setProperty("country", $data['country']);
            $SampleItem->update();
        }

        $Template = $this->getTemplate("SampleData.list.html");
        $rowHTML = $this->renderRow($Template, $data['uid'], $data['year'], $data['name'], $data['city'], $data['country']);

        return json_encode([
            "name" => $data['name'],
            "rowHTML" => $rowHTML
        ]);
    }


    public function getItem(): string
    {
        $data = $this->requestData();
        $SampleItem = $this->SampleService->getByUniqueId($data['uid']);
        return json_encode($SampleItem->getProperties());
    }


    public function delete(): string
    {
        $params = $this->requestData();

        $SampleItem = $this->SampleService->getByUniqueId($params['uid']);
        $SampleItem->setProperty("deleted", 1);
        $update = $SampleItem->update();

        return json_encode([
            "success" => $update,
            "uid" => $params['uid'],
            "name" => $SampleItem->getProperty("name")
        ]);
    }

}