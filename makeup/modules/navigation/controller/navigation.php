<?php

/**
 * Include libraries like below.
 * (Module is mandatory!)
 */
use makeup\lib\Module;
use makeup\lib\RQ;
use makeup\lib\Routing;
use makeup\lib\Config;
use makeup\lib\Tools;
use makeup\lib\Template;

/**
 * Class names of modules always have to be UpperCamelCase.
 * But when you create a module, all chars are lowercase
 * and parts are connected with an underscore: Module::create("lower_case")
 */
class Navigation extends Module
{
    /**
     * Calling the parent constructor is required!
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * This is the manatory default task. It is required
     * to render the template. It returns pure HTML.
     *
     * @param string $modName
     * @return string
     */
    public function build($modName = "")
    {
        // Init slices:
        $menuNoSubSlice = $this->getTemplate()->getSlice("##MENU_NO_SUB##");
        $s["##MENU_NO_SUB##"] = "";
        $menuHasSubSlice = $this->getTemplate()->getSlice("##MENU_HAS_SUB##");
        $s["##MENU_HAS_SUB##"] = "";

        $m = [];
        $m["##MENU_ITEMS##"] = "";

        $routing = Routing::getConfig();

        // Tools::debug($routing);
        
        foreach ($routing as $item => $data)
        {
            // Main menu:
            if (!isset($data["submenu"])) {
                $m["##MENU_ITEMS##"] .= $menuNoSubSlice->parse([
                    "##ACTIVE##" => $data["active"] ? "active" : "",
                    "##LINK##" => $data["route"],
                    "##TEXT##" => $data["text"]
                ]);
            } 
            // With submenu:
            else {
                $m["##MENU_ITEMS##"] .= $menuHasSubSlice->parse([
                    "##LINK##" => "#", // $data["route"],
                    "##TEXT##" => $data["text"],
                    "##SUBMENU##" => $this->submenu($data)
                ]);
            }
        }

        return $this->getTemplate()->parse($m, $s);
    }

    /**
	 * Create the submenu items
	 *
	 * @return string HTML
	 */
	public function submenu($data, $showOpen = true, $showHeader = true)
	{
        $subMenuTmpl = $this->getTemplate("navigation.sub.html");

        // Init slices:
        $subMenuNoSubSlice = $subMenuTmpl->getSlice("##SUBMENU_NO_SUB##");
        $ss["##SUBMENU_NO_SUB##"] = "";
        $subMenuHasSubSlice = $subMenuTmpl->getSlice("##SUBMENU_HAS_SUB##");
        $ss["##SUBMENU_HAS_SUB##"] = "";
        $separator = $subMenuTmpl->getSlice("##SEPARATOR##");
        $ss["##SEPARATOR##"] = "";
        $header = $subMenuTmpl->getSlice("##HEADER##");
        $ss["##HEADER##"] = "";

        // Open item
        if ($showOpen) {
            $ss["##SUBMENU_NO_SUB##"] .= $subMenuNoSubSlice->parse([
                "##LINK##" => $data["route"],
                "##TEXT##" => "Open"
            ]);
            // With separator
            $ss["##SUBMENU_NO_SUB##"] .= $separator->parse();
        }

        foreach ($data["submenu"] as $subItem => $subData) {
            $sliceMarker = isset($subData["submenu"]) ? "##SUBMENU_HAS_SUB##" : "##SUBMENU_NO_SUB##";

            // Separator and Section header
            if ($subData["separate"]) {
                $ss[$sliceMarker] .= $separator->parse();
            }
            if ($subData["header"]) {
                $ss[$sliceMarker] .= $header->parse([
                    "##TEXT##" => $subData["header"]
                ]);
            }

            $markers = [
                "##LINK##" => $subData["route"],
                "##TEXT##" => $subData["text"]
            ];

            // Main menu:
            if (!isset($subData["submenu"])) {
                $ss[$sliceMarker] .= $subMenuNoSubSlice->parse($markers);
            } 
            // With submenu:
            else {
                $markers["##SUBMENU##"] = $this->submenu($subData, true, true);
                $ss[$sliceMarker] .= $subMenuHasSubSlice->parse($markers);
            }
        }

        return $subMenuTmpl->parse([], $ss);
	}

}
