<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Inserts a hidden admin panel controlled by permissions.
 *
 * Inserts required javascript and css files for a hidden admin panel that is triggered by a rendered link.
 * Builds and renders an unordered list of admin-capable modules and their adminLinks using the
 * jQuery.mmenu library <@see http://mmenu.frebsite.nl>
 *
 * This plugin currently has NO configuration options.
 *
 * Examples:
 *
 * <samp>{adminpanelmenu}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return string
 */
function smarty_function_adminpanelmenu($params, Zikula_View $view)
{
    if (!SecurityUtil::checkPermission('ZikulaAdminModule::', "::", ACCESS_EDIT)) {
        return ''; // Since no permission, return empty
    }

    // add required scritps and stylesheets to page
    PageUtil::addVar('javascript', '@ZikulaAdminModule/Resources/public/js/jQuery.mmenu-master/dist/js/jquery.mmenu.min.all.js');
    PageUtil::addVar('stylesheet', '@ZikulaAdminModule/Resources/public/js/jQuery.mmenu-master/dist/css/jquery.mmenu.all.css');
    // add override for panel width created from .scss file
    PageUtil::addVar('stylesheet', '@ZikulaAdminModule/Resources/public/css/mmenu-hiddenpanel-customwidth.css');

    $modules = ModUtil::getModulesCapableOf('admin');
    // sort modules by displayname
    $moduleNames = array();
    foreach ($modules as $key => $module) {
        $moduleNames[$key] = $module['displayname'];
    }
    array_multisort($moduleNames, SORT_ASC, $modules);

    // create unordered list of admin-capable module links
    $htmlContent = '<nav id="zikula-admin-hiddenpanel-menu">';
    $htmlContent .= '<div class="text-left">';
    $htmlContent .= '<h1><img src="images/logo.gif" height="32px"> ' . __('Administration') . '</h1>';
    $htmlContent .= '<ul>';
    foreach ($modules as $module) {
        if (SecurityUtil::checkPermission("module[name]::", '::', ACCESS_EDIT)) {
            // first-level list - list modules with general 'index' link
            $img = ModUtil::getModuleImagePath($module['name']);
            $url = ModUtil::url($module['name'], 'admin', 'index');
            $moduleSelected = empty($moduleSelected) && strpos($view->getRequest()->getUri(), $module['url']) ? " class='Selected'" : "";
            $htmlContent .= "<li{$moduleSelected}><a href='$url'><img src='$img' height='18px' /> " . $module['displayname'] . "</a>";
            $htmlContent .= "<ul class='text-left'>";
            $links = (array)ModUtil::apiFunc($module['name'], 'admin', 'getLinks');
            // create second-level list from module adminLinks
            foreach ($links as $link) {
                if (isset($link['icon'])) {
                    $img = "<i class='fa fa-$link[icon]'></i>";
                } elseif (isset($link['class'])) {
                    $img = "<span class='$link[class]'></span>";
                } else {
                    $img = '';
                }
                $linkSelected = empty($linkSelected) && strpos($view->getRequest()->getUri(), $link['url']) ? " class='Selected'" : "";
                $htmlContent .= "<li{$linkSelected}><a href='$link[url]'>$img $link[text]</a>";
                // create third-level list from adminLinks subLinks
                if (isset($link['links'])) {
                    $htmlContent .= "<ul class='text-left'>";
                    foreach ($link['links'] as $sublink) {
                        $htmlContent .= "<li><a href='$sublink[url]'>$sublink[text]</a></li>";
                    }
                    $htmlContent .= '</ul>';
                }
                $htmlContent .= "</li>";
            }
            $htmlContent .= "</ul>";
            $htmlContent .= "</li>";
        }
    }
    $htmlContent .= "</ul>";
    $htmlContent .= "</div>";
    $htmlContent .= "</nav>";
    $htmlContent .= '
            <script type="text/javascript">
                jQuery(document).ready(function( $ ){
                    $("#zikula-admin-hiddenpanel-menu").mmenu({
                        extensions: ["hiddenpanel-customwidth"],
                        "header": {
                           "title": "' . __('Zikula Administration') . '",
                           "add": true,
                           "update": true
                        },
                        "searchfield": true
                    });
                });
            </script>';

    // the the html content before </body>
    PageUtil::addVar('footer', $htmlContent);

    // display the control link
    return '<a href="#zikula-admin-hiddenpanel-menu"><i class="fa fa-bars"></i></a>';
}