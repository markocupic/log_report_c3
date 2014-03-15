<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   log_report
 * @author    Marko Cupic
 * @license   shareware
 * @copyright Marko Cupic 2014
 */


$GLOBALS['TL_PURGE']['tables']['log_report']['callback'] = array(
       'MCupic\LogReport',
       'purgeLogReportTable'
);
$GLOBALS['TL_PURGE']['tables']['log_report']['affected'] = array('tl_log_report');

$GLOBALS['TL_HOOKS']['generatePage'][] = array(
       'MCupic\LogReport',
       'runLogReport'
);

// add request token to query string
if (TL_MODE == 'BE' && strlen(\Input::get('lrtoken')))
{
       $objLogReport = \Database::getInstance()->prepare('SELECT * FROM tl_log_report WHERE token=?')->execute(\Input::get('lrtoken'));
       if ($objLogReport->numRows)
       {
              $url = str_replace('/contao/', 'contao/', \Environment::get('requestUri'));
              $url = preg_replace('/lrtoken=(.+?)\&/', '', $url);
              $url .= '&rt=' . @$_SESSION['REQUEST_TOKEN'];
              \Controller::redirect($url);
       }
}