<?php
/*
Theme System
Version 1
by:ceemoo
http://www.smf.konusal.com
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
   
  $actionArray += array('tema' => array('tema2.php', 'ThemesMain'));
  
}
function themes_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;
   	loadtemplate('tema2.1');
	loadLanguage('tema');
	loadLanguage('ManageSettings');
	$admin_areas['config']['areas']['modsettings']['subsections']['tema_izinler'] = array($txt['themesizinlerbaslik']);
    themes_array_insert($admin_areas, 'layout',
	        array(
            'tema' => array(
			'title' => $txt['tema_admin'],
			'permission' => array('themes_manage'),
			'areas' => array(
								'tema' => array(
									'label' => $txt['tema_text_settings'],
									'file' => 'tema2.php',
									'function' => 'Themes_AdminSettings',
									'custom_url' => $scripturl . '?action=admin;area=tema;sa=adminset',
									'icon' => 'temaicon.png',
								),
								'approvelist' => array(
									'label' => $txt['tema_form_approvethemes'],
									'file' => 'tema2.php',
									'function' => 'Themes_ApproveList',
									'custom_url' => $scripturl . '?action=admin;area=approvelist',
									'icon' => 'temaicon.png',
									'subsections' => array(
									),
								),
								'reportlist' => array(
									'label' => $txt['tema_form_reportthemes'],
									'file' => 'tema2.php',
									'function' => 'Themes_ReportList',
									'custom_url' => $scripturl . '?action=admin;area=reportlist',
									'icon' => 'temaicon.png',
									'subsections' => array(
									),
								),
							
								'filespace' => array(
									'label' => $txt['tema_filespace'],
									'file' => 'tema2.php',
									'function' => 'Themes_FileSpaceAdmin',
									'custom_url' => $scripturl . '?action=admin;area=filespace',
									'icon' => 'temaicon.png',
									'subsections' => array(
									),
								),
								
								'catpermlist' => array(
									'label' => $txt['tema_text_catpermlist2'],
									'file' => 'tema2.php',
									'function' => 'Themes_CatPermList',
									'custom_url' => $scripturl . '?action=admin;area=catpermlist',
									'icon' => 'temaicon.png',
									'subsections' => array(
									),
								),
							),
						),
					)
				);
		
}

function Themes_mod_Ayarlar(&$sub_actions)
{
	$sub_actions['tema_izinler'] = 'Themes_mod';
}
function Themes_mod($return_config = false)
{

		global $txt, $scripturl, $context;

	$config_vars = array(
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
if ($return_config)
			return $config_vars;

		$context['post_url'] = $scripturl . '?action=admin;area=modsettings;sa=tema_izinler;save';
		$context['settings_title'] = $txt['themesizinlerbaslik'];


	if (isset($_GET['save']))
	{
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=modsettings;sa=tema_izinler');
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