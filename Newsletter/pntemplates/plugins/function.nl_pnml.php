<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2004, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: function.pnml.php 24342 2008-06-06 12:03:14Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Template_Plugins
 * @subpackage Functions
 */

/**
 * Smarty function to read a Zikula language constant.
 *
 * This function takes a identifier and returns the corresponding language constant.
 *
 * Available parameters:
 *   - name:            Name of the language constant to return
 *   - html:            Treat the language define as HTML
 *   - assign:          If set, the results are assigned to the corresponding variable instead of printed out
 *   - noprocess        If set, no processing is applied to the constant value
 *   - escapeForScript  If set, escape output for using in JavaScript single quotes string
 *   - *                All remaining parameters are used as string replacements
 *
 * Example
 * _EXAMPLESTRING = 'Hello World'
 * <!--[pnml name="_EXAMPLESTRING"]--> returns Hello World
 *
 * _EXAMPLESTRING = 'There are %u% users online';
 *  $usersonline = 10
 * <!--[pnml name=_EXAMPLESTRING u=$usersonline]--> returns There are 10 users online
 *
 * @author       Mark West
 * @author       Robert Gasch
 * @since        08/08/2003
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      assign       The smarty variable to assign the resulting menu HTML to
 * @param        string      noprocess    If set the resulting string constant is not processed
 * @return       string      the language constant
 */
function smarty_function_nl_pnml ($params, &$smarty)
{
    $assign          = isset($params['assign'])          ? $params['assign']          : null;
    $html            = isset($params['html'])            ? (bool)$params['html']            : false;
    $name            = isset($params['name'])            ? $params['name']            : null;
    $noprocess       = isset($params['noprocess'])       ? (bool)$params['noprocess']       : false;
    $escapeForScript = isset($params['escapeForScript']) ? $params['escapeForScript'] : null;

    // Avoid passing these to pnMl as variables
    unset($params['name']);
    unset($params['html']);
    unset($params['assign']);
    unset($params['noprocess']);
    unset($params['escapeForScript']);

    if (!$name) {
        $smarty->trigger_error('pnml: parameter [name] required');
        return false;
    }

    $result = NewsletterUtil::encodeText(pnML($name, $params, $html, $noprocess, $escapeForScript));

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}
