<?php

use makeUp\lib\Lang;
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

        $m["[[DATA-MOD]]"] = $this->getDataMod();
        $s["{{ROW}}"] = "";

        if ($this->SampleService->count() > 0) {
            $row = $template->getSlice("{{ROW}}");

            while ($Data = $this->SampleService->next()) {
                $sm = [];
                $sm["[[UID]]"] = $Data->getProperty("uid");
                $sm["[[NAME]]"] = $Data->getProperty("name");
                $sm["[[CITY]]"] = $Data->getProperty("city");
                $sm["[[COUNTRY]]"] = $Data->getProperty("country");
                $s["{{ROW}}"] .= $row->parse($sm);
            }
        }

        $html = $template->parse($m, $s);
        return $this->render($html);
    }


    public function add(): string
    {
        $m = [];
        $s = [];
        $template = $this->getTemplate("SampleData.add.html");
        $html = $template->parse($m, $s);

        return $this->render($html);
    }


    public function delete(): string
    {
        $params = Module::getParameters();
        $Item = $this->SampleService->getByUniqueId($params['uid']);
        $Item->setProperty("deleted", 1);
        $update = $Item->update();

        return json_encode([
            "success" => $update,
            "uid" => $params['uid']
        ]);
    }

}