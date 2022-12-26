<?php

use makeUp\lib\Lang;
use makeup\lib\Module;
// use makeUp\lib\attributes\Inject;


class PerfumeMen extends Module
{
    // #[Inject('Sampledata')]
    // protected $SampleService;


    protected function build() : string
    {
        $m["[[MODULE]]"] = $this->modName;

        $m["[[MOD_CREATED_SUCCESS]]"] = Lang::get("module_created_success");
        $m["[[CONTINUE_LEARNING]]"] = Lang::get("continue_learning");

        $s["{{SAMPLE_DATA}}"] = "";
        $slice = $this->getTemplate()->getSlice("{{SAMPLE_DATA}}");

        // To retrieve sample data, connect a database, import 'sample_database.sql' 
        // and enable the Service by removing the comments.

        // $this->SampleService->read();

        // if($this->SampleService->count() > 0) {
        //     $row = $slice->getSlice("{{ROW}}");

        //     $sm["[[HEADER]]"] = "Sample Data";
        //     $ss["{{ROW}}"] = "";

        //     while ($Data = $this->SampleService->next()) {
        //         $ssm = [];
        //         $ssm["[[UID]]"] = $Data->getProperty("uid");
        //         $ssm["[[NAME]]"] = $Data->getProperty("name");
        //         $ssm["[[CITY]]"] = $Data->getProperty("city");
        //         $ssm["[[COUNTRY]]"] = $Data->getProperty("country");
        //         $ss["{{ROW}}"] .= $row->parse($ssm);
        //     }

        //     $s["{{SAMPLE_DATA}}"] .= $slice->parse($sm, $ss);
        // }

        $html = $this->getTemplate("PerfumeMen.html")->parse($m, $s);
        return $this->render($html);
    }


    /**
     * A task is simply a method that is triggered with a request parameter.
     * Like so: "?mod=perfumemen&task=doSomething". Or rewritten: "/perfumemen/doSomething/"
	 */
	public function doSomething()
	{ 
        // Do something ...
        return;
	}

}
