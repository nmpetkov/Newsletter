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

class PNPluginBaseArray extends PNObjectArray 
{
    function PNPluginBaseArray($init=null, $where='')
    {
        $this->PNObjectArray();
    }

    // to be implenented by derived classes
    function getPluginData($lang=null)
    {
        exit('Base class implementation of getPluginData() should not be called ...');
    }

    //EM Start
    // to be derived by derived classes when necessary
    function setPluginParameters()
    {
    }

    // to be derived by derived classes when necessary
    function getPluginParameters()
    {
        return array ('number' => 0,
                      'param' => array());
    }
    //EM end
}
