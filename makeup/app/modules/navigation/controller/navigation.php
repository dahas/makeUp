<?php

use makeup\lib\Module;
use makeup\lib\RQ;
use makeup\lib\Routing;
use makeup\lib\Config;
use makeup\lib\Tools;
use makeup\lib\Template;

/**
 * This is a systeem module
 */
class Navigation extends Module
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Build before rendering
     *
     * @param string $modName
     * @return string
     */
    public function build($modName = "")
    {
        $mainTmpl = $this->getTemplate();

        // Init slices:
        $menuNoSubSlice = $mainTmpl->getSlice("##MENU_NO_SUB##");
        $s["##MENU_NO_SUB##"] = "";
        $menuHasSubSlice = $mainTmpl->getSlice("##MENU_HAS_SUB##");
        $s["##MENU_HAS_SUB##"] = "";
        $icon = $mainTmpl->getSlice("##OI_ICON##");
        $s["##OI_ICON##"] = "";

        $m = [];
        $m["##MENU_ITEMS##"] = "";

        $routing = Routing::getConfig();
        
        foreach ($routing as $item => $data)
        {
            // Main menu:
            if (!isset($data["submenu"])) {
                $m["##MENU_ITEMS##"] .= $menuNoSubSlice->parse([
                    "##ACTIVE##" => RQ::GET("mod") == $data["module"] ? "active" : "",
                    "##LINK##" => $data["route"],
                    "##TEXT##" => $data["text"],
                    "##ICON##" => $icon->parse([
                        "##NAME##" => $data["icon"]
                    ])
                ]);
            } 
            // With submenu:
            else {
                $m["##MENU_ITEMS##"] .= $menuHasSubSlice->parse([
                    "##LINK##" => $data["route"],
                    "##TEXT##" => $data["text"],
                    "##ICON##" => $data["icon"] ? $icon->parse([
                        "##NAME##" => $data["icon"]
                    ]) : "",
                    "##SUBMENU##" => $this->submenu($data, $data["show_open"])
                ]);
            }
        }

        return $mainTmpl->parse($m, $s);
    }

    /**
	 * Create the submenu items
	 *
	 * @return string HTML
	 */
	private function submenu($data, $showOpen = true, $showHeader = true)
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
        $icon = $subMenuTmpl->getSlice("##OI_ICON##");
        $ss["##OI_ICON##"] = "";

        // Open item
        if ($showOpen) {
            $ss["##SUBMENU_NO_SUB##"] .= $subMenuNoSubSlice->parse([
                "##LINK##" => $data["route"],
                "##TEXT##" => "Open",
                "##ICON##" => ""
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
                "##TEXT##" => $subData["text"],
                "##ICON##" => $subData["icon"] ? $icon->parse([
                    "##NAME##" => $subData["icon"]
                ]) : ""
            ];

            // Main menu:
            if (!isset($subData["submenu"])) {
                $ss[$sliceMarker] .= $subMenuNoSubSlice->parse($markers);
            } 
            // With submenu:
            else {
                $markers["##SUBMENU##"] = $this->submenu($subData, $subData["show_open"], true);
                $ss[$sliceMarker] .= $subMenuHasSubSlice->parse($markers);
            }
        }

        return $subMenuTmpl->parse([], $ss);
	}

}
