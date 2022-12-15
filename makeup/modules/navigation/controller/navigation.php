<?php

use makeUp\lib\Module;
use makeUp\lib\RQ;
use makeUp\lib\Routing;
use makeUp\lib\Session;
use makeUp\lib\Tools;


class Navigation extends Module
{
    public function __construct()
    {
        parent::__construct();
    }

    public function build(): string
    {
        $mainTmpl = $this->getTemplate();

        // Init slices:
        $menuNoSubSlice = $mainTmpl->getSlice("{{MENU_NO_SUB}}");
        $s["{{MENU_NO_SUB}}"] = "";
        $menuHasSubSlice = $mainTmpl->getSlice("{{MENU_HAS_SUB}}");
        $s["{{MENU_HAS_SUB}}"] = "";
        $icon = $mainTmpl->getSlice("{{OI_ICON}}");
        $s["{{OI_ICON}}"] = "";

        $m = [];
        $m["[[MENU_ITEMS]]"] = "";

        $routing = Routing::getConfig();

        foreach ($routing as $data) {
            // Main menu:
            if (!isset($data->submenu)) {
                if (@$data->protected != 1 || (@$data->protected == 1 && $this->checkLogin())) {
                    $m["[[MENU_ITEMS]]"] .= $menuNoSubSlice->parse([
                        "[[ACTIVE]]" => RQ::GET("mod") == @$data->module ? "active" : "",
                        "[[MOD_NAME]]" => @$data->module ? @$data->module : "",
                        "[[LINK]]" => @$data->module ? Tools::linkBuilder(@$data->module, @$data->task) : "",
                        "[[TEXT]]" => $data->text,
                        "[[ICON]]" => @$data->icon ? $icon->parse([
                            "[[NAME]]" => @$data->icon
                        ]) : ""
                    ]);
                }
            }
            // With submenu:
            else {
                if (@$data->protected != 1 || (@$data->protected == 1 && $this->checkLogin())) {
                    $m["[[MENU_ITEMS]]"] .= $menuHasSubSlice->parse([
                        "[[MOD_NAME]]" => @$data->module ? @$data->module : "",
                        "[[LINK]]" => @$data->module ? Tools::linkBuilder(@$data->module, @$data->task) : "",
                        "[[TEXT]]" => $data->text,
                        "[[ICON]]" => @$data->icon ? $icon->parse([
                            "[[NAME]]" => @$data->icon
                        ]) : "",
                        "[[SUBMENU]]" => $this->submenu($data)
                    ]);
                }
            }
        }

        return $mainTmpl->parse($m, $s);
    }

    private function submenu($data): string
    {
        $subMenuTmpl = $this->getTemplate("navigation.sub.html");

        // Init slices:
        $subMenuNoSubSlice = $subMenuTmpl->getSlice("{{SUBMENU_NO_SUB}}");
        $ss["{{SUBMENU_NO_SUB}}"] = "";
        $subMenuHasSubSlice = $subMenuTmpl->getSlice("{{SUBMENU_HAS_SUB}}");
        $ss["{{SUBMENU_HAS_SUB}}"] = "";
        $separator = $subMenuTmpl->getSlice("{{SEPARATOR}}");
        $ss["{{SEPARATOR}}"] = "";
        $header = $subMenuTmpl->getSlice("{{HEADER}}");
        $ss["{{HEADER}}"] = "";
        $icon = $subMenuTmpl->getSlice("{{OI_ICON}}");
        $ss["{{OI_ICON}}"] = "";

        if (@$data->module) {
            // Dropdown item
            if (@$data->protected != 1 || (@$data->protected == 1 && $this->checkLogin())) {
                $ss["{{SUBMENU_NO_SUB}}"] .= $subMenuNoSubSlice->parse([
                    "[[MOD_NAME]]" => @$data->module ? @$data->module : "",
                    "[[LINK]]" => @$data->module ? Tools::linkBuilder(@$data->module, @$data->task) : "",
                    "[[ACTIVE]]" => @$data->module == RQ::get("mod") ? "active" : "",
                    "[[TEXT]]" => $data->text,
                    "[[ICON]]" => ""
                ]);
                // With separator
                $ss["{{SUBMENU_NO_SUB}}"] .= $separator->parse();
            }
        }


        foreach ($data->submenu as $subData) {
            $sliceMarker = isset($subData->submenu) ? "{{SUBMENU_HAS_SUB}}" : "{{SUBMENU_NO_SUB}}";

            // Separator
            if (@$subData->separate) {
                $ss[$sliceMarker] .= $separator->parse();
            }

            // Header
            if (@$subData->header) {
                $ss[$sliceMarker] .= $header->parse([
                    "[[TEXT]]" => @$subData->header
                ]);
            }

            // Module
            if (@$subData->module) {
                if (@$subData->protected != 1 || (@$subData->protected == 1 && $this->checkLogin())) {
                    $markers = [
                        "[[MOD_NAME]]" => @$subData->module ? @$subData->module : "",
                        "[[LINK]]" => @$subData->module ? Tools::linkBuilder(@$subData->module, @$subData->task) : "",
                        "[[ACTIVE]]" => @$subData->module == RQ::get("mod") ? "active" : "",
                        "[[TEXT]]" => $subData->text,
                        "[[ICON]]" => @$subData->icon ? $icon->parse([
                            "[[NAME]]" => @$subData->icon
                        ]) : ""
                    ];
    
                    // Main menu:
                    if (!isset($subData->submenu)) {
                        $ss[$sliceMarker] .= $subMenuNoSubSlice->parse($markers);
                    }
                    // With submenu:
                    else {
                        $markers["[[SUBMENU]]"] = $this->submenu($subData);
                        $ss[$sliceMarker] .= $subMenuHasSubSlice->parse($markers);
                    }
                }
            }
        }

        return $subMenuTmpl->parse([], $ss);
    }

}