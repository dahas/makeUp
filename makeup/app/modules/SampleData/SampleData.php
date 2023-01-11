<?php

use makeup\lib\Module;
use makeUp\lib\attributes\Inject;
use makeUp\lib\Template;


class SampleData extends Module {

    #[Inject('SampleService')]
    protected $SampleService;


    protected function build(): string
    {
        $m = [];

        if ($this->SampleService->isAvailable()) {
            $count = $this->SampleService->read(where: "deleted=0");
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

        $uid = 0;
        $authorized = false;
        $modelName = "";
        $rowHTML = "";

        if (Module::checkLogin()) {
            $authorized = true;

            $SampleItem = $this->SampleService->create();
            $SampleItem->setProperty("year", $data['year']);
            $SampleItem->setProperty("name", $data['name']);
            $SampleItem->setProperty("city", $data['city']);
            $SampleItem->setProperty("country", $data['country']);
            $uid = $SampleItem->store();
    
            $Template = $this->getTemplate("SampleData.list.html");
            $rowHTML = $this->renderRow($Template, $uid, $data['year'], $data['name'], $data['city'], $data['country'], true);

            $modelName = $data['name'];
        } 
    
        return json_encode([
            "uid" => $uid,
            "authorized" => $authorized,
            "name" => $modelName,
            "rowHTML" => $rowHTML
        ]);
    }


    public function update(): string
    {
        $data = $this->requestData();
        
        $authorized = false;
        $modelName = "";
        $rowHTML = "";

        if (Module::checkLogin()) {
            $authorized = true;

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

            $modelName = $data['name'];
        }

        return json_encode([
            "authorized" => $authorized,
            "name" => $modelName,
            "rowHTML" => $rowHTML
        ]);
    }


    public function delete(): string
    {
        $params = $this->requestData();

        $authorized = false;
        $update = false;
        $uid = 0;
        $modelName = "";

        if (Module::checkLogin()) {
            $authorized = true;

            $SampleItem = $this->SampleService->getByUniqueId($params['uid']);
            $SampleItem->setProperty("deleted", 1);
            $update = $SampleItem->update();

            $uid = $params['uid'];
            $modelName = $SampleItem->getProperty("name");
        }

        return json_encode([
            "success" => $update,
            "authorized" => $authorized,
            "uid" => $uid,
            "name" => $modelName
        ]);
    }


    public function getItem(): string
    {
        $data = $this->requestData();
        $SampleItem = $this->SampleService->getByUniqueId($data['uid']);
        $props = $SampleItem->getProperties();
        return json_encode([
            ...$props,
            "authorized" => Module::checkLogin()
        ]);
    }

}