<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Log_report
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'MCupic',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Modules
	'MCupic\LogReport' => 'system/modules/log_report/modules/LogReport.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'log_report_partial' => 'system/modules/log_report/templates',
	'log_report'         => 'system/modules/log_report/templates',
));
