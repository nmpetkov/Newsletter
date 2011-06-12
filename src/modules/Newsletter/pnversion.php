<?php
/**
 * Newletter Module for Zikula
 *
 * @copyright © 2001-2010, Devin Hayes (aka: InvalidReponse), Dominik Mayer (aka: dmm), Robert Gasch (aka: rgasch)
 * @link http://www.zikula.org
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

$dom = ZLanguage::getModuleDomain('Newsletter');

$modversion['version']     = '2.1.0';
$modversion['displayname'] = __('Newsletter', $dom);
$modversion['description'] = __('Newsletter extension for Zikula.', $dom);
//! module URL must be in lowercase and different to displayname
$modversion['url']         = __('Newsletter', $dom);

$modversion['changelog'] = 'pndocs/changelog.txt';
$modversion['credits']   = 'pndocs/credits.txt';
$modversion['help']      = 'pndocs/install.txt';
$modversion['license']   = 'pndocs/license.txt';
$modversion['official']  = 0;
$modversion['author']    = 'D. Hayes, D. Mayer, R. Gasch';
$modversion['contact']   = 'http://community.zikula.org, http://support.zikula.de';
$modversion['admin']     = 1;

$modversion['securityschema'] = array('Newsletter::' => '::');