<?php

use makeup\lib\Module;
use makeUp\lib\attributes\Inject;


class SampleData extends Module {
    
    #[Inject('SampleService')]
    protected $SampleService;


    protected function build(): string
    {
        $m = []; $s = [];

        $template = $this->getTemplate("SampleData.html");
        $this->SampleService->read(where: "deleted=0");

        $m["[[DATA-MOD]]"] = "SampleData";
        $m["[[LIST]]"] = $this->list();

        $html = $template->parse($m);
        return $this->render($html);
    }


    public function list(): string
    {
        $m = [];

        $html = "";

        $template = $this->getTemplate("SampleData.list.html");
        $this->SampleService->read(where: "deleted=0");

        if ($this->SampleService->count() > 0) {
            while ($Data = $this->SampleService->next()) {
                $m["[[UID]]"] = $Data->getProperty("uid");
                $m["[[NAME]]"] = $Data->getProperty("name");
                $m["[[CITY]]"] = $Data->getProperty("city");
                $m["[[COUNTRY]]"] = $Data->getProperty("country");
                $html .= $template->parse($m);
            }
        }

        return $html;
    }


    public function add(): string
    {
        $m = [];
        $s = [];
        $template = $this->getTemplate("SampleData.add.html");
        $html = $template->parse($m, $s);

        return $this->render($html);
    }


    public function insert(): string
    {
        $data = $this->requestData();
        $Item = $this->SampleService->create($data['name'], intval($data['age']), $data['city'], $data['country']);
        return $Item->getProperty("uid");
    }


    public function delete(): string
    {
        $params = Module::requestData();
        $Item = $this->SampleService->getByUniqueId($params['uid']);
        $Item->setProperty("deleted", 1);
        $update = $Item->update();

        return json_encode([
            "success" => $update,
            "uid" => $params['uid']
        ]);
    }

}