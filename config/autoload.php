<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
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
	// Src
	'MCupic\LogReport\ModLogReport' => 'system/modules/log_report/src/modules/ModLogReport.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'log_report_partial' => 'system/modules/log_report/templates',
	'log_report'         => 'system/modules/log_report/templates',
));
