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


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Modules
	'Contao\LogReport' => 'system/modules/log_report/modules/LogReport.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'log_report'         => 'system/modules/log_report/templates',
	'log_report_partial' => 'system/modules/log_report/templates',
));
