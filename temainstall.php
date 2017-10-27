<?php
/*
Theme System
Version 1
by:ceemoo
http://www.smf.konusal.com
*/
// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
  
global $smcFunc;
db_extend('Packages');
db_extend('Extra');
$hook_functions = array(
		'integrate_pre_include' => '$sourcedir/temaHooks.php',
        'integrate_admin_areas' => 'themes_admin_areas',
	    'integrate_menu_buttons' => 'themes_menu_buttons',
		'integrate_actions' => 'themes_actions',
		'integrate_modify_modifications' => 'Themes_AdminSettings2',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);

	global $boardurl,$boarddir;
	$yol= $boardurl . '/tema/';
	$yoldir= $boarddir . '/tema/';
	
$mod_settings = array(
	'tema_max_filesize' => '5000000',
	'tema_path' => $yoldir,
	'tema_url' => $yol,
	'tema_who_viewing' => '0',
	'tema_set_commentsnewest' => '1',
	'tema_commentchoice' => '0',
	'tema_set_count_child' => '1',
	'tema_show_ratings' => '1',
	'tema_index_recent' => '1',
	'tema_index_mostviewed' => '1',
	'tema_index_toprated' => '0',
	'tema_index_mostcomments' => '0',
	'themes_index_mostdownloaded' => '0',
	'tema_set_files_per_page' => '20',
	'tema_set_t_views' => '1',
	'tema_set_t_downloads' => '1',
	'tema_set_t_filesize' => '1',
	'tema_set_t_date' => '1',
	'tema_set_t_comment' => '1',
	'tema_set_t_username' => '1',
	'tema_set_t_rating' => '1',
	'tema_set_t_title' => '1',
	'tema_index_showtop' => '0',
	'tema_set_cat_width' => '120',
	'tema_set_cat_height' => '120',
	'tema_set_file_image_height' => '350',
	'tema_set_file_image_width' => '450',
	'tema_set_show_quickreply' => '0',
	'tema_set_file_thumb' => '1',
	'tema_set_file_prevnext' => '1',
	'tema_set_file_desc' => '1',
	'tema_set_file_title' => '1',
	'tema_set_file_views' => '1',
	'tema_set_file_downloads' => '1',
	'tema_set_file_lastdownload' => '1',
	'tema_set_file_poster' => '1',
	'tema_set_file_date' => '1',
	'tema_set_file_showfilesize' => '1',
	'tema_set_file_showrating' => '1',
	'tema_set_file_keywords' => '1',
	'tema_set_showcode_directlink' => '0',
	'tema_set_showcode_htmllink' => '0',
	'tema_set_enable_multifolder' => '0',
	'tema_folder_id' => '0',
	'themes_smfversion' => '2.1',
);

updateSettings($mod_settings);

$smcFunc['db_create_table']('{db_prefix}tema_file', array(
	array('name' => 'id_file', 'type' => 'int', 'size' => 11,'null' => false, 'auto' => true),
	array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8,'null' => false, 'default' => 0),
	array('name' => 'date', 'type' => 'int', 'size' => 11,'unsigned' => true,'null' => false, 'default' => 0),
	array('name' => 'title', 'type' => 'varchar', 'size' => 255,),
	array('name' => 'description','type' => 'text',),	
	array('name' => 'views', 'type' => 'int', 'size' => 11,'null' => false,'default' => 0),
	array('name' => 'totaldownloads', 'type' => 'int', 'size' => 11,'null' => false,'default' => 0),
	array('name' => 'lastdownload', 'type' => 'int', 'size' => 11,'unsigned' => true,'null' => false, 'default' => 0),
	array('name' => 'filesize', 'type' => 'int', 'size' => 11,'null' => false,'default' => 0),
	array('name' => 'orginalfilename', 'type' => 'tinytext',),
	array('name' => 'filename', 'type' => 'tinytext',),
	array('name' => 'fileurl', 'type' => 'tinytext',),
	array('name' => 'picture', 'type' => 'tinytext',),
	array('name' => 'pictureurl', 'type' => 'tinytext',),
	array('name' => 'demourl', 'type' => 'tinytext',),
	array('name' => 'id_cat', 'type' => 'int', 'size' => 11,'null' => false,'default' => 0),
	array('name' => 'approved', 'type' => 'tinyint','size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'totalratings', 'type' => 'int', 'null' => false, 'default' => 0),
	array('name' => 'rating', 'type' => 'int', 'size' => 11, 'null' => false, 'default' => 0),
	array('name' => 'type', 'type' => 'tinyint','size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'sendemail', 'type' => 'tinyint','size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'id_topic', 'type' => 'mediumint','size' => 8,'unsigned' => true, 'null' => false, 'default' => 0),
	array('name' => 'keywords', 'type' => 'varchar', 'size' => 255,),
	array('name' => 'credits', 'type' => 'int','size' => 11, 'null' => false, 'default' => 0),
	),
	array(array('type' => 'primary', 'columns' => array('id_file')),)
);

$smcFunc['db_create_table']('{db_prefix}tema_cat', array(
	array('name' => 'id_cat', 'type' => 'mediumint', 'size' => 8,'null' => false, 'auto' => true),
	array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8,'unsigned' => true,'null' => false, 'default' => 0),
	array('name' => 'title', 'type' => 'varchar', 'size' => 255),
	array('name' => 'description','type' => 'varchar', 'size' => 255),	
	array('name' => 'roworder', 'type' => 'mediumint', 'size' => 11,'unsigned' => true,'null' => false,'default' => 0),
	array('name' => 'image', 'type' => 'varchar', 'size' => 255),
	array('name' => 'filename', 'type' => 'tinytext',),
	array('name' => 'id_board', 'type' => 'tinyint', 'size' => 5,'unsigned' => true,'null' => false,'default' => 0),
	array('name' => 'id_parent', 'type' => 'tinyint','size' => 5, 'unsigned' => true,'null' => false, 'default' => 0),
	array('name' => 'disablerating', 'type' => 'tinyint','size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'total', 'type' => 'int', 'size' => 11, 'null' => false, 'default' => 0),
	array('name' => 'redirect', 'type' => 'tinyint','size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'locktopic', 'type' => 'tinyint','size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'showpostlink', 'type' => 'tinyint','size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'sortby', 'type' => 'tinytext',),
	array('name' => 'orderby', 'type' => 'tinytext',),
	),
	array(array('type' => 'primary', 'columns' => array('id_cat')),)
);

$smcFunc['db_create_table']('{db_prefix}tema_rating', array(
	array('name' => 'id', 'type' => 'int', 'size' => 11,'null' => false, 'auto' => true),
	array('name' => 'id_file', 'type' => 'int','size' => 11,'null' => false,),
	array('name' => 'id_member', 'type' => 'mediumint','size' => 8,'unsigned' => true, 'null' => false, 'default' => 0),
	array('name' => 'value', 'type' => 'tinyint', 'size' => 2,'null' => false,),
	),
	array(array('type' => 'primary', 'columns' => array('id')),)
);

$smcFunc['db_create_table']('{db_prefix}tema_report', array(
	array('name' => 'id', 'type' => 'int', 'size' => 11,'null' => false, 'auto' => true),
	array('name' => 'id_file', 'type' => 'int','size' => 11,'null' => false,),
	array('name' => 'id_member', 'type' => 'mediumint','size' => 8,'unsigned' => true, 'null' => false, 'default' => 0),
	array('name' => 'comment', 'type' => 'text',),
	array('name' => 'date', 'type' => 'int', 'size' => 11,'unsigned' => true,'null' => false, 'default' => 0),
	),
	array(array('type' => 'primary', 'columns' => array('id')),)
);

$smcFunc['db_create_table']('{db_prefix}tema_creport', array(
	array('name' => 'id', 'type' => 'int', 'size' => 11,'null' => false, 'auto' => true),
	array('name' => 'id_file', 'type' => 'int','size' => 11,'null' => false,),
	array('name' => 'id_comment', 'type' => 'int','size' => 11,'null' => false,),
	array('name' => 'id_member', 'type' => 'mediumint','size' => 8,'unsigned' => true, 'null' => false, 'default' => 0),
	array('name' => 'comment', 'type' => 'text',),
	array('name' => 'date', 'type' => 'int', 'size' => 11,'unsigned' => true,'null' => false, 'default' => 0),
	),
	array(array('type' => 'primary', 'columns' => array('id')),)
);

$smcFunc['db_create_table']('{db_prefix}tema_userquota', array(
	array('name' => 'id_member', 'type' => 'mediumint','size' => 8,'unsigned' => true, 'null' => false, 'default' => 0),
	array('name' => 'totalfilesize', 'type' => 'int', 'size' => 11,'null' => false, 'default' => 0),
	),
	array(array('type' => 'primary', 'columns' => array('id_member')),)
);

$smcFunc['db_create_table']('{db_prefix}tema_groupquota', array(
	array('name' => 'id_group', 'type' => 'smallint','size' => 5,'unsigned' => true, 'null' => false, 'default' => 0),
	array('name' => 'totalfilesize', 'type' => 'int', 'size' => 11,'null' => false, 'default' => 0),
	),
	array(array('type' => 'primary', 'columns' => array('id_group')),)
);

$smcFunc['db_query']('', "ALTER TABLE {db_prefix}tema_userquota CHANGE totalfilesize totalfilesize BIGINT NOT NULL  default '0'");
$smcFunc['db_query']('', "ALTER TABLE {db_prefix}tema_groupquota CHANGE totalfilesize totalfilesize BIGINT NOT NULL  default '0'");
$smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat SET total = -1");


$smcFunc['db_create_table']('{db_prefix}tema_catperm', array(
	array('name' => 'id', 'type' => 'mediumint', 'size' => 8,'null' => false, 'auto' => true),
	array('name' => 'id_group', 'type' => 'mediumint','size' => 8, 'null' => false, 'default' => 0),
	array('name' => 'id_cat', 'type' => 'mediumint', 'size' => 8,'null' => false, 'default' => 0),
	array('name' => 'view', 'type' => 'tinyint', 'size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'viewdownload','type' => 'tinyint', 'size' => 4, 'null' => false, 'default' => 0),	
	array('name' => 'addfile', 'type' => 'tinyint', 'size' => 4,'null' => false,'default' => 0),
	array('name' => 'editfile', 'type' => 'tinyint', 'size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'delfile', 'type' => 'tinyint','size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'ratefile', 'type' => 'tinyint', 'size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'addcomment', 'type' => 'tinyint','size' => 4,  'null' => false, 'default' => 0),
	array('name' => 'editcomment', 'type' => 'tinyint','size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'report', 'type' => 'tinyint', 'size' => 4, 'null' => false, 'default' => 0),
	),
	array(array('type' => 'primary', 'columns' => array('id')),)
);

$smcFunc['db_create_table']('{db_prefix}tema_custom_field', array(
	array('name' => 'id_custom', 'type' => 'mediumint', 'size' => 8,'null' => false, 'auto' => true),
	array('name' => 'id_cat', 'type' => 'int', 'size' => 11,'null' => false),
	array('name' => 'roworder', 'type' => 'mediumint', 'size' => 8,'unsigned' => true, 'null' => false, 'default' => 0),
	array('name' => 'title','type' => 'tinytext',),	
	array('name' => 'defaultvalue', 'type' => 'tinytext',),
	array('name' => 'is_required', 'type' => 'tinyint', 'size' => 4, 'null' => false, 'default' => 0),
	array('name' => 'showoncatlist', 'type' => 'tinyint','size' => 4, 'null' => false, 'default' => 0),
	),
	array(array('type' => 'primary', 'columns' => array('id_custom')),)
);

$smcFunc['db_create_table']('{db_prefix}tema_custom_field_data', array(
	array('name' => 'id_custom', 'type' => 'int', 'size' => 11,'null' => false),
	array('name' => 'id_file', 'type' => 'int','size' => 11,'null' => false,'default' => 0),
	array('name' => 'value','type' => 'tinytext',),	
	),
	array(array('type' => 'primary', 'columns' => array('id_custom')),)
);


// Recount Totals
echo 'Recounting Download Totals...<br />';
$dbresult = $smcFunc['db_query']('', "
SELECT
	id_cat
FROM {db_prefix}tema_cat");
while($row = $smcFunc['db_fetch_assoc']($dbresult))
	UpdateCategoryTotals($row['id_cat']);

$smcFunc['db_free_result']($dbresult);

function UpdateCategoryTotals($ID_CAT)
{
	global $smcFunc;

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}tema_file
	WHERE id_cat = $ID_CAT AND approved = 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);

	// Update the count
	$dbresult = $smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat SET total = $total WHERE ID_CAT = $ID_CAT LIMIT 1");

}

// Permissions array
$permissions = array(
	'downloads_view' => array(-1, 0, 2), // ALL
);

addPermissions($permissions);

function addPermissions($permissions)
{
	global $smcFunc;

	$perm = array();

	foreach ($permissions as $permission => $default)
	{
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}permissions
			WHERE permission = {string:permission}',
			array(
				'permission' => $permission
			)
		);

		list ($num) = $smcFunc['db_fetch_row']($result);

		if ($num == 0)
		{
			foreach ($default as $grp)
				$perm[] = array($grp, $permission);
		}
	}

	if (empty($perm))
		return;

	$smcFunc['db_insert']('insert',
		'{db_prefix}permissions',
		array(
			'id_group' => 'int',
			'permission' => 'string'
		),
		$perm,
		array()
	);
}

?>