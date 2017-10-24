<?php
/*
Theme System
Version 1
by:ceemoo
http://www.smf.konusal.com

############################################
License Information:

Smf themes System Edit - www.smfhack.com
#############################################
*/

if (!defined('SMF'))
	die('Hacking attempt...');



// Hook Add Action
function themes_actions(&$actionArray)
{
	global $sourcedir, $modSettings;

    // Load the language files
    if (loadlanguage('tema') == false)
        loadLanguage('tema','english');
   
  $actionArray += array('tema' => array('tema2.php', 'DownloadsMain'));
  
}
function themes_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;
   	loadLanguage('tema');
	loadLanguage('ManageSettings');
    themes_array_insert($admin_areas, 'layout',
	        array(
            'tema' => array(
			'title' => $txt['tema_admin'],
			'permission' => array('themes_manage'),
			'areas' => array(
								'tema' => array(
									'label' =>'',
									'file' => 'tema2.php',
									'function' => 'DownloadsMain',
								),
								'adminset' => array(
									'label' => $txt['tema_text_settings'],
									'function' => 'Downloads_AdminSettings2',
									'icon' => 'temaicon.png',
									'subsections' => array(
									),
								),
								'approvelist' => array(
									'label' => $txt['tema_form_approvethemes'],
									'file' => 'tema2.php',
									'function' => 'Downloads_ApproveList',
									'custom_url' => $scripturl . '?action=admin;area=tema;sa=approvelist',
									'icon' => 'temaicon.png',
									'subsections' => array(
									),
								),
								'reportlist' => array(
									'label' => $txt['tema_form_reportthemes'],
									'file' => 'tema2.php',
									'function' => 'Downloads_ReportList',
									'custom_url' => $scripturl . '?action=admin;area=tema;sa=reportlist',
									'icon' => 'temaicon.png',
									'subsections' => array(
									),
								),
							
								'filespace' => array(
									'label' => $txt['tema_filespace'],
									'file' => 'tema2.php',
									'function' => 'Downloads_FileSpaceAdmin',
									'custom_url' => $scripturl . '?action=admin;area=tema;sa=filespace',
									'icon' => 'temaicon.png',
									'subsections' => array(
									),
								),
								
								'catpermlist' => array(
									'label' => $txt['tema_text_catpermlist2'],
									'file' => 'tema2.php',
									'function' => 'Downloads_CatPermList',
									'custom_url' => $scripturl . '?action=admin;area=tema;sa=catpermlist',
									'icon' => 'temaicon.png',
									'subsections' => array(
									),
								),
								'import' => array(
									'label' => $txt['tema_txt_import'],
									'file' => 'tema2.php',
									'function' => 'Downloads_ImportDownloads',
									'custom_url' => $scripturl . '?action=admin;area=tema;sa=import',
									'icon' => 'temaicon.png',
									'subsections' => array(
									),
								),
							),
						),
					)
				);
		
}

function Downloads_AdminSettings2()
{

	global $context, $scripturl, $sourcedir;
		global $context, $mbname, $txt;
	require_once($sourcedir . '/ManageServer.php');

	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_settings'];
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $context['page_title'],
	);
$set= $txt['tema_upload_max_filesize'].' : <a href="http://www.php.net/manual/en/ini.core.php#ini.upload-max-filesize" target="_blank">' . @ini_get("upload_max_filesize") . '</a>';
$set2= $txt['tema_post_max_size'].' : <a href="http://www.php.net/manual/en/ini.core.php#ini.post-max-size" target="_blank">' . @ini_get("post_max_size") . '</a>';
$txt['tema_pos']=$set.'<br>'.$set2;	

	$config_vars = array(
				
				array('title', 'tema_max_file'),
				array('desc','tema_pos'),

				array('title','tema_text_settings'),
				array('text', 'tema_max_filesize'),
				array('text', 'tema_path'),
				array('text', 'tema_url'),
				array('text', 'tema_set_files_per_page'),
				array('text', 'tema_set_cat_width'),
				array('text', 'tema_set_cat_height'),
				array('text', 'tema_set_file_image_width'),
				array('text', 'tema_set_file_image_height'),

				array('title','tema_catthumb_settings'),
				array('check', 'tema_who_viewing'),
				array('check', 'tema_set_enable_multifolder'),
				array('check', 'tema_show_ratings'),  
				array('check', 'tema_index_recent'), 
				array('check', 'tema_index_showtop'),	
				array('check', 'tema_set_show_quickreply'),	

				array('title', 'tema_files_settings'),
				array('check', 'tema_set_t_downloads'),
				array('check', 'tema_set_t_views'),
				array('check', 'tema_set_t_filesize'),  
				array('check', 'tema_set_t_date'), 
				array('check', 'tema_set_t_username'),	
				array('check', 'tema_set_t_rating'),	
				array('check', 'tema_set_t_title'),
				array('check', 'tema_set_count_child'), 
				array('check', 'tema_set_file_prevnext'),
				array('check', 'tema_set_file_thumb'),
				array('check', 'tema_set_file_desc'),  
				array('check', 'tema_set_file_title'), 
				array('check', 'tema_set_file_views'),	
				array('check', 'tema_set_file_downloads'),	
				array('check', 'tema_set_file_lastdownload'),
				array('check', 'tema_set_file_poster'),
				array('check', 'tema_set_file_date'),
				array('check', 'tema_set_file_showfilesize'),
				array('check', 'tema_set_file_showrating'),  
				array('check', 'tema_set_file_keywords'), 

				array('title', 'tema_txt_download_linking'),
				array('check', 'tema_set_showcode_directlink'),
				array('check', 'tema_set_showcode_htmllink'),
				'',

				array('title', 'themesizinlerbaslik'),
				array('title', 'themes_view'),
				array('permissions', 'themes_view', 'subtext' => $txt['permissionhelp_themes_view']),	  
				'',
				array('title', 'themes_viewdownload'),
				array('permissions', 'themes_viewdownload', 'subtext' => $txt['permissionhelp_themes_viewdownload']),
				'',
				array('title', 'themes_add'),
				array('permissions', 'themes_add', 'subtext' => $txt['permissionhelp_themes_add']),
				'',
				array('title', 'themes_edit'),
				array('permissions', 'themes_edit', 'subtext' => $txt['permissionhelp_themes_edit']),
				'',
				array('title', 'themes_delete'),
				array('permissions', 'themes_delete', 'subtext' => $txt['permissionhelp_themes_delete']),	  
				'',
				array('title', 'themes_ratefile'),
				array('permissions', 'themes_ratefile', 'subtext' => $txt['permissionhelp_themes_ratefile']),
				'',
				array('title', 'themes_report'),
				array('permissions', 'themes_report', 'subtext' => $txt['permissionhelp_themes_report']),
				'',
				array('title', 'themes_autoapprove'),
				array('permissions', 'themes_autoapprove', 'subtext' => $txt['permissionhelp_themes_autoapprove']),
				'',
				array('title', 'themes_manage'),
				array('permissions', 'themes_manage', 'subtext' => $txt['permissionhelp_themes_manage']),
	);
	$context['post_url'] = $scripturl . '?action=admin;area=adminset;save';


	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=adminset');
	}

		prepareDBSettingContext($config_vars);
}

function themes_menu_buttons(&$menu_buttons)
{
	global $txt, $user_info, $context, $modSettings, $scripturl;
   	loadLanguage('tema');

	#You can use these settings to move the button around or even disable the button and use a sub button
	#Main menu button options
	
	#Where the button will be shown on the menu
	$button_insert = 'mlist';
	
	#before or after the above
	$button_pos = 'before';
	#default is before the memberlist
    
    themes_array_insert($menu_buttons, $button_insert,
		     array(
                    'tema' => array(
    				'title' => $txt['tema_menu'],
    				'href' => $scripturl . '?action=tema',
    				'show' => allowedTo('themes_view'),
    				'icon' => 'temaicon.png',
			    )	
		    )
	    ,$button_pos);
        
 


}

function themes_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
{
	$position = array_search($key, array_keys($input), $strict);
	
	// Key not found -> insert as last
	if ($position === false)
	{
		$input = array_merge($input, $insert);
		return;
	}
	
	if ($where === 'after')
		$position += 1;

	// Insert as first
	if ($position === 0)
		$input = array_merge($insert, $input);
	else
		$input = array_merge(
			array_slice($input, 0, $position),
			$insert,
			array_slice($input, $position)
		);
}


?>