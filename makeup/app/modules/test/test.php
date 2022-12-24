<?php

use makeUp\lib\Lang;
use makeup\lib\Module;
use makeUp\lib\attributes\Inject;
use makeUp\lib\RQ;


class Test extends Module
{
    #[Inject('Sampledata')]
    protected $SampleService;


    protected function build() : string
    {
        $m["[[MODULE]]"] = $this->modName;

        $m["[[MOD_CREATED_SUCCESS]]"] = Lang::get("module_created_success");
        $m["[[CONTINUE_LEARNING]]"] = Lang::get("continue_learning");

        $s["{{SAMPLE_DATA}}"] = "";
        $slice = $this->getTemplate()->getSlice("{{SAMPLE_DATA}}");

        $this->SampleService->read(where: "deleted=0");

        if($this->SampleService->count() > 0) {
            $row = $slice->getSlice("{{ROW}}");

            $sm["[[HEADER]]"] = "Sample Data";
            $ss["{{ROW}}"] = "";

            while ($Data = $this->SampleService->next()) {
                $ssm = [];
                $ssm["[[UID]]"] = $Data->getProperty("uid");
                $ssm["[[NAME]]"] = $Data->getProperty("name");
                $ssm["[[CITY]]"] = $Data->getProperty("city");
                $ssm["[[COUNTRY]]"] = $Data->getProperty("country");
                $ss["{{ROW}}"] .= $row->parse($ssm);
            }

            $s["{{SAMPLE_DATA}}"] .= $slice->parse($sm, $ss);
        }

        $html = $this->getTemplate("Test.html")->parse($m, $s);
        return $this->render($html);
    }


	public function delete()
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
