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
                $html .= $this->renderRow($Template, $Data);
            }
        }

        return $html;
    }


    public function renderRow(Template $Template, SampleServiceItem $Item): string
    {
        $m["[[UID]]"] = $Item->getProperty("uid");
        $m["[[NAME]]"] = $Item->getProperty("name");
        $m["[[YEAR]]"] = $Item->getProperty("year");
        $m["[[CITY]]"] = $Item->getProperty("city");
        $m["[[COUNTRY]]"] = $Item->getProperty("country");
        return $Template->parse($m);
    }


    public function insert(): string
    {
        $data = $this->requestData();
        $Item = $this->SampleService->create($data['name'], intval($data['year']), $data['city'], $data['country']);
        $Template = $this->getTemplate("SampleData.list.html");
        $rowHTML = $this->renderRow($Template, $Item);
        $result = [
            "uid" => $Item->getProperty("uid"),
            "name" => $Item->getProperty("name"),
            "rowHTML" => $rowHTML
        ];
        return json_encode($result);
    }


    public function update(): string
    {
        $data = $this->requestData();
        $Item = $this->SampleService->getByUniqueId($data['uid']);
        if ($Item) {
            $Item->setProperty("year", $data['year']);
            $Item->setProperty("name", $data['name']);
            $Item->setProperty("city", $data['city']);
            $Item->setProperty("country", $data['country']);
            $Item->update();
        }
        $Template = $this->getTemplate("SampleData.list.html");
        $rowHTML = $this->renderRow($Template, $Item);
        $result = [
            "name" => $data['name'],
            "rowHTML" => $rowHTML
        ];
        return json_encode($result);
    }


    public function getItem(): string
    {
        $data = $this->requestData();
        $Item = $this->SampleService->getByUniqueId($data['uid']);
        return json_encode($Item->getProperties());
    }


    public function delete(): string
    {
        $params = Module::requestData();
        $Item = $this->SampleService->getByUniqueId($params['uid']);
        $Item->setProperty("deleted", 1);
        $update = $Item->update();

        return json_encode([
            "success" => $update,
            "uid" => $params['uid'],
            "name" => $Item->getProperty("name")
        ]);
    }

}