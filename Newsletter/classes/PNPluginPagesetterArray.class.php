<?php
/**
 * Newletter Module for Zikula
 *
 * @copyright © 2001-2009, Devin Hayes (aka: InvalidReponse), Dominik Mayer (aka: dmm), Robert Gasch (aka: rgasch)
 * @link http://www.zikula.org
 * @version $Id: pnuser.php 24342 2008-06-06 12:03:14Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * Support: http://support.zikula.de, http://community.zikula.org
 */


class PNPluginPagesetterArray extends PNPluginBaseArray
{
    function PNPluginPagesetterArray ($init=null, $where='')
    {
        $this->PNPluginBaseArray ();
    }


    function getPluginData ($lang=null)
    {
        if (!pnModAvailable('Pagesetter')) {
            return array();
        }

        if (!pnModDBInfoLoad('Pagesetter')) {
            return array();
        }

        return $this->_getPagesetterItems ($lang);
    }


    function _getPagesetterItems($lang)
    {
        $rc = true;

        // for some reason (that I don't quite understand) the table prefix can cause problems so we strip it here 
        $pnTables = $GLOBALS['pntables'];
        $tbl = $pnTables['pagesetter_pubtypes'];
        $col = $pnTables['pagesetter_pubtypes_column'];
        $t = array();
        foreach ($col as $k=>$v) {
            $t[$k] = str_replace ("$tbl.", '', $v);
        }
        $pnTables['pagesetter_pubtypes_column'] = $t;
        $GLOBALS['pntables'] = $pnTables;

        $tids     = pnModGetVar ('Newsletter', 'pagesetterTIDs', '');
        $nItems   = pnModGetVar ('Newsletter', 'plugin_Pagesetter_nItems', 1);
        $enableML = pnModGetVar ('Newsletter', 'enable_multilingual', 0);
        $where  = '';
        if ($tids) {
            $where = "pg_id in ($tids)";
        }
        $tids = DBUtil::selectObjectArray ('pagesetter_pubtypes', $where);
        if (!$tids) {
            return array();
        }

        $pagesetterPublications = array();
        if ($rc) {
            $cnt   = 0;
            $prefix = pnConfigGetVar('prefix');

            foreach ($tids as $tid) {
                if (!SecurityUtil::checkPermission('pagesetter::', "$tid::", ACCESS_READ)) {
                    continue;
                }

                // get text field name from pagesetter field definition
                $where        = "pg_tid = $tid[id] AND pg_istitle = 1";
                $field       = DBUtil::selectField ('pagesetter_pubfields', 'id', $where, 'pg_id');
                $where        = "pg_id = $tid[id]";
                $useRevisions = DBUtil::selectField ('pagesetter_pubtypes', 'enableRevisions', $where);

                if (!$field) {
                    LogUtil::registerError ("Unable to find pagesetter title field for tid [$tid]");
                    return array();
                } else {
                    // create pnables entry for pagesetter publication type
                    $pagesetterTable = 'pagesetter_pubdata' . $tid['id'];
                    $pagesetterTableCol = 'pagesetter_pubdata' . $tid['id'] . '_column';
                    $pagesetterTableFull = $prefix . '_' . $pagesetterTable;

                    $tidTables = array();
                    $tidTables[$pagesetterTable] = $pagesetterTableFull;
                    $columns = array (
                        'id'                    => 'pg_id',
                        'pid'                   => 'pg_pid',
                        'online'                => 'pg_online',
                        'publishDate'           => 'pg_publishDate',
                        'title'                 => 'pg_field' . $field
                    );

                    $tidTables[$pagesetterTableCol] = $columns;
                    $GLOBALS['pntables'] = array_merge((array)$GLOBALS['pntables'], $tidTables);

                    // now search publication type
                    $page  = 0;
                    $where = 'pg_online = 1';
                    if ($enableML) {
                        $where .= " AND pg_language = '$lang'";
                    }
                    $pgPubs = DBUtil::selectObjectArray ($pagesetterTable, $where, 'pg_created DESC', 0, $nItems, 'publishDate');
                    if ($pgPubs === false) {
                        break;
                    } elseif ($pgPubs) {
                        foreach ($pgPubs as $k=>$pgPub) {
                            $pgPubs[$k]['tid']   = $tid['id'];
                        }
                        $pagesetterPublications = array_merge ($pagesetterPublications, $pgPubs);
                    }
                }
            }
        }

        if ($pagesetterPublications) {
            krsort ($pagesetterPublications, SORT_STRING);
            $pagesetterPublications = array_slice ($pagesetterPublications, 0, $nItems);
        }

        return $pagesetterPublications;
    }
}
