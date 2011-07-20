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

if (!Loader::loadClassFromModule('Newsletter', 'user')) {
    return LogUtil::registerError (__('Unable to load class [user] ... ', $dom));
}

class PNUserActive extends PNUser 
{
    function PNUserStatus ($init=null, $key=null, $field=null)
    {
        $this->PNUser ($init, $key, $field);
    }

    function save()
    {
        $dom = ZLanguage::getModuleDomain('Newsletter');

        $id = FormUtil::getPassedValue ('id', null, 'GET');

        $data = $this->get ($id);
        $data['active'] = $data['active'] ? 0 : 1;

        $this->_objData = $data;
        $this->update ();
        return LogUtil::registerStatus (__("The user's approved status has been changed", $dom));
    }
}
