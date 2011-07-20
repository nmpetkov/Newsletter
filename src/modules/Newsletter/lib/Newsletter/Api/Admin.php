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

class Newsletter_Api_Admin extends Zikula_AbstractApi
{
    public function getlinks()
    {
        $links = array();

        $dom = ZLanguage::getModuleDomain('Newsletter');

        if (SecurityUtil::checkPermission('Newsletter::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Newsletter', 'admin', 'main'),                             'text' => __('Start', $dom));
            $links[] = array('url' => ModUtil::url('Newsletter', 'admin', 'settings'),                         'text' => __('Newsletter Settings', $dom));
            $links[] = array('url' => ModUtil::url('Newsletter', 'admin', 'archive'),                          'text' => __('Archive Settings', $dom));
            $links[] = array('url' => ModUtil::url('Newsletter', 'admin', 'view', array('ot'=>'statistics')),  'text' => __('Statistics', $dom));
            $links[] = array('url' => ModUtil::url('Newsletter', 'admin', 'view', array('ot'=>'message')),     'text' => __('Intro Message', $dom));
            $links[] = array('url' => ModUtil::url('Newsletter', 'admin', 'view', array('ot'=>'preview')),     'text' => __('Preview', $dom));
            $links[] = array('url' => ModUtil::url('Newsletter', 'admin', 'view', array('ot'=>'user')),        'text' => __('Subscribers', $dom));
            $links[] = array('url' => ModUtil::url('Newsletter', 'admin', 'view', array('ot'=>'plugin')),      'text' => __('Plugins', $dom));
            $links[] = array('url' => ModUtil::url('Newsletter', 'admin', 'view', array('ot'=>'userimport')),  'text' => __('Import', $dom));
        }

        return $links;
    }
}
