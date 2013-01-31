<?php
 /**
 * Newletter Module for Zikula
 *
 * @copyright © 2001-2010, Devin Hayes (aka: InvalidReponse), Dominik Mayer (aka: dmm), Robert Gasch (aka: rgasch)
 * @link http://www.zikula.org
 * @version $Id: Newsletter_DBObject_User.php 24342 2008-06-06 12:03:14Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * Support: http://support.zikula.de, http://community.zikula.org
 */

class Newsletter_DBObject_NewsletterSend extends DBObject 
{
    var $_objLang;
    var $_objNewsletterData;
    var $_objSendType;
    var $_objUpdateSendDate;

    function Newsletter_DBObject_NewsletterSend($init=null, $key=null, $field=null)
    {
        $this->_objType           = 'generic';
        $this->_objColumnPrefix   = 'nlu';
        $this->_objPath           = 'user_array';
        $this->_objLang           = null;   // custom var
        $this->_objNewsletterData = null;   // custom var
        $this->_objSendType       = null;   // custom var
        $this->_objUpdateSendDate = null;   // custom var

        $this->_objJoin   = array();
        $this->_objJoin[] = array(
                                'join_table'          => 'users',
                                'join_field'          => array('uname', 'email'),
                                'object_field_name'   => array('user_name', 'user_email'),
                                'compare_field_table' => 'uid',
                                'compare_field_join'  => 'uid'
                            );

        $this->_init ($init, $key, $field);
    }

    // doesn't save user info but allows us to use the standard API through Newsletter_userform_edit()
    function insert($args=array())
    {

        if (!ModUtil::available('Mailer')) {
            return LogUtil::registerError(__('The Mailer module is not available.'));
        }

        if (!class_exists('Newsletter_DBObject_User')) {
            return LogUtil::registerError(__f('Unable to load class [%s]', 'user'));
        }

        if (!class_exists('Newsletter_DBObject_UserArray')) {
            return LogUtil::registerError(__('Unable to load array class [user]'));
        }

        if (!class_exists('Newsletter_DBObject_NewsletterDataArray')) {
            return LogUtil::registerError(__('Unable to load array class [newsletter_data]'));
        }

        $enable_multilingual       = ModUtil::getVar('Newsletter', 'enable_multilingual', 0);
        $this->_objLang            = $enable_multilingual ? FormUtil::getPassedValue('language', '', 'GETPOST') : SessionUtil::getVar('lang'); // custom var
        $newsletterDataObjectArray = new Newsletter_DBObject_NewsletterDataArray();
        $this->_objNewsletterData  = $newsletterDataObjectArray->getNewsletterData($this->_objLang);              // custom var

        $this->_objSendType       = FormUtil::getPassedValue('sendType', '', 'GETPOST');                          // custom var
        $this->_objUpdateSendDate = FormUtil::getPassedValue('updateSendDate', '', 'GETPOST');                    // custom var
        $testsend                 = FormUtil::getPassedValue('testsend', 0, 'GETPOST');                           // from admin->preview

        if (!$this->_objNewsletterData) {
            return LogUtil::registerError(__('No newsletter data to send'));
        }

        if ($testsend) {
            $this->_objSendType = 'test';
            return $this->_sendTest();
        } 

        if ($this->_objSendType == 'manual') {
            return $this->_sendManual($args);
        }

        if ($this->_objSendType == 'manual_archive') {
            return $this->_sendManual_archive($args);
        }
     
        $this->_objSendType = 'api';

        return $this->_sendAPI($args);
    }

    function _sendTest()
    {
        $testsendEmail = FormUtil::getPassedValue('testsend_email', 0, 'GETPOST');
        $format        = FormUtil::getPassedValue('format', 1, 'GETPOST');

        $user = array();
        $user['email'] = $testsendEmail;
        $user['type'] = $format;

        return $this->_sendNewsletter($user);
    }

    function _sendManual($args=array())
    {
        $data = $this->_objData;
        if (!$data) {
            return LogUtil::registerError(__('No users were selected to send the newsletter to'));
        }

        $objectArray = new Newsletter_DBObject_UserArray();
        $userIDs     = implode(',', $data);
        $where       = "nlu_id IN ($userIDs) AND nlu_active=1 AND nlu_approved=1";
        $users       = $objectArray->get($where, 'id');
        if (!$users) {
            return LogUtil::registerError(__('No users were available to send the newsletter to'));
        }

        $nSent   = 0;
        foreach ($users as $user) {
            if ($this->_sendNewsletter($user)) {
                $nSent++;
            }
        }

        LogUtil::registerStatus($nSent.' '.__('Newsletter(s) were successfully sent.'));
        return true;
    }

    function _sendManual_archive($args=array()) // send Newsletter & make an archive
    {
        $data = $this->_objData;
        if ($data) {
            if (!class_exists('Newsletter_DBObject_Archive')) {
                return LogUtil::registerError(__f('Unable to load class [%s]', 'archive'));
            }

            $objectArray = new Newsletter_DBObject_UserArray();
            $userIDs     = implode(',', $data);
            $where       = "nlu_id IN ($userIDs) AND nlu_active=1 AND nlu_approved=1";
            $users       = $objectArray->get($where, 'id');
            if (!$users) {
                //return LogUtil::registerError(__('No users were available to send the newsletter to'));
            }
        }

        $sendPerRequest = ModUtil::getVar('Newsletter', 'send_per_request', 5);

        // check archives for new archive time
        $matched = false;
        $archiveObj = new Newsletter_DBObject_Archive();
        $archive    = $archiveObj->getRecent();
        if ($archive) {
            $newArchiveTime = $archive['date'];                        
            $matched = true;
        } else {
            $newArchiveTime = DateUtil::getDatetime();
            $matched = false;
        }
        $newArchiveId = 0;
        //if ($matched) {
            //LogUtil::registerStatus(__('Newsletter is not saved to archive, as last saved is within 1 week ('.$newArchiveTime.').'));
        //} else {
            if ($this->_archiveNewsletter($newArchive, $newArchiveTime, $newArchiveId)) {
                LogUtil::registerStatus(__('The new newsletter is added to archive.'));
            }
        //}

        if ($users) {
            $nSent = 0;
            foreach ($users as $user) {
                if (!empty($newArchiveId)) {
                    $user['last_send_nlid'] = $newArchiveId;
                }
                if ($this->_sendNewsletter($user)) {
                    $nSent++;
                }
            }

            LogUtil::registerStatus($nSent.' '.__('Newsletter(s) were successfully sent.'));
        }

        return true;
    }

    function _sendAPI($args=array()) // API
    {
        if (!class_exists('Newsletter_DBObject_Archive')) {
            return LogUtil::registerError(__f('Unable to load class [%s]', 'archive'));
        }

        // check auth key
        $adminKey  = (string)FormUtil::getPassedValue('admin_key', FormUtil::getPassedValue('authKey', 0));
        $masterKey = (string)ModUtil::getVar('Newsletter', 'admin_key', -1);
        if ($adminKey != $masterKey) {
            return (__('Invalid admin_key received'));
        }

        // get elegible users
        $objectArray = new Newsletter_DBObject_UserArray();
        $users = $objectArray->getSendable($this->_objLang, 'id');
        if (!$users) {
            return (__('No users were available to send the newsletter to'));
        }

        $thisDay   = date('w', time());
        $scheduled = isset($args['scheduled']) ? $args['scheduled'] : 0;
        $sendAll   = (boolean)FormUtil::getPassedValue('send_all', 0);  // overrides send_limit per request

        set_time_limit(90);
        ignore_user_abort(true);

        // ensure non-scheduled execution happens on the correct day
        if (!$scheduled){
            $sendDay = ModUtil::getVar('Newsletter', 'send_day', 0);
            if ($sendDay != $thisDay) {
                return __('Wrong day');
            }
        }
        
        // keep a record of how many mails were sent today
        $maxPerHour = ModUtil::getVar('Newsletter', 'max_send_per_hour', 0);
        if ($maxPerHour) {
            $spamData  = ModUtil::getVar('Newsletter', 'spam_count', '');
            if ($spamData) {
                $spamArray = explode('-', $spamData);
                $now       = time();
                // if now minus start of send is more than an hour and more than "x" have been sent, stop the process
                // step two: if the hour is greater than when we started, reset counters (and set spam array, used below)
                if ($now-$spamArray['0'] >= 3600 && $spamArray['1'] >= $maxPerHour && date('G')==date('G', $spamArray['0'])) {
                    return 'spam limits encountered';
                } else if (date('G') > date('G', $spamArray['0'])) {
                    ModUtil::setVar('Newsletter', 'spam_count', time().'-0');
                    $spamArray = array(time(),'0');
                }
            }
        }

        ModUtil::setVar('Newsletter', 'start_execution_time', (float)array_sum(explode(' ', microtime())));
        $sendPerRequest = ModUtil::getVar('Newsletter', 'send_per_request', 5);
        
        // check archives for new archive time
        $matched = false;
        $archiveObj = new Newsletter_DBObject_Archive();
        $archive    = $archiveObj->getRecent ();
        if ($archive) {
            $newArchiveTime = $archive['date'];                        
            $matched = true;
        } else {
            $newArchiveTime = DateUtil::getDatetime();
            $matched = false;
        }
        if (!$matched) {
            $this->_archiveNewsletter($newArchive, $newArchiveTime);
        }

        $nSent = 0;
        $allowFrequencyChange = ModUtil::getVar('Newsletter', 'allow_frequency_change', 0);
        foreach ($users as $user) {
            if (!$allowFrequencyChange || (isset($user['send_now']) && $user['send_now'])) {        
                if ($this->_sendNewsletter($user)) {
                    if (++$nSent == $sendPerRequest) {
                        break;
                    }
                }
            }
        }
        LogUtil::registerStatus($nSent.' '.__('Newsletter(s) were successfully sent.'));
        
        if ($maxPerHour) {
            ModUtil::setVar('Newsletter', 'spam_count', $spamArray['0'] . '-' . ($spamArray['1']+$nSent));
        }

        ModUtil::setVar('Newsletter', 'end_execution_time', (float)array_sum(explode(' ', microtime())));
        ModUtil::setVar('Newsletter', 'end_execution_count', $nSent);
        if (isset($args['respond']) && $args['respond']) {
            return " $nSent ";
        }
        
        return true;
    }

    function _getNewsletterMessage($user, $cacheID=null, $personalize=false, &$html=false) 
    {
        switch ($user['type']) {
            case 1:  $tpl = 'output/text.tpl'; $html = false; break;
            case 2:  $tpl = 'output/html.tpl'; $html = true; break;
            case 3:  $tpl = 'output/text_with_link.tpl'; $html = false; break;
            default: $tpl = 'output/html.tpl'; $html = true; break;
        }

        $personalize = ModUtil::getVar('Newsletter','personalize_email', false);

        $view = Zikula_View::getInstance('Newsletter', $personalize ? false : true, $personalize ? null : $cacheID);

        $view->assign('show_header', '1');
        $view->assign('site_url', System::getBaseUrl());
        $view->assign('site_name', System::getVar('sitename'));
        $view->assign('user_name', $personalize ? $user['name'] : '');
        $view->assign('user_email', $personalize ? $user['email'] : '');
        $view->assign('objectArray', $this->_objNewsletterData);

        return $view->fetch($tpl);
    }

    function _sendNewsletter($user, $cacheID=null)
    {
        $html    = false;
        $message = $this->_getNewsletterMessage($user, $cacheID, false, $html); // $html is output, defaults to html
        $from    = ModUtil::getVar('Newsletter', 'send_from_address', System::getVar('adminmail'));
        $subject = ModUtil::getVar('Newsletter', 'newsletter_subject') ;
        // ModUtil::apiFunc('Mailer', 'user', 'sendmessage') requires boolean html parameter!
        if ($html) {
            $html = true;
        } else {
            $html = false;
        }
        if (!$html) {
            // convert new lines, if exist in message
            $message = str_replace('<br />',"\n",$message);
            // strip tags to prevent spam, exept <a> tag
            $message = strip_tags($message, '<a>');
            // remove unwanted parts from <a> tag
            $message = preg_replace("#\<a.+href\=[\"|\'](.+)[\"|\'].*\>.*\<\/a\>#U","$1", $message);
        }
        $sent    = ModUtil::apiFunc('Mailer', 'user', 'sendmessage',
                                    array('toaddress'  => $user['email'],
                                          'fromaddress'=> $from,
                                          'subject'    => $subject,
                                          'body'       => $message,
                                          'html'       => $html));

        if ($sent && ($this->_objSendType == 'api' || $this->_objUpdateSendDate)) {
            $userData = array();
            $userData['id'] = $user['id'];
            $userData['last_send_date'] = DateUtil::getDatetime();
            $userData['last_send_nlid'] = 0;
            if (isset($user['last_send_nlid'])) {
                $userData['last_send_nlid'] = $user['last_send_nlid'];
            }

            $object = new Newsletter_DBObject_User();
            $object->setData($userData);
            $object->update();

            return System::redirect(ModUtil::url('Newsletter', 'admin', 'view', array('ot' => 'user')));
        }

        return $sent;
    }

    // creates new record in newsletter archive table
    // in $newArchiveId return new archive Id
    function _archiveNewsletter($newArchive, $newArchiveTime, &$newArchiveId = 0)
    {
        if (!class_exists('Newsletter_DBObject_Archive')) {
            return LogUtil::registerError(__f('Unable to load class [%s]', 'archive'));
        }

        $message_text = $this->_getNewsletterMessage(array('type' => 1), null, false);
        $message_html = $this->_getNewsletterMessage(array('type' => 2), null, false);

        $archiveData = array();
        $archiveData['date']      = DateUtil::getDatetime();
        $archiveData['time']      = $newArchiveTime;
        $archiveData['lang']      = $this->_objLang;
        // language if not set - same sequence as in html.tpl/text.tpl
        if (empty($archiveData['lang'])) {
            $archiveData['lang'] = FormUtil::getPassedValue('language', '', 'GETPOST');
        }
        if (empty($archiveData['lang'])) {
            $archiveData['lang'] = ZLanguage::getLanguageCode();
        }
        $archiveData['n_plugins'] = $this->_objNewsletterData['nPlugins'];
        $archiveData['n_items']   = $this->_objNewsletterData['nItems'];
        $archiveData['text']      = $message_text;
        $archiveData['html']      = $message_html;
        $archiveObj = new Newsletter_DBObject_Archive();
        $archiveObj->setData($archiveData);
        $result = $archiveObj->save($archiveData);
        if ($result) {
            $newArchiveId = $result['id'];
        }

        return true;
    }
}
