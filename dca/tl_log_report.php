<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @package log_report
 * @link    http://contao.org
 * @author Marko Cupic m.cupic@gmx.ch
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

$GLOBALS['TL_DCA']['tl_log_report'] = array
(
    'config' => array
    (
        'dataContainer' => 'Table',
        'sql'           => array('keys' => array(
            'id'            => 'primary'
        ))
    ),
 
    'fields' => array
    (
        'id'            => array('sql' => "int(10) unsigned NOT NULL auto_increment"),
        'date'          => array('sql' => "varchar(10) NOT NULL default ''"),
        'recipients'    => array('sql' => "text NOT NULL"),
        'report'        => array('sql' => "text NOT NULL")
    )
);

 