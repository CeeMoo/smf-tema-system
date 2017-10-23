<?php
/*
Download System
Version 2.5
by:vbgamer45
http://www.smfhacks.com
Copyright 2008-2014 SMFHacks.com

############################################
License Information:

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/

// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/temaHooks.php',
        'integrate_admin_areas' => 'themes_admin_areas',
	    'integrate_menu_buttons' => 'themes_menu_buttons',
		'integrate_actions' => 'themes_actions',
		'integrate_load_permissions' => 'themes_load_permissions',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);

// Insert the settings for 2.1
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES
('themes_smfversion','2.1')
");

?>