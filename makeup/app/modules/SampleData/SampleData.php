<?php

use makeUp\lib\Auth;
use makeup\src\Module;
use makeUp\src\attributes\Inject;
use makeUp\src\Request;
use makeUp\lib\Template;


class SampleData extends Module {

    #[Inject('SampleService')]
    protected $SampleService;


    protected function build(Request $request): string
    {
        $m = [];

        if ($this->SampleService->isAvailable()) {
            $count = $this->SampleService->read(where: "deleted=0");
            $template = Template::load("SampleData");
            $m["[[DATA-MOD]]"] = "SampleData";
            $m["[[LIST]]"] = $this->list();
            $html = $template->parse($m);
        } else {
            $template = Template::load("SampleData", "SampleData.nodb.html");
            $html = $template->parse();
        }

        return $this->render($html);
    }


    public function list(): string
    {
        $html = "";
        $Template = Template::load("SampleData", "SampleData.list.html");

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


    public function insert(Request $request): string
    {
        $uid = 0;
        $authorized = false;
        $modelName = "";
        $rowHTML = "";

        if (Auth::check()) {
            $authorized = true;

            $SampleItem = $this->SampleService->create();
            $SampleItem->setProperty("year", $request->getParameter("year"));
            $SampleItem->setProperty("name", $request->getParameter("name"));
            $SampleItem->setProperty("city", $request->getParameter("city"));
            $SampleItem->setProperty("country", $request->getParameter("country"));
            $uid = $SampleItem->store();
    
            $Template = Template::load("SampleData", "SampleData.list.html");
            $rowHTML = $this->renderRow($Template, $uid, $request->getParameter("year"), 
                $request->getParameter("name"), $request->getParameter("city"), 
                $request->getParameter("country"), true);

            $modelName = $request->getParameter("name");
        } 
    
        return json_encode([
            "uid" => $uid,
            "authorized" => $authorized,
            "name" => $modelName,
            "rowHTML" => $rowHTML
        ]);
    }


    public function update(Request $request): string
    {
        $authorized = false;
        $modelName = "";
        $rowHTML = "";

        if (Auth::check()) {
            $authorized = true;

            $SampleItem = $this->SampleService->getByUniqueId($request->getParameter("uid"));
            if ($SampleItem) {
                $SampleItem->setProperty("year", $request->getParameter("year"));
                $SampleItem->setProperty("name", $request->getParameter("name"));
                $SampleItem->setProperty("city", $request->getParameter("city"));
                $SampleItem->setProperty("country", $request->getParameter("country"));
                $SampleItem->update();
            }
    
            $Template = Template::load("SampleData", "SampleData.list.html");
            $rowHTML = $this->renderRow($Template, $request->getParameter("uid"), 
                $request->getParameter("year"), $request->getParameter("name"), 
                $request->getParameter("city"), $request->getParameter("country"));

            $modelName = $request->getParameter("name");
        }

        return json_encode([
            "authorized" => $authorized,
            "name" => $modelName,
            "rowHTML" => $rowHTML
        ]);
    }


    public function delete(Request $request): string
    {
        $authorized = false;
        $update = false;
        $uid = 0;
        $modelName = "";

        if (Auth::check()) {
            $authorized = true;

            $SampleItem = $this->SampleService->getByUniqueId($request->getParameter("uid"));
            $SampleItem->setProperty("deleted", 1);
            $update = $SampleItem->update();

            $uid = $request->getParameter("uid");
            $modelName = $SampleItem->getProperty("name");
        }

        return json_encode([
            "success" => $update,
            "authorized" => $authorized,
            "uid" => $uid,
            "name" => $modelName
        ]);
    }


    public function getItem(Request $request): string
    {
        $SampleItem = $this->SampleService->getByUniqueId($request->getParameter("uid"));
        $props = $SampleItem->getProperties();
        return json_encode([
            ...$props,
            "authorized" => Auth::check()
        ]);
    }

}