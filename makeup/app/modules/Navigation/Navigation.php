<?php

use makeUp\lib\Auth;
use makeUp\lib\Template;
use makeUp\src\Module;
use makeUp\src\Menu;
use makeUp\src\Request;


class Navigation extends Module {

    public function build(Request $request): string
    {
        $modName = $request->getModule();
        $mainTmpl = Template::load("Navigation");

        // Init slices:
        $menuNoSubSlice = $mainTmpl->getSlice("{{MENU_NO_SUB}}");
        $menuHasSubSlice = $mainTmpl->getSlice("{{MENU_HAS_SUB}}");
        $icon = $mainTmpl->getSlice("{{OI_ICON}}");

        $html = "";

        $menuConf = Menu::getConfig();

        foreach ($menuConf as $data) {
            // Main menu:
            if (!isset($data->submenu)) {
                if (@$data->protected != 1 || (@$data->protected == 1 && Auth::check())) {
                    if (@$data->module) {
                        $module = @$data->module ? @$data->module : "";
                        $task = @$data->task ? @$data->task : "";
                        $routeHandler = "setRoute('$module', '$task', this);";
                    } else {
                        $routeHandler = "void(0);";
                    }
                    $html .= $menuNoSubSlice->parse([
                        "[[ACTIVE]]" => $modName == @$data->module ? "active" : "",
                        "[[ROUTE_HANDLER]]" => $routeHandler,
                        "[[TEXT]]" => $data->text,
                        "[[ICON]]" => @$data->icon ? $icon->parse([
                            "[[NAME]]" => @$data->icon
                        ]) : ""
                    ]);
                }
            }
            // With submenu:
            else {
                if (@$data->protected != 1 || (@$data->protected == 1 && Auth::check())) {
                    if (@$data->module) {
                        $module = @$data->module ? @$data->module : "";
                        $task = @$data->task ? @$data->task : "";
                        $routeHandler = "setRoute('$module', '$task', this);";
                    } else {
                        $routeHandler = "void(0);";
                    }
                    $html .= $menuHasSubSlice->parse([
                        "[[ROUTE_HANDLER]]" => $routeHandler,
                        "[[TEXT]]" => $data->text,
                        "[[ICON]]" => @$data->icon ? $icon->parse([
                            "[[NAME]]" => @$data->icon
                        ]) : "",
                        "[[SUBMENU]]" => $this->submenu($data, $modName)
                    ]);
                }
            }
        }

        return $this->render($html);
    }

    private function submenu($data, string $modName): string
    {
        $subMenuTmpl = Template::load("Navigation", "Navigation.sub.html");

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
            if (@$data->protected != 1 || (@$data->protected == 1 && Auth::check())) {
                if (@$data->module) {
                    $module = @$data->module ? @$data->module : "";
                    $task = @$data->task ? @$data->task : "";
                    $routeHandler = "setRoute('$module', '$task', this);";
                } else {
                    $routeHandler = "void(0);";
                }
                $ss["{{SUBMENU_NO_SUB}}"] .= $subMenuNoSubSlice->parse([
                    "[[ROUTE_HANDLER]]" => $routeHandler,
                    "[[ACTIVE]]" => @$data->module == $modName ? "active" : "",
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
                if (@$subData->protected != 1 || (@$subData->protected == 1 && Auth::check())) {
                    if (@$subData->module) {
                        $module = @$subData->module ? @$subData->module : "";
                        $task = @$subData->task ? @$subData->task : "";
                        $routeHandler = "setRoute('$module', '$task', this);";
                    } else {
                        $routeHandler = "void(0);";
                    }
                    $markers = [
                        "[[ROUTE_HANDLER]]" => $routeHandler,
                        "[[ACTIVE]]" => @$subData->module == $modName ? "active" : "",
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
                        $markers["[[SUBMENU]]"] = $this->submenu($subData, $modName);
                        $ss[$sliceMarker] .= $subMenuHasSubSlice->parse($markers);
                    }
                }
            }
        }

        return $subMenuTmpl->parse([], $ss);
    }

}