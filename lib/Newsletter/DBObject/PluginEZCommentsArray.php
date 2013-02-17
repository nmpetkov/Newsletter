<?php
/**
 * Newletter Module for Zikula
 *
 * @copyright  Newsletter Team
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Newsletter
 * @subpackage User
 *
 * Please see the CREDITS.txt file distributed with this source code for further
 * information regarding copyright.
 */

class Newsletter_DBObject_PluginEZCommentsArray extends Newsletter_DBObject_PluginBaseArray
{
    function Newsletter_DBObject_PluginEZCommentsArray($init=null, $where='')
    {
        $this->Newsletter_DBObject_PluginBaseArray();
    }

    // $filtAfterDate is null if is not set, or in format yyyy-mm-dd hh:mm:ss
    function getPluginData($lang=null, $filtAfterDate=null)
    {
        if (!ModUtil::available('EZComments')) {
            return array();
        }

        $enableML = ModUtil::getVar ('Newsletter', 'enable_multilingual', 0);
        $nItems   = ModUtil::getVar ('Newsletter', 'plugin_EZComments_nItems', 1);
        $params   = array();
        $params['order']    = 'items DESC';
        $params['numitems'] = $nItems;
        $params['startnum'] = 0;
        $params['ignoreml'] = true;

        if ($enableML && $lang) {
            $params['ignoreml'] = false;
            $params['language'] = $lang;
        }

        $items = ModUtil::apiFunc('EZComments', 'user', 'getall', $params);

        // filter by date is given, remove older data
        if ($filtAfterDate) {
            foreach (array_keys($items) as $k) {
                if ($items[$k]['date'] < $filtAfterDate) {
                    unset($items[$k]);
                }
            }
        }

        return $items;
    }
}
