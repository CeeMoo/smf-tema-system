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

// Permissions
function themes_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
   global $context;
	
   $permissionList['membergroup'] += array(
        'themes_view' => array(false, 'themes', 'themes'),
		'themes_viewdownload' => array(false, 'themes', 'themes'),
		'themes_add' => array(false, 'themes', 'themes'),
		'themes_edit' => array(false, 'themes', 'themes'),
		'themes_delete' => array(false, 'themes', 'themes'),
		'themes_ratefile' => array(false, 'themes', 'themes'),
		'themes_report' => array(false, 'themes', 'themes'),
		'themes_autoapprove' => array(false, 'themes', 'themes'),
		'themes_manage' => array(false, 'themes', 'themes'),
    );
	

}

function themes_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;
   

    themes_array_insert($admin_areas, 'layout',
	        array(
                'tema' => array(
			'title' => $txt['tema_admin'],
			'permission' => array('themes_manage'),
			'areas' => array(
				'tema' => array(
					'label' => $txt['tema_admin'],
					'file' => 'tema2.php',
					'function' => 'DownloadsMain',
					'custom_url' => $scripturl . '?action=admin;area=tema;sa=adminset',
					'icon' => 'temaicon.png',
					'subsections' => array(
						'adminset' => array($txt['tema_text_settings']),
						'approvelist' => array($txt['tema_form_approvethemes']),
						'reportlist' => array($txt['tema_form_reportthemes']),
						'filespace' => array($txt['tema_filespace']),
						'catpermlist' => array($txt['tema_text_catpermlist2']),
						'import' => array($txt['tema_txt_import']),
					),
				),),
		),
                
	        )
        );
		
        


}

function themes_menu_buttons(&$menu_buttons)
{
	global $txt, $user_info, $context, $modSettings, $scripturl;

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