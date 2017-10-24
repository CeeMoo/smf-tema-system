<?php


if (!defined('SMF'))
	die('Hacking attempt...');

function DownloadsMain()
{
	global $boardurl, $modSettings, $boarddir, $currentVersion, $context;

	$currentVersion = '2.0';

	if (empty($modSettings['tema_url']))
		$modSettings['tema_url'] = $boardurl . '/tema/';

	if (empty($modSettings['tema_path']))
		$modSettings['tema_path'] = $boarddir . '/tema/';

	// Load the language files
	if (loadlanguage('tema') == false)
		loadLanguage('tema','english');


   loadtemplate('tema2.1');
   $context['downloads21beta'] = true;


	// Download Actions pretty big array heh
	$subActions = array(
		'view' => 'Downloads_ViewDownload',
		'bulkactions' => 'Downloads_BulkActions',
		'delete' => 'Downloads_DeleteDownload',
		'delete2' => 'Downloads_DeleteDownload2',
		'edit' => 'Downloads_EditDownload',
		'edit2' => 'Downloads_EditDownload2',
		'report' => 'Downloads_ReportDownload',
		'report2' => 'Downloads_ReportDownload2',
		'deletereport' => 'Downloads_DeleteReport',
		'reportlist' => 'Downloads_ReportList',
		'rate' => 'Downloads_RateDownload',
		'viewrating' => 'Downloads_ViewRating',
		'delrating' => 'Downloads_DeleteRating',
		'catup' => 'Downloads_CatUp',
		'catdown' => 'Downloads_CatDown',
		'catperm' => 'Downloads_CatPerm',
		'catperm2' => 'Downloads_CatPerm2',
		'catpermlist' => 'Downloads_CatPermList',
		'catpermdelete' => 'Downloads_CatPermDelete',
		'catimgdel' => 'Downloads_CatImageDelete',
		'fileimgdel' => 'Downloads_FileImageDelete',
		'addcat' => 'Downloads_AddCategory',
		'addcat2' => 'Downloads_AddCategory2',
		'editcat' => 'Downloads_EditCategory',
		'editcat2' => 'Downloads_EditCategory2',
		'deletecat' => 'Downloads_DeleteCategory',
		'deletecat2' => 'Downloads_DeleteCategory2',
		'myfiles' => 'Downloads_MyFiles',
		'approvelist' => 'Downloads_ApproveList',
		'approve' => 'Downloads_ApproveDownload',
		'unapprove' => 'Downloads_UnApproveDownload',
		'add' => 'Downloads_AddDownload',
		'add2' => 'Downloads_AddDownload2',
		'search' => 'Downloads_Search',
		'search2' => 'Downloads_Search2',
		'stats' => 'Downloads_Stats',
		'filespace' => 'Downloads_FileSpaceAdmin',
		'filelist' => 'Downloads_FileSpaceList',
		'recountquota' => 'Downloads_RecountFileQuotaTotals',
		'addquota' => 'Downloads_AddQuota',
		'deletequota' => 'Downloads_DeleteQuota',
		'next' => 'Downloads_NextDownload',
		'prev' => 'Downloads_PreviousDownload',
		'cusup' => 'Downloads_CustomUp',
		'cusdown' => 'Downloads_CustomDown',
		'cusadd' => 'Downloads_CustomAdd',
		'cusdelete' => 'Downloads_CustomDelete',
		'downfile' => 'Downloads_DownloadFile',
		'import' => 'Downloads_ImportDownloads',
		'importtp' => 'Downloads_ImportTinyPortalDownloads',

	);


	// Follow the sa or just go to  the main function
	if (isset($_GET['sa']))
		$sa = $_GET['sa'];
	else
		$sa = '';
		
	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		Downloads_MainView();


}

function Downloads_MainView()
{
	global $context, $scripturl, $mbname, $txt, $modSettings, $user_info, $smcFunc;

	TopDownloadTabs();

	// View the main Downloads

	// Is the user allowed to view the downloads?
	isAllowedTo('themes_view');

	// Load the main downloads template
	$context['sub_template']  = 'mainview';


	// Get the main groupid
	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];

	if (isset($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];
	else
		$cat = 0;

	if (!empty($cat))
	{
		require_once($sourcedir . '/Subs-Tema2.php');
		GetCatPermission($cat,'view');

		// Get category name used for the page title
		$dbresult1 = $smcFunc['db_query']('', "
		SELECT
			ID_CAT, title, roworder, description, image,
			disablerating, orderby, sortby,ID_PARENT
		FROM {db_prefix}tema_cat
		WHERE ID_CAT = $cat LIMIT 1");
		$row1 = $smcFunc['db_fetch_assoc']($dbresult1);
		$context['downloads_cat_name'] = $row1['title'];
		$context['downloads_sortby'] = $row1['sortby'];
		$context['downloads_orderby'] = $row1['orderby'];
		$context['downloads_cat_norate'] = $row1['disablerating'];
		if ($context['downloads_cat_norate'] == '')
			$context['downloads_cat_norate'] = 0;

		$smcFunc['db_free_result']($dbresult1);

		Downloads_GetParentLink($row1['ID_PARENT']);

		// Link Tree
		$context['linktree'][] = array(
					'url' => $scripturl . '?action=tema;cat=' . $cat,
					'name' => $context['downloads_cat_name']
				);

		// Set the page title
		$context['page_title'] = $mbname . ' - ' . $context['downloads_cat_name'];

		// Get the total number of pages
		$total = Downloads_GetTotalByCATID($cat);


		$context['start'] = (int) $_REQUEST['start'];

		$context['downloads_total'] = $total;


		// Check if we are sorting stuff heh
		$sortby = '';
		$orderby = '';
		if (isset($_REQUEST['sortby']))
		{
			switch ($_REQUEST['sortby'])
			{
				case 'date':
					$sortby = 'p.ID_FILE';

				break;
				case 'title':
					$sortby = 'p.title';
				break;

				case 'mostview':
					$sortby = 'p.views';
				break;

				case 'mostdowns':
					$sortby = 'p.totaldownloads';
				break;
				case 'filesize':
					$sortby = 'p.filesize';
				break;
				case 'membername':
					$sortby = 'm.real_name';
				break;

				case 'mostrated':
					$sortby = 'p.totalratings';
				break;


				default:
					$sortby = 'p.ID_FILE';
				break;
			}

			$sortby2 = $_REQUEST['sortby'];

			$context['downloads_sortby'] = $sortby2;
		}
		else
		{
			if (!empty($context['downloads_sortby']))
				$sortby = $context['downloads_sortby'];
			else
				$sortby = 'p.ID_FILE';

			$sortby2 = 'date';

			$context['downloads_sortby'] = $sortby2;
		}


		if (isset($_REQUEST['orderby']))
		{
			switch ($_REQUEST['orderby'])
			{
				case 'asc':
					$orderby = 'ASC';

				break;
				case 'desc':
					$orderby = 'DESC';
				break;



				default:
					$orderby = 'DESC';
				break;
			}

			$orderby2 = $_REQUEST['orderby'];

			$context['downloads_orderby2'] = $orderby2;
		}
		else
		{

			if (!empty($context['downloads_orderby']))
				$orderby = $context['downloads_orderby'];
			else
				$orderby = 'DESC';

			$orderby2 = 'desc';

			$context['downloads_orderby2'] = $orderby2;
		}


		// Show the downloads
		$dbresult = $smcFunc['db_query']('', "
		SELECT
			p.ID_FILE, p.totalratings, p.rating, p.filesize, p.views, p.title, p.id_member, m.real_name,
		 	 p.date, p.description, p.totaldownloads
		FROM {db_prefix}tema_file as p
			LEFT JOIN {db_prefix}members AS m ON (p.id_member = m.id_member)
		WHERE  p.ID_CAT = $cat AND p.approved = 1
		ORDER BY $sortby $orderby
		LIMIT $context[start]," . $modSettings['tema_set_files_per_page']);
		$context['downloads_files'] = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_files'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'totalratings' => $row['totalratings'],
			'rating' => $row['rating'],
			'filesize' => $row['filesize'],
			'views' => $row['views'],
			'id_member' => $row['id_member'],
			'real_name' => $row['real_name'],
			'date' => $row['date'],
			'description' => $row['description'],
			'totaldownloads' => $row['totaldownloads'],

			);

		}
		$smcFunc['db_free_result']($dbresult);



		$context['page_index'] = constructPageIndex($scripturl . '?action=tema;cat=' . $cat . ';sortby=' . $context['downloads_sortby'] . ';orderby=' . $context['downloads_orderby2'], $_REQUEST['start'], $total, $modSettings['tema_set_files_per_page']);




		if (!empty($modSettings['tema_who_viewing']))
		{
			$context['can_moderate_forum'] = allowedTo('moderate_forum');

				// SMF 1.1.x
				// Taken from Display.php
				// Start out with no one at all viewing it.
				$context['view_members'] = array();
				$context['view_members_list'] = array();
				$context['view_num_hidden'] = 0;
				$whoID = (string) $cat;

				// Search for members who have this downloads id set in their GET data.
				$request = $smcFunc['db_query']('', "
					SELECT
						lo.id_member, lo.log_time, mem.real_name, mem.member_name, mem.show_online,
						mg.online_color, mg.ID_GROUP, mg.group_name
					FROM {db_prefix}log_online AS lo
						LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lo.id_member)
						LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
					WHERE INSTR(lo.url, 's:9:\"downloads\";s:3:\"cat\";s:" . strlen($whoID ) .":\"$cat\";') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'");
				while ($row = $smcFunc['db_fetch_assoc']($request))
				{
					if (empty($row['id_member']))
						continue;

					if (!empty($row['online_color']))
						$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" style="color: ' . $row['online_color'] . ';">' . $row['real_name'] . '</a>';
					else
						$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';

					$is_buddy = in_array($row['id_member'], $user_info['buddies']);
					if ($is_buddy)
						$link = '<b>' . $link . '</b>';

					// Add them both to the list and to the more detailed list.
					if (!empty($row['show_online']) || allowedTo('moderate_forum'))
						$context['view_members_list'][$row['log_time'] . $row['member_name']] = empty($row['show_online']) ? '<i>' . $link . '</i>' : $link;
					$context['view_members'][$row['log_time'] . $row['member_name']] = array(
						'id' => $row['id_member'],
						'username' => $row['member_name'],
						'name' => $row['real_name'],
						'group' => $row['ID_GROUP'],
						'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
						'link' => $link,
						'is_buddy' => $is_buddy,
						'hidden' => empty($row['show_online']),
					);

					if (empty($row['show_online']))
						$context['view_num_hidden']++;
				}

				// The number of guests is equal to the rows minus the ones we actually used ;).
				$context['view_num_guests'] = $smcFunc['db_num_rows']($request) - count($context['view_members']);
				$smcFunc['db_free_result']($request);

				// Sort the list.
				krsort($context['view_members']);
				krsort($context['view_members_list']);

		}


	}
	else
	{
		$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'];

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		c.ID_CAT, c.title, p.view, c.roworder, c.description, c.image, c.filename, c.redirect
	FROM {db_prefix}tema_cat AS c
	LEFT JOIN {db_prefix}tema_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
	WHERE c.ID_PARENT = 0 ORDER BY c.roworder ASC");
	$context['downloads_cats'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_cats'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'view' => $row['view'],
			'roworder' => $row['roworder'],
			'description' => $row['description'],
			'filename' => $row['filename'],
			'redirect' => $row['redirect'],
			'image' => $row['image'],
			);

		}
		$smcFunc['db_free_result']($dbresult);


		// Downloads waiting for approval
		$dbresult3 = $smcFunc['db_query']('', "
		SELECT
			COUNT(*) as totalfiles
		FROM {db_prefix}tema_file
		WHERE approved = 0");
		$row2 = $smcFunc['db_fetch_assoc']($dbresult3);
		$totalfiles = $row2['totalfiles'];
		$smcFunc['db_free_result']($dbresult3);
		$context['downloads_waitapproval'] = $totalfiles;
		// Reported Downloads
		$dbresult4 = $smcFunc['db_query']('', "
		SELECT
			COUNT(*) as totalreport
		FROM {db_prefix}tema_report");
		$row2 = $smcFunc['db_fetch_assoc']($dbresult4);
		$totalreport = $row2['totalreport'];
		$smcFunc['db_free_result']($dbresult4);
		$context['downloads_totalreport'] = $totalreport;

		// Total reported Comments
		$dbresult6 = $smcFunc['db_query']('', "
		SELECT
			COUNT(*) as totalcreport
		FROM {db_prefix}tema_creport");
		$row2 = $smcFunc['db_fetch_assoc']($dbresult6);
		$totalcomments = $row2['totalcreport'];
		$smcFunc['db_free_result']($dbresult6);
		$context['downloads_totalcreport'] = $totalcomments;

	}

}

function Downloads_AddCategory()
{
	global $context, $mbname, $txt, $modSettings, $sourcedir;
	isAllowedTo('themes_manage');
	require_once($sourcedir . '/Subs-Tema2.php');
	AddCategory();
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_addcategory'];
	$context['sub_template']  = 'add_category';
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

}

function Downloads_AddCategory2()
{
	global $txt, $sourcedir, $smcFunc;
	isAllowedTo('themes_manage');
	$title = $smcFunc['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);
	$description = $smcFunc['htmlspecialchars']($_REQUEST['description'],ENT_QUOTES);
	$image =  htmlspecialchars($_REQUEST['image'],ENT_QUOTES);
	$boardselect = (int) $_REQUEST['boardselect'];
	$parent = (int) $_REQUEST['parent'];
	$locktopic = isset($_REQUEST['locktopic']) ? 1 : 0;
	$disablerating  = isset($_REQUEST['disablerating']) ? 1 : 0;
	if (empty($title))
	fatal_error($txt['tema_error_cat_title'],false);
		$sortby = '';
		$orderby = '';
		if (isset($_REQUEST['sortby']))
		{
			switch ($_REQUEST['sortby'])
			{
				case 'date':
					$sortby = 'p.ID_FILE';

				break;
				case 'title':
					$sortby = 'p.title';
				break;

				case 'mostview':
					$sortby = 'p.views';
				break;

				case 'mostrated':
					$sortby = 'p.totalratings';
				break;

				case 'mostdowns':
					$sortby = 'p.totaldownloads';
				break;
				case 'filesize':
					$sortby = 'p.filesize';
				break;
				case 'membername':
					$sortby = 'm.real_name';
				break;


				default:
					$sortby = 'p.ID_FILE';
				break;
			}

		}
		else
		{
			$sortby = 'p.ID_FILE';
		}


		if (isset($_REQUEST['orderby']))
		{
			switch ($_REQUEST['orderby'])
			{
				case 'asc':
					$orderby = 'ASC';

				break;
				case 'desc':
					$orderby = 'DESC';
				break;

				default:
					$orderby = 'DESC';
				break;
			}
		}
		else
		{
			$orderby = 'DESC';
		}

	require_once($sourcedir . '/Subs-Tema2.php');
	AddCategory2($title,$description,$image,$boardselect,$parent,$locktopic,$disablerating,$sortby,$orderby);	
	redirectexit('action=tema;sa=admincat');
}

function Downloads_EditCategory()
{
	global $context, $mbname, $txt, $modSettings, $sourcedir;
	isAllowedTo('themes_manage');
	$cat = (int) $_REQUEST['cat'];
	if (empty($cat))
		fatal_error($txt['tema_error_no_cat']);
	require_once($sourcedir . '/Subs-Tema2.php');
	EditCategory($cat);
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_editcategory'];
	$context['sub_template']  = 'edit_category';
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

}

function Downloads_EditCategory2()
{
	global $txt,$sourcedir, $smcFunc;
	
	isAllowedTo('themes_manage');
	$title = $smcFunc['htmlspecialchars']($_REQUEST['title'], ENT_QUOTES);
	$description = $smcFunc['htmlspecialchars']($_REQUEST['description'], ENT_QUOTES);
	$catid = (int) $_REQUEST['catid'];
	$image = $smcFunc['htmlspecialchars']($_REQUEST['image'], ENT_QUOTES);
	$parent = (int) $_REQUEST['parent'];
	$boardselect = (int) $_REQUEST['boardselect'];
	$locktopic = isset($_REQUEST['locktopic']) ? 1 : 0;
	$disablerating  = isset($_REQUEST['disablerating']) ? 1 : 0;
	if (empty($title))
		fatal_error($txt['tema_error_cat_title'],false);

		$sortby = '';
		$orderby = '';
		if (isset($_REQUEST['sortby']))
		{
			switch ($_REQUEST['sortby'])
			{
				case 'date':
					$sortby = 'p.ID_FILE';

				break;
				case 'title':
					$sortby = 'p.title';
				break;

				case 'mostview':
					$sortby = 'p.views';
				break;

				case 'mostrated':
					$sortby = 'p.totalratings';
				break;

				case 'mostdowns':
					$sortby = 'p.totaldownloads';
				break;
				case 'filesize':
					$sortby = 'p.filesize';
				break;
				case 'membername':
					$sortby = 'm.real_name';
				break;

				default:
					$sortby = 'p.ID_FILE';
				break;
			}

		}
		else
		{
			$sortby = 'p.ID_FILE';
		}


		if (isset($_REQUEST['orderby']))
		{
			switch ($_REQUEST['orderby'])
			{
				case 'asc':
					$orderby = 'ASC';

				break;
				case 'desc':
					$orderby = 'DESC';
				break;

				default:
					$orderby = 'DESC';
				break;
			}
		}
		else
		{
			$orderby = 'DESC';
		}
	require_once($sourcedir . '/Subs-Tema2.php');
	EditCategory2($title,$description,$catid,$image,$boardselect,$parent,$locktopic,$disablerating,$sortby,$orderby);		
	redirectexit('action=tema;sa=admincat');

}
function Downloads_DeleteCategory()
{
	global $context, $mbname, $txt, $sourcedir;
	isAllowedTo('themes_manage');
	$catid = (int) $_REQUEST['cat'];
	if (empty($catid))
		fatal_error($txt['tema_error_no_cat']);
	require_once($sourcedir . '/Subs-Tema2.php');
	DeleteCategory($catid);
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_delcategory'];
	$context['sub_template']  = 'delete_category';
}

function Downloads_DeleteCategory2()
{
	global $sourcedir;
	isAllowedTo('themes_manage');
	$catid = (int) $_REQUEST['catid'];
	require_once($sourcedir . '/Subs-Tema2.php');
	DeleteCategory2($catid);
	Downloads_RecountFileQuotaTotals(false);
	redirectexit('action=tema;sa=admincat');
}

function Downloads_ViewDownload()
{
	global $context, $mbname, $modSettings, $txt, $sourcedir;
	isAllowedTo('themes_view');
	TopDownloadTabs();
	if (isset($_REQUEST['down']))
		$id = (int) $_REQUEST['down'];
	if (isset($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected'], false);
	require_once($sourcedir . '/Subs-Tema2.php');
	ViewDownload($id);
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	$context['sub_template']  = 'view_download';
	$context['page_title'] = $mbname . ' - ' . $context['downloads_file']['title'];
}

function Downloads_AddDownload()
{
	global $context, $mbname, $txt, $modSettings, $sourcedir;
	isAllowedTo('themes_add');
	if (isset($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];
	else
		$cat = 0;
	require_once($sourcedir . '/Subs-Tema2.php');
	AddDownload($cat);
	$context['sub_template']  = 'add_download';
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_form_adddownload'];
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	require_once($sourcedir . '/Subs-Editor.php');
	$editorOptions = array(
		'id' => 'descript',
		'value' => '',
		'width' => '90%',
		'form' => 'picform',
		'labels' => array(
			'post_button' => '',
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];
}

function Downloads_AddDownload2()
{
	global $txt, $modSettings, $sourcedir, $user_info, $smcFunc;
	isAllowedTo('themes_add');
	if (!empty($_REQUEST['descript_mode']) && isset($_REQUEST['descript']))
	{
		require_once($sourcedir . '/Subs-Editor.php');
		$_REQUEST['descript'] = html_to_bbc($_REQUEST['descript']);
		$_REQUEST['descript'] = un_htmlspecialchars($_REQUEST['descript']);
	}
	if (!is_writable($modSettings['tema_path']))
		fatal_error($txt['tema_write_error'] . $modSettings['tema_path']);
	$title = $smcFunc['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);
	$description = $smcFunc['htmlspecialchars']($_REQUEST['descript'],ENT_QUOTES);
	$keywords = $smcFunc['htmlspecialchars']($_REQUEST['keywords'],ENT_QUOTES);
	$cat = (int) $_REQUEST['cat'];
	$fileurl = $smcFunc['htmlspecialchars']($_REQUEST['fileurl'],ENT_QUOTES);
	$demourl = $smcFunc['htmlspecialchars']($_REQUEST['demourl'],ENT_QUOTES);
	$pictureurl = $smcFunc['htmlspecialchars']($_REQUEST['pictureurl'],ENT_QUOTES);
	$filesize = 0;
	$approved = (allowedTo('themes_autoapprove') ? 1 : 0);
	if ($title == '')
		fatal_error($txt['tema_error_no_title'],false);
	if ($cat == '')
		fatal_error($txt['tema_error_no_cat'],false);
	require_once($sourcedir . '/Subs-Tema2.php');
	AddDownload2($title,$description,$keywords,$cat,$fileurl,$demourl,$pictureurl,$filesize,$approved );
		if ($user_info['id'] != 0)
			redirectexit('action=tema;sa=myfiles;u=' . $user_info['id']);
		else
			redirectexit('action=tema;cat=' . $cat);
}

function Downloads_EditDownload()
{
	global $context, $txt, $user_info,$mbname,$modSettings, $sourcedir;
	is_not_guest();
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
		if ($user_info['is_guest'])
			$groupid = -1;
		else
			$groupid =  $user_info['groups'][0];
	require_once($sourcedir . '/Subs-Tema2.php');
	EditDownload($id,$groupid);
	require_once($sourcedir . '/Subs-Editor.php');
	$editorOptions = array(
		'id' => 'descript',
		'value' => $context['downloads_file']['description'],
		'width' => '90%',
		'form' => 'picform',
		'labels' => array(
			'post_button' => '',
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_form_editdownload'];
	$context['sub_template']  = 'edit_download';
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
}

function Downloads_EditDownload2()
{
	global $txt, $modSettings, $sourcedir, $smcFunc, $user_info;

	is_not_guest();
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
	if (!empty($_REQUEST['descript_mode']) && isset($_REQUEST['descript']))
	{
		require_once($sourcedir . '/Subs-Editor.php');
		$_REQUEST['descript'] = html_to_bbc($_REQUEST['descript']);
		$_REQUEST['descript'] = un_htmlspecialchars($_REQUEST['descript']);

	}
		$title = $smcFunc['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);
		$description = $smcFunc['htmlspecialchars']($_REQUEST['descript'],ENT_QUOTES);
		$keywords = $smcFunc['htmlspecialchars']($_REQUEST['keywords'],ENT_QUOTES);
		$cat = (int) $_REQUEST['cat'];
		$fileurl = htmlspecialchars($_REQUEST['fileurl'],ENT_QUOTES);
		$pictureurl = htmlspecialchars($_REQUEST['pictureurl'],ENT_QUOTES);
		$demourl = htmlspecialchars($_REQUEST['demourl'],ENT_QUOTES);
		$filesize = 0;
		$approved = (allowedTo('themes_autoapprove') ? 1 : 0);


		if ($title == '')
			fatal_error($txt['tema_error_no_title'],false);
		if ($cat == '')
			fatal_error($txt['tema_error_no_cat'],false);
		require_once($sourcedir . '/Subs-Tema2.php');
	EditDownload2($id,$title,$description,$keywords,$cat,$fileurl,$pictureurl,$demourl,$filesize,$approved);
}

function Downloads_DeleteDownload()
{
	global $context, $mbname, $txt, $sourcedir, $user_info;
	is_not_guest();
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
		require_once($sourcedir . '/Subs-Tema2.php');
		DeleteDownload($id);
	if (allowedTo('themes_manage') || (allowedTo('themes_delete') && $user_info['id'] == $context['downloads_file']['id_member']))
	{
		$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_form_deldownload'];
		$context['sub_template']  = 'delete_download';
	}
	else
		fatal_error($txt['tema_error_nodelete_permission']);
}

function Downloads_DeleteDownload2()
{
	global $txt, $user_info,$sourcedir;
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
		require_once($sourcedir . '/Subs-Tema2.php');
		DeleteDownload2($id);
	if (allowedTo('themes_manage') || (allowedTo('themes_delete') && $user_info['id'] == $memID))
	{
		DeleteFileByID($id);
		UpdateCategoryTotals($row['ID_CAT']);
		redirectexit('action=tema;sa=myfiles;u=' . $user_info['id']);
	}
	else
		fatal_error($txt['tema_error_nodelete_permission']);
}
function Downloads_ReportDownload()
{
	global $context, $mbname, $txt;
	isAllowedTo('themes_report');
	is_not_guest();
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
     TopDownloadTabs();
	$context['downloads_file_id'] = $id;
	$context['sub_template']  = 'report_download';
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_form_reportdownload'];
}

function Downloads_ReportDownload2()
{
	global $txt, $smcFunc, $user_info,$sourcedir;
	isAllowedTo('themes_report');
	$comment = $smcFunc['htmlspecialchars']($_REQUEST['comment'],ENT_QUOTES);
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
	if ($comment == '')
		fatal_error($txt['tema_error_no_comment'],false);
	$commentdate = time();
	$memid = $user_info['id'];
	require_once($sourcedir . '/Subs-Tema2.php');
	ReportDownload2($comment,$id,$commentdate,$memid);
	redirectexit('action=tema;sa=view;down=' . $id);
}

function Downloads_CatUp()
{
	global  $sourcedir;
	isAllowedTo('themes_manage');
	$cat = (int) $_REQUEST['cat'];
	require_once($sourcedir . '/Subs-Tema2.php');
	ReOrderCats($cat);
	CatUp($cat);
}

function Downloads_CatDown()
{
	global  $sourcedir;
	isAllowedTo('themes_manage');
	$cat = (int) $_REQUEST['cat'];
	require_once($sourcedir . '/Subs-Tema2.php');
	ReOrderCats($cat);
	CatDown($cat);
}

function Downloads_MyFiles()
{
	global $context, $mbname, $txt, $sourcedir,$scripturl,$modSettings;
	isAllowedTo('themes_view');
	TopDownloadTabs();
	$u = (int) $_REQUEST['u'];
	if (empty($u))
		fatal_error($txt['tema_error_no_user_selected']);
	require_once($sourcedir . '/Subs-Tema2.php');
	MyFiles($u);
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $context['downloads_userdownloads_name'];
	$context['sub_template']  = 'myfiles';
	$context['page_index'] = constructPageIndex($scripturl . '?action=tema;sa=myfiles;u=' . $context['downloads_userid'], $_REQUEST['start'], $context['downloads_total'], $modSettings['tema_set_files_per_page']);
}

function Downloads_ApproveList()
{
	global $context, $mbname, $txt, $scripturl, $sourcedir;
	isAllowedTo('themes_manage');
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_form_approvedownloads'];
	$context['sub_template']  = 'approvelist';
	$al = (int) $_REQUEST['start'];
	require_once($sourcedir . '/Subs-Tema2.php');
	ApproveList($al);
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=tema;sa=approvelist', $_REQUEST['start'], $context['downloads_total'], 10);
}

function Downloads_ApproveDownload()
{
	global $txt,$sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
	require_once($sourcedir . '/Subs-Tema2.php');
	ApproveFileByID($id);
	redirectexit('action=admin;area=tema;sa=approvelist');
}

function Downloads_UnApproveDownload()
{
	global $txt,$sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
	require_once($sourcedir . '/Subs-Tema2.php');
	UnApproveFileByID($id);
	redirectexit('action=admin;area=tema;sa=approvelist');
}

function Downloads_ReportList()
{
	global $context, $mbname, $txt, $sourcedir;
	isAllowedTo('themes_manage');
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_form_reportdownloads'];
	$context['sub_template']  = 'reportlist';
	require_once($sourcedir . '/Subs-Tema2.php');
	ReportList();
}

function Downloads_DeleteReport()
{
	global $txt, $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_report_selected']);
	require_once($sourcedir . '/Subs-Tema2.php');
	DeleteReport($id);
	redirectexit('action=admin;area=tema;sa=reportlist');
}

function Downloads_Search()
{
	global $context, $mbname, $txt, $user_info, $sourcedir;
	TopDownloadTabs();
	isAllowedTo('themes_view');
	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];
	require_once($sourcedir . '/Subs-Tema2.php');
	Search($groupid);
	$context['sub_template']  = 'search';
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_search'];
}

function Downloads_Search2()
{
	global $context, $mbname, $txt, $smcFunc;

	// Is the user allowed to view the downloads?
	isAllowedTo('themes_view');

	TopDownloadTabs();
	
	if (isset($_REQUEST['q']))
	{
		$data = json_decode(base64_decode($_REQUEST['q']),true);
		@$_REQUEST['cat'] = $data['cat'];
		@$_REQUEST['key'] = $data['keyword'];
		@$_REQUEST['searchkeywords'] = $data['searchkeywords'];
		@$_REQUEST['searchtitle'] = $data['searchtitle'];
		@$_REQUEST['searchdescription'] = $data['searchdescription'];
		@$_REQUEST['searchcustom'] = $data['searchcustom'];
		@$_REQUEST['daterange'] = $data['daterange'];
		@$_REQUEST['pic_postername'] = $data['pic_postername'];
		@$_REQUEST['searchfor'] = $data['searchfor'];
		
	}	



		@$cat = (int) $_REQUEST['cat'];

		// Check if keyword search was selected
		@$keyword =  $smcFunc['htmlspecialchars']($_REQUEST['key'],ENT_QUOTES);
		$searchArray = array();
		$searchArray['keyword'] = $keyword;
		$context['downloads_search_query_encoded'] = base64_encode(json_encode($searchArray));

		if ($keyword == '')
		{
			// Probably a normal Search
			if (empty($_REQUEST['searchfor']))
				fatal_error($txt['tema_error_no_search'],false);

			$searchfor =  $smcFunc['htmlspecialchars']($_REQUEST['searchfor'],ENT_QUOTES);


			if ($smcFunc['strlen']($searchfor) <= 3)
				fatal_error($txt['tema_error_search_small'],false);

			// Check the search options
			@$searchkeywords = $_REQUEST['searchkeywords'];
			@$searchtitle = $_REQUEST['searchtitle'];
			@$searchdescription = $_REQUEST['searchdescription'];
			@$daterange = (int) $_REQUEST['daterange'];
			$memid = 0;

			// Check if searching by member id
			if (!empty($_REQUEST['pic_postername']))
			{
				$pic_postername = str_replace('"','', $_REQUEST['pic_postername']);
				$pic_postername = str_replace("'",'', $pic_postername);
				$pic_postername = str_replace('\\','', $pic_postername);
				$pic_postername = $smcFunc['htmlspecialchars']($pic_postername, ENT_QUOTES);
				$searchArray['pic_postername'] = $pic_postername;


				$dbresult = $smcFunc['db_query']('', "
						SELECT
							real_name, id_member
						FROM {db_prefix}members
						WHERE real_name = '$pic_postername' OR member_name = '$pic_postername'  LIMIT 1");
						$row = $smcFunc['db_fetch_assoc']($dbresult);
						$smcFunc['db_free_result']($dbresult);

					if ($smcFunc['db_affected_rows']() != 0)
					{
						$memid = $row['id_member'];
					}
			}
			
			
			$searchArray['searchfor'] = $searchfor;
			$searchArray['searchkeywords'] = $searchkeywords;
			$searchArray['cat'] = $cat;
			$searchArray['searchtitle'] = $searchtitle;
			$searchArray['searchdescription'] = $searchdescription;
			$searchArray['daterange'] = $daterange;
			$context['downloads_search_query_encoded'] = base64_encode(json_encode($searchArray));
			
			
			$context['catwhere'] = '';


			if ($cat != 0)
				$context['catwhere'] = "p.ID_CAT = $cat AND ";

			// Check if searching by member id
			if ($memid != 0)
				$context['catwhere'] .= "p.id_member = $memid AND ";

			// Date Range check
			if ($daterange!= 0)
			{
				$currenttime = time();
				$pasttime = $currenttime - ($daterange * 24 * 60 * 60);

				$context['catwhere'] .=  "(p.date BETWEEN '" . $pasttime . "' AND '" . $currenttime . "')  AND";
			}

			$s1 = 1;
			$searchquery = '';
			if ($searchtitle)
				$searchquery = "p.title LIKE '%$searchfor%' ";
			else
				$s1 = 0;

			$s2 = 1;
			if ($searchdescription)
			{
				if ($s1 == 1)
					$searchquery = "p.title LIKE '%$searchfor%' OR p.description LIKE '%$searchfor%'";
				else
					$searchquery = "p.description LIKE '%$searchfor%'";
			}
			else
				$s2 = 0;

			if ($searchkeywords)
			{
				if ($s1 == 1 || $s2 == 1)
					$searchquery .= " OR p.keywords LIKE '$searchfor'";
				else
					$searchquery = "p.keywords LIKE '$searchfor'";
			}


			if ($searchquery == '')
				$searchquery = "p.title LIKE '%$searchfor%' ";

			$context['downloads_search_query'] = $searchquery;



			$context['downloads_search'] = $searchfor;
		}
		else
		{
			// Search for the keyword


			//Debating if I should add string length check for keywords...
			//if (strlen($keyword) <= 3)
				//fatal_error($txt['tema_error_search_small']);

			$context['downloads_search'] = $keyword;

			$context['downloads_search_query'] = "p.keywords LIKE '$keyword'";
		}

	$downloads_where = '';
	if (isset($context['catwhere']))
		$downloads_where = $context['catwhere'];

	$context['downloads_where'] = $downloads_where;


	$context['start'] = (int) $_REQUEST['start'];

    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	p.ID_FILE
    FROM {db_prefix}tema_file as p
    WHERE  " . $downloads_where . " p.approved = 1 AND (" . $context['downloads_search_query'] . ")");
    $numrows = $smcFunc['db_num_rows']($dbresult);
    $smcFunc['db_free_result']($dbresult);

    $total = $numrows;
	$context['downloads_total'] = $total;


    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	p.ID_FILE, p.ID_CAT, p.rating, p.filesize, p.title,
    	p.views, p.id_member, m.real_name, p.date, p.totaldownloads, p.totalratings
    FROM {db_prefix}tema_file as p
   	 	LEFT JOIN {db_prefix}members AS m ON (m.id_member = p.id_member)
    WHERE  " . $downloads_where . " p.approved = 1 AND (" . $context['downloads_search_query'] . ")
    LIMIT $context[start],10");
    $context['downloads_files'] = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_files'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'totalratings' => $row['totalratings'],
			'rating' => $row['rating'],
			'filesize' => $row['filesize'],
			'views' => $row['views'],
			'id_member' => $row['id_member'],
			'real_name' => $row['real_name'],
			'date' => $row['date'],
			'totaldownloads' => $row['totaldownloads'],

			);

		}
	$smcFunc['db_free_result']($dbresult);


	$context['sub_template']  = 'search_results';

	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_searchresults'];
}

function Downloads_RateDownload()
{
	global $txt, $sourcedir, $user_info;
	is_not_guest();
	isAllowedTo('themes_ratefile');
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
	$rating = (int) $_REQUEST['rating'];
	if (empty($rating))
		fatal_error($txt['tema_error_no_rating_selected']);
	$memid = $user_info['id'];
	require_once($sourcedir . '/Subs-Tema2.php');
	RateDownload($id,$rating,$memid);
	redirectexit('action=tema;sa=view;down=' . $id);
}

function Downloads_ViewRating()
{
	global $context, $mbname, $txt, $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
	$context['downloads_id'] = $id;
	require_once($sourcedir . '/Subs-Tema2.php');
	ViewRating($id);
	$context['sub_template']  = 'view_rating';
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_form_viewratings'];
}

function Downloads_DeleteRating()
{
	global $sourcedir, $txt, $smcFunc;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_rating_selected']);
	require_once($sourcedir . '/Subs-Tema2.php');
	DeleteRating($id);
}

function Downloads_Stats()
{
	global $context, $mbname,$txt, $sourcedir;
	isAllowedTo('themes_view');
	TopDownloadTabs();
	$context['sub_template']  = 'stats';
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_stats'];
	require_once($sourcedir . '/Subs-Tema2.php');
	Stats();
}


function Downloads_FileSpaceAdmin()
{
	global $mbname, $txt, $context, $scripturl, $sourcedir;
	isAllowedTo('themes_manage');
	loadLanguage('Admin');
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_filespace'];
	$context['sub_template']  = 'filespace';
	$al = (int) $_REQUEST['start'];
	require_once($sourcedir . '/Subs-Tema2.php');
	FileSpaceAdmin($al);
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=tema;sa=filespace', $_REQUEST['start'], $total, 20);
}

function Downloads_FileSpaceList()
{
	global $mbname, $txt, $context, $scripturl, $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_user_selected']);
	$al = (int) $_REQUEST['start'];
	require_once($sourcedir . '/Subs-Tema2.php');
	FileSpaceList($id,$al);
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_filespace'] . ' - ' . $context['downloads_filelist_real_name'];
	$context['sub_template']  = 'filelist';
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=tema;sa=filelist&id=' . $context['downloads_filelist_userid'], $_REQUEST['start'], $context['downloads_total'], 20);
}

function Downloads_RecountFileQuotaTotals($redirect = true)
{
	global $sourcedir;
	if ($redirect == true)
		isAllowedTo('themes_manage');
	require_once($sourcedir . '/Subs-Tema2.php');
	RecountFileQuotaTotals();
	if ($redirect == true)
		redirectexit('action=admin;area=tema;sa=filespace');
}
function Downloads_AddQuota()
{
	global $txt, $sourcedir;
	isAllowedTo('themes_manage');
	$groupid = (int) $_REQUEST['groupname'];
	$filelimit = (double) $_REQUEST['filelimit'];
	if (empty($filelimit))
	{
		fatal_error($txt['tema_error_noquota'],false);
	}
	require_once($sourcedir . '/Subs-Tema2.php');
	AddQuota($groupid,$filelimit);
	redirectexit('action=admin;area=tema;sa=filespace');
}

function Downloads_DeleteQuota()
{
	global $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	require_once($sourcedir . '/Subs-Tema2.php');
	DeleteQuota($id);
	redirectexit('action=admin;area=tema;sa=filespace');
}

function Downloads_CatPerm()
{
	global $mbname, $txt, $context, $sourcedir;
	isAllowedTo('themes_manage');
	$cat = (int) $_REQUEST['cat'];
	if (empty($cat))
		fatal_error($txt['tema_error_no_cat']);
	loadLanguage('Admin');
	require_once($sourcedir . '/Subs-Tema2.php');
	CatPerm($cat);
	$context['sub_template']  = 'catperm';
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_catperm'] . ' -' . $context['downloads_cat_name'];
}

function Downloads_CatPerm2()
{
	global $sourcedir;
	isAllowedTo('themes_manage');
	$groupname = (int) $_REQUEST['groupname'];
	$cat = (int) $_REQUEST['cat'];
	$view = isset($_REQUEST['view']) ? 1 : 0;
	$viewdownload = isset($_REQUEST['viewdownload']) ? 1 : 0;
	$add = isset($_REQUEST['add']) ? 1 : 0;
	$edit = isset($_REQUEST['edit']) ? 1 : 0;
	$delete = isset($_REQUEST['delete']) ? 1 : 0;
	require_once($sourcedir . '/Subs-Tema2.php');
	CatPerm2($groupname,$cat,$view,$viewdownload,$add,$edit,$delete);
	redirectexit('action=tema;sa=catperm;cat=' . $cat);
}

function Downloads_CatPermList()
{
	global $mbname, $txt, $context, $sourcedir;
	isAllowedTo('themes_manage');
	$context['sub_template']  = 'catpermlist';
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_catpermlist'];
	require_once($sourcedir . '/Subs-Tema2.php');
	CatPermList();
}

function Downloads_CatPermDelete()
{
	global $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	require_once($sourcedir . '/Subs-Tema2.php');
	CatPermDelete($id);
	redirectexit('action=admin;area=tema;sa=catpermlist');
}

function Downloads_PreviousDownload()
{
	global $txt, $sourcedir;
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
	require_once($sourcedir . '/Subs-Tema2.php');
	PreviousDownload($id);
}

function Downloads_NextDownload()
{
	global $txt, $sourcedir;
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
	require_once($sourcedir . '/Subs-Tema2.php');
	NextDownload($id);
}

function Downloads_CatImageDelete()
{
	global $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		exit;
	require_once($sourcedir . '/Subs-Tema2.php');
	CatImageDelete($id);
	redirectexit('action=tema;sa=editcat;cat=' . $id);
}
function Downloads_FileImageDelete()
{
	global $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		exit;
	require_once($sourcedir . '/Subs-Tema2.php');
	FileImageDelete($id);
	redirectexit('action=tema;sa=edit&id=' . $id);
}
function Downloads_BulkActions()
{
	global $sourcedir;
		isAllowedTo('themes_manage');
	if (isset($_REQUEST['files']))
	{
		$baction = $_REQUEST['doaction'];
		require_once($sourcedir . '/Subs-Tema2.php');
		foreach ($_REQUEST['files'] as $value)
		{

			if ($baction == 'approve')
				ApproveFileByID($value);
			if ($baction == 'delete')
				DeleteFileByID($value);

		}
	}
	redirectexit('action=admin;area=tema;sa=approvelist');
}

function Downloads_CustomUp()
{
	global $txt, $smcFunc;

	// Check Permission
	isAllowedTo('themes_manage');
	// Get the id
	$id = (int) $_REQUEST['id'];

	Downloads_ReOrderCustom($id);

	// Check if there is a category above it
	// First get our row order
	$dbresult1 = $smcFunc['db_query']('', "
	SELECT
		ID_CAT, ID_CUSTOM, roworder
	FROM {db_prefix}tema_custom_field
	WHERE ID_CUSTOM = $id");
	$row = $smcFunc['db_fetch_assoc']($dbresult1);

	$ID_CAT = $row['ID_CAT'];
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o--;

	$smcFunc['db_free_result']($dbresult1);
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_CUSTOM, roworder
	FROM {db_prefix}tema_custom_field
	WHERE ID_CAT = $ID_CAT AND roworder = $o");

	if ($smcFunc['db_affected_rows']()== 0)
		fatal_error($txt['tema_error_nocustom_above'], false);
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);


	// Swap the order Id's
	$smcFunc['db_query']('', "UPDATE {db_prefix}tema_custom_field
		SET roworder = $oldrow WHERE ID_CUSTOM = " .$row2['ID_CUSTOM']);

	$smcFunc['db_query']('', "UPDATE {db_prefix}tema_custom_field
		SET roworder = $o WHERE ID_CUSTOM = $id");


	$smcFunc['db_free_result']($dbresult);

	// Redirect to index to view cats
	redirectexit('action=tema;sa=editcat;cat=' . $ID_CAT);

}

function Downloads_CustomDown()
{
	global $txt, $smcFunc;

	isAllowedTo('themes_manage');

	// Get the id
	$id = (int) $_REQUEST['id'];

	Downloads_ReOrderCustom($id);

	// Check if there is a category below it
	// First get our row order
	$dbresult1 = $smcFunc['db_query']('', "
	SELECT
		ID_CUSTOM,ID_CAT, roworder
	FROM {db_prefix}tema_custom_field
	WHERE ID_CUSTOM = $id LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult1);
	$ID_CAT = $row['ID_CAT'];

	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o++;

	$smcFunc['db_free_result']($dbresult1);
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_CUSTOM, ID_CAT, roworder
	FROM {db_prefix}tema_custom_field
	WHERE ID_CAT = $ID_CAT AND roworder = $o");
	if ($smcFunc['db_affected_rows']()== 0)
		fatal_error($txt['tema_error_nocustom_below'], false);
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);

	// Swap the order Id's
	$smcFunc['db_query']('', "UPDATE {db_prefix}tema_custom_field
		SET roworder = $oldrow WHERE ID_CUSTOM = " .$row2['ID_CUSTOM']);

	$smcFunc['db_query']('', "UPDATE {db_prefix}tema_custom_field
		SET roworder = $o WHERE ID_CUSTOM = $id");


	$smcFunc['db_free_result']($dbresult);


	// Redirect to index to view cats
	redirectexit('action=tema;sa=editcat;cat=' . $ID_CAT);

}

function Downloads_CustomAdd()
{
	global $txt, $smcFunc;

	// Check Permission
	isAllowedTo('themes_manage');

	$id = (int) $_REQUEST['id'];

	$title = $smcFunc['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);
	$defaultvalue = $smcFunc['htmlspecialchars']($_REQUEST['defaultvalue'],ENT_QUOTES);
	$required = isset($_REQUEST['required']) ? 1 : 0;


	if ($title == '')
		fatal_error($txt['tema_custom_err_title'], false);


	$smcFunc['db_query']('', "INSERT INTO {db_prefix}tema_custom_field
			(ID_CAT,title, defaultvalue, is_required)
		VALUES ($id,'$title','$defaultvalue', '$required')");


	// Redirect back to the edit category page
	redirectexit('action=tema;sa=editcat;cat=' . $id);

}

function Downloads_CustomDelete()
{
	global $smcFunc;

	// Check Permission
	isAllowedTo('themes_manage');

	// Custom ID
	$id = (int) $_REQUEST['id'];

	// Get the CAT ID to redirect to the page
	$result = $smcFunc['db_query']('', "
	SELECT
		ID_CAT
	FROM {db_prefix}tema_custom_field
	WHERE ID_CUSTOM =  $id LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);


	// Delete all custom data for downloads that use it
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_custom_field_data
	WHERE ID_CUSTOM = $id ");

	// Finaly delete the field
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_custom_field
	WHERE ID_CUSTOM = $id LIMIT 1");

	// Redirect to the edit category page
	redirectexit('action=tema;sa=editcat;cat=' . $row['ID_CAT']);

}

function Downloads_ReOrderCustom($id)
{
	global $smcFunc;

	// Get the Category ID by id
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_CAT, roworder
	FROM {db_prefix}tema_custom_field
	WHERE ID_CUSTOM = $id");
	$row1 = $smcFunc['db_fetch_assoc']($dbresult);
	$ID_CAT = $row1['ID_CAT'];
	$smcFunc['db_free_result']($dbresult);

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_CUSTOM, roworder
	FROM {db_prefix}tema_custom_field
	WHERE ID_CAT = $ID_CAT ORDER BY roworder ASC");
	if ($smcFunc['db_affected_rows']() != 0)
	{
		$count = 1;
		while($row2 = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$smcFunc['db_query']('', "UPDATE {db_prefix}tema_custom_field
			SET roworder = $count WHERE ID_CUSTOM = " . $row2['ID_CUSTOM']);
			$count++;
		}
	}
	$smcFunc['db_free_result']($dbresult);
}


function Downloads_ComputeNextFolderID($ID_FILE)
{
	global $modSettings;

	$folderid = floor($ID_FILE / 1000);

	// If the current folder ID does not match the new folder ID update the settings
	if ($modSettings['tema_folder_id'] != $folderid)
		updateSettings(array('tema_folder_id' => $folderid));


}

function Downloads_CreateDownloadFolder()
{
	global $modSettings;

	$newfolderpath = $modSettings['tema_path'] . $modSettings['tema_folder_id'] . '/';

	// Check if the folder exists if it doess just exit
	if  (!file_exists($newfolderpath))
	{
		// If the folder does not exist then create it
		@mkdir ($newfolderpath);
		// Try to make sure that the correct permissions are on the folder
		@chmod ($newfolderpath,0755);
	}

}

function Downloads_GetFileTotals($ID_CAT)
{
	global $modSettings, $subcats_linktree, $scripturl, $smcFunc;

	$total = 0;

	$total += Downloads_GetTotalByCATID($ID_CAT);
	$subcats_linktree = '';

	// Get the child categories to this category
	if ($modSettings['tema_set_count_child'])
	{
		$dbresult3 = $smcFunc['db_query']('', "
		SELECT
			ID_CAT, total, title
		FROM {db_prefix}tema_cat WHERE ID_PARENT = $ID_CAT");
		while($row3 = $smcFunc['db_fetch_assoc']($dbresult3))
		{
			$subcats_linktree .= '<a href="' . $scripturl . '?action=tema;cat=' . $row3['ID_CAT'] . '">' . $row3['title'] . '</a>&nbsp;&nbsp;';

			if ($row3['total'] == -1)
			{
				$dbresult = $smcFunc['db_query']('', "
				SELECT
					COUNT(*) AS total
				FROM {db_prefix}tema_file
				WHERE ID_CAT = " . $row3['ID_CAT'] . " AND approved = 1");
				$row = $smcFunc['db_fetch_assoc']($dbresult);
				$total2 = $row['total'];
				$smcFunc['db_free_result']($dbresult);


				$dbresult = $smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat SET total = $total2 WHERE ID_CAT =  " . $row3['ID_CAT'] . " LIMIT 1");
			}
		}
		$smcFunc['db_free_result']($dbresult3);

/*
		$dbresult3 = $smcFunc['db_query']('', "
		SELECT
			SUM(total) AS finaltotal
		FROM {db_prefix}tema_cat
		WHERE ID_PARENT = $ID_CAT");
		$row3 = $smcFunc['db_fetch_assoc']($dbresult3);

		$smcFunc['db_free_result']($dbresult3);
		if ($row3['finaltotal'] != '')
			$total += $row3['finaltotal'];
*/
		$dbresult3 = $smcFunc['db_query']('', "
		SELECT
			total, ID_CAT, ID_PARENT
		FROM {db_prefix}tema_cat
		WHERE ID_PARENT <> 0");
		
		$childArray = array();
		while($row3 = $smcFunc['db_fetch_assoc']($dbresult3))
		{
			$childArray[] = $row3;
		}
	
		$total += Downloads_GetFileTotalsByParent($ID_CAT,$childArray);

	}


	return $total;
}

function Downloads_GetFileTotalsByParent($ID_PARENT,$data)
{
	$total = 0;
	foreach($data as $row)
	{
		if ($row['ID_PARENT'] == $ID_PARENT)
		{
			$total += $row['total'];
			$total += Downloads_GetFileTotalsByParent($row['ID_CAT'],$data);
		}
	}
	
	return $total;
}




function Downloads_GetTotalByCATID($ID_CAT)
{
	global $smcFunc;

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		total
	FROM {db_prefix}tema_cat
	WHERE ID_CAT = $ID_CAT");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if ($row['total'] != -1)
		return $row['total'];
	else
	{
		$dbresult = $smcFunc['db_query']('', "
		SELECT
			COUNT(*) AS total
		FROM {db_prefix}tema_file
		WHERE ID_CAT = $ID_CAT AND approved = 1");
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$total = $row['total'];
		$smcFunc['db_free_result']($dbresult);

		// Update the count
		$dbresult = $smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat SET total = $total WHERE ID_CAT = $ID_CAT LIMIT 1");

		// Return the total files
		return $total;

	}

}

function Downloads_DownloadFile()
{
	global $modSettings, $txt, $context, $smcFunc, $user_info;

	// Check Permission
	isAllowedTo('themes_view');
	isAllowedTo('themes_viewdownload');

	if (isset($_REQUEST['down']))
		$id = (int) $_REQUEST['down'];
	else
		$id = (int) $_REQUEST['id'];

	// Get the download information
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		f.filename, f.fileurl, f.orginalfilename, f.approved, f.credits, f.ID_CAT, f.id_member
	FROM {db_prefix}tema_file as f
	WHERE f.ID_FILE = $id");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);


	// Check if File is approved
	if ($row['approved'] == 0 && $user_info['id'] != $row['id_member'])
	{
		if (!allowedTo('themes_manage'))
			fatal_error($txt['tema_error_file_notapproved'],false);
	}

	require_once($sourcedir . '/Subs-Tema2.php');
	GetCatPermission($row['ID_CAT'],'viewdownload');

	// Check credits

	// End Credit check

	// Download File or Redirect to the download location
	if ($row['fileurl'] != '')
	{
		$lastdownload = time();
		// Update download count
		$dbresult = $smcFunc['db_query']('', "
		UPDATE {db_prefix}tema_file
			SET totaldownloads = totaldownloads + 1, lastdownload  = '$lastdownload'
		WHERE ID_FILE = $id LIMIT 1");

		// Redirect to the download
		header("Location: " . $row['fileurl']);

		exit;
	}
	else
	{
		$lastdownload = time();
		// Update download count
		$dbresult = $smcFunc['db_query']('', "
		UPDATE {db_prefix}tema_file
			SET totaldownloads = totaldownloads + 1, lastdownload  = '$lastdownload'
		WHERE ID_FILE = $id LIMIT 1");


		$real_filename = $row['orginalfilename'];
		$filename = $modSettings['tema_path'] . $row['filename'];

		// This is done to clear any output that was made before now. (would use ob_clean(), but that's PHP 4.2.0+...)
		ob_end_clean();
		if (!empty($modSettings['enableCompressedOutput']) && @version_compare(PHP_VERSION, '4.2.0') >= 0 && @filesize($filename) <= 4194304)
			@ob_start('ob_gzhandler');
		else
		{
			ob_start();
			header('Content-Encoding: none');
		}

		// No point in a nicer message, because this is supposed to be an attachment anyway...
		if (!file_exists($filename))
		{
			loadLanguage('Errors');

			header('HTTP/1.0 404 ' . $txt['attachment_not_found']);
			header('Content-Type: text/plain; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

			// We need to die like this *before* we send any anti-caching headers as below.
			die('404 - ' . $txt['attachment_not_found']);
		}





		// Check whether the ETag was sent back, and cache based on that...
		$file_md5 = '"' . md5_file($filename) . '"';


		// Send the attachment headers.
		header('Pragma: ');

		if (!$context['browser']['is_gecko'])
			header('Content-Transfer-Encoding: binary');

		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
		header('Accept-Ranges: bytes');
		header('Set-Cookie:');
		header('Connection: close');
		header('ETag: ' . $file_md5);

		if (filesize($filename) != 0)
		{
			$size = @getimagesize($filename);
			if (!empty($size))
			{
				// What headers are valid?
				$validTypes = array(
					1 => 'gif',
					2 => 'jpeg',
					3 => 'png',
					5 => 'psd',
					6 => 'bmp',
					7 => 'tiff',
					8 => 'tiff',
					9 => 'jpeg',
					14 => 'iff',
				);

				// Do we have a mime type we can simpy use?
				if (!empty($size['mime']))
					header('Content-Type: ' . $size['mime']);
				elseif (isset($validTypes[$size[2]]))
					header('Content-Type: image/' . $validTypes[$size[2]]);
				// Otherwise - let's think safety first... it might not be an image...
				elseif (isset($_REQUEST['image']))
					unset($_REQUEST['image']);
			}
			// Once again - safe!
			elseif (isset($_REQUEST['image']))
				unset($_REQUEST['image']);
		}

		if (!isset($_REQUEST['image']))
		{
			header('Content-Disposition: attachment; filename="' . $real_filename . '"');
			header('Content-Type: application/octet-stream');
		}

		if (empty($modSettings['enableCompressedOutput']) || filesize($filename) > 4194304)
			header('Content-Length: ' . filesize($filename));

		// Try to buy some time...
		@set_time_limit(0);

		// For text files.....
		if (!isset($_REQUEST['image']) && in_array(substr($real_filename, -4), array('.txt', '.css', '.htm', '.php', '.xml')))
		{
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false)
				$callback = create_function('$buffer', 'return preg_replace(\'~[\r]?\n~\', "\r\n", $buffer);');
			elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false)
				$callback = create_function('$buffer', 'return preg_replace(\'~[\r]?\n~\', "\r", $buffer);');
			else
				$callback = create_function('$buffer', 'return preg_replace(\'~\r~\', "\r\n", $buffer);');
		}

		// Since we don't do output compression for files this large...
		if (filesize($filename) > 4194304)
		{
			// Forcibly end any output buffering going on.
			if (function_exists('ob_get_level'))
			{
				while (@ob_get_level() > 0)
					@ob_end_clean();
			}
			else
			{
				@ob_end_clean();
				@ob_end_clean();
				@ob_end_clean();
			}

			$fp = fopen($filename, 'rb');
			while (!feof($fp))
			{
				if (isset($callback))
					echo $callback(fread($fp, 8192));
				else
					echo fread($fp, 8192);
				flush();
			}
			fclose($fp);
		}
		// On some of the less-bright hosts, readfile() is disabled.  It's just a faster, more byte safe, version of what's in the if.
		elseif (isset($callback) || @readfile($filename) == null)
			echo isset($callback) ? $callback(file_get_contents($filename)) : file_get_contents($filename);

		obExit(false);

		exit;
	}


}

function Downloads_ShowSubCats($cat,$g_manage)
{
	global $txt, $scripturl, $modSettings, $subcats_linktree, $smcFunc, $user_info, $context;


	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];


		// List all the catagories
		$dbresult = $smcFunc['db_query']('', "
		SELECT
			c.ID_CAT, c.title, p.view, c.roworder, c.description, c.image, c.filename
		FROM {db_prefix}tema_cat AS c
			LEFT JOIN {db_prefix}tema_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
		WHERE c.ID_PARENT = $cat ORDER BY c.roworder ASC");
		if ($smcFunc['db_affected_rows']() != 0)
		{
		  
          if ($context['downloads21beta'] == false)
          {

            			echo '<br /><table border="0" cellspacing="1" cellpadding="4" class="table_grid"  align="center" width="100%">
            <thead>	
            <tr class="catbg">
            					<th scope="col" class="smalltext first_th" colspan="2">' . $txt['tema_text_categoryname'] . '</th>
            					<th scope="col" class="smalltext" align="center">' . $txt['tema_text_totalfiles'] . '</th>
            					';
            			if ($g_manage)
            			echo '
            					<th scope="col" class="smalltext">' . $txt['tema_text_reorder'] . '</th>
            					<th scope="col" class="smalltext last_th">' . $txt['tema_text_options'] . '</th>';
            
            			echo '</tr>
            			</thead>';
            
            }
            else
            {
			echo '<br /><table border="0" cellspacing="1" cellpadding="4" class="table_grid"  align="center" width="100%">
            <thead>	
            <tr class="title_bar">
            					<th class="lefttext first_th" colspan="2">' . $txt['tema_text_categoryname'] . '</th>
            					<th  class="centertext" align="center">' . $txt['tema_text_totalfiles'] . '</th>
            					';
            			if ($g_manage)
            			echo '
            					<th class="lefttext">' . $txt['tema_text_reorder'] . '</th>
            					<th class="lefttext last_th">' . $txt['tema_text_options'] . '</th>';
            
            			echo '</tr>
            			</thead>'; 
                
            }
            


			while($row = $smcFunc['db_fetch_assoc']($dbresult))
			{
				// Check permission to show the downloads category
				if ($row['view'] == '0')
					continue;

				$totalfiles = Downloads_GetFileTotals($row['ID_CAT']);

				echo '<tr>';

					if ($row['image'] == '' && $row['filename'] == '')
						echo '<td class="windowbg" width="10%"></td><td  class="windowbg2"><b><a href="' . $scripturl . '?action=tema;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></b><br />' . parse_bbc($row['description']) . '</td>';
					else
					{
						if ($row['filename'] == '')
							echo '<td class="windowbg" width="10%"><a href="' . $scripturl . '?action=tema;cat=' . $row['ID_CAT'] . '"><img src="' . $row['image'] . '" /></a></td>';
						else
							echo '<td class="windowbg" width="10%"><a href="' . $scripturl . '?action=tema;cat=' . $row['ID_CAT'] . '"><img src="' . $modSettings['tema_url'] . 'catimgs/' . $row['filename'] . '" /></a></td>';

						echo '<td class="windowbg2"><b><a href="' . $scripturl . '?action=tema;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></b><br />' . parse_bbc($row['description']) . '</td>';
					}



				// Show total files in the category
				echo '<td align="center" valign="middle" class="windowbg">' . $totalfiles . '</td>';

				// Show Edit Delete and Order category
				if ( $g_manage)
				{
					echo '
					<td class="windowbg2"><a href="' . $scripturl . '?action=tema;sa=catup;cat=' . $row['ID_CAT'] . '">' . $txt['tema_text_up'] . '</a>&nbsp;<a href="' . $scripturl . '?action=tema;sa=catdown;cat=' . $row['ID_CAT'] . '">' . $txt['tema_text_down'] . '</a></td>
					<td class="windowbg"><a href="' . $scripturl . '?action=tema;sa=editcat;cat=' . $row['ID_CAT'] . '">' . $txt['tema_text_edit'] . '</a>&nbsp;<a href="' . $scripturl . '?action=tema;sa=deletecat;cat=' . $row['ID_CAT'] . '">' . $txt['tema_text_delete'] . '</a>
					<br /><br />
					<a href="' . $scripturl . '?action=tema;sa=catperm;cat=' . $row['ID_CAT'] . '">[' . $txt['tema_text_permissions'] . ']</a>
					</td>';

				}


				echo '</tr>';


                  if ($context['downloads21beta'] == false)
                  {
        				if ($subcats_linktree != '')
        					echo '
        					<tr class="windowbg3">
        						<td colspan="',($g_manage ? '6' : '4'), '">&nbsp;<span class="smalltext">',($subcats_linktree != '' ? '<b>' . $txt['tema_sub_cats'] . '</b>' . $subcats_linktree : ''),'</span></td>
        					</tr>';
                }
                else
                {
                    		if ($subcats_linktree != '')
        					echo '
        					<tr class="windowbg2">
        						<td colspan="',($g_manage ? '6' : '4'), '">&nbsp;<span class="smalltext">',($subcats_linktree != '' ? '<b>' . $txt['tema_sub_cats'] . '</b>' . $subcats_linktree : ''),'</span></td>
        					</tr>';
                    
                }

			}
			$smcFunc['db_free_result']($dbresult);
			echo '</table><br /><br />';
		}
}

function MainPageBlock($title, $type = 'recent')
{
	global $scripturl, $txt, $modSettings, $context, $user_info, $smcFunc;


	if (!$user_info['is_guest'])
		$groupsdata = implode($user_info['groups'],',');
	else
		$groupsdata = -1;


	$maxrowlevel = 4;
	echo '
    <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $title, '
        </h3>
</div>';

 if ($context['downloads21beta'] == false)
   echo '<table class="table_list">';
  else
    echo '<table class="table_grid">'; 
                
			//Check what type it is
			$query = ' ';
			$query_type = 'p.ID_FILE';
			switch($type)
			{
				case 'recent':
					$query_type = 'p.ID_FILE';
				break;

				case 'viewed':

					$query_type = 'p.views';
				break;

				break;
				case 'mostdownloaded':
					$query_type = 'p.totaldownloads';
				break;

				case 'toprated':
					$query_type = 'p.rating';
				break;
			}

				$query = "SELECT p.ID_FILE, p.totalratings, p.rating, p.filesize, p.views, p.title, p.id_member, m.real_name, p.date, p.description,
				p.totaldownloads, picture, pictureurl
					FROM {db_prefix}tema_file as p
					LEFT JOIN {db_prefix}members AS m  ON (m.id_member = p.id_member)
					LEFT JOIN {db_prefix}tema_catperm AS c ON (c.ID_GROUP IN ($groupsdata) AND c.ID_CAT = p.ID_CAT)
					WHERE p.approved = 1 AND (c.view IS NULL || c.view =1) GROUP by p.ID_FILE ORDER BY $query_type DESC LIMIT 8";

			// Execute the SQL query
			$dbresult = $smcFunc['db_query']('', $query);
			$rowlevel = 0;
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			if ($rowlevel == 0)
				echo '<tr class="windowbg2">';

			echo '<td align="center"><a href="' . $scripturl . '?action=tema;sa=view;down=' . $row['ID_FILE'] . '">',$row['title'],'</a><br />';
			echo '<div style="height:280px;overflow:hidden;">
				<a href="' . $scripturl . '?action=tema;sa=view;down=' . $row['ID_FILE'] . '"><img style="width:auto;max-width:100%;" src="',$row['picture'] == '' ? $row['pictureurl'] : $modSettings['tema_url'].'temaresim/'.$row['picture'],'" alt="">
				</a>
			</div>'; 

			echo '<span class="smalltext">';
			if (!empty($modSettings['tema_set_t_rating']))
				echo $txt['tema_form_rating'] . Downloads_GetStarsByPrecent(($row['totalratings'] != 0) ? ($row['rating'] / ($row['totalratings']* 5) * 100) : 0) . '<br />';
			if (!empty($modSettings['tema_set_t_downloads']))
				echo $txt['tema_text_downloads'] . $row['totaldownloads'] . '<br />';
			if (!empty($modSettings['tema_set_t_views']))
				echo $txt['tema_text_views'] . $row['views'] . '<br />';
			if (!empty($modSettings['tema_set_t_filesize']))
				echo $txt['tema_text_filesize'] . Downloads_format_size($row['filesize'], 2) . '<br />';
			if (!empty($modSettings['tema_set_t_date']))
				echo $txt['tema_text_date'] . timeformat($row['date']) . '<br />';
			if (!empty($modSettings['tema_set_t_username']))
			{
				if ($row['real_name'] != '')
					echo $txt['tema_text_by'] . ' <a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">'  . $row['real_name'] . '</a><br />';
				else
					echo $txt['tema_text_by'] . ' ' . $txt['tema_guest'] . '<br />';
			}
			echo '</span></td>';


			if ($rowlevel < ($maxrowlevel-1))
				$rowlevel++;
			else
			{
				echo '</tr>';
				$rowlevel = 0;
			}
		}
		if ($rowlevel !=0)
		{
			echo '</tr>';
		}

	echo '
	      </table><br />';

	$smcFunc['db_free_result']($dbresult);

}


function TopDownloadTabs()
{
	global $context, $txt, $scripturl, $user_info;

	$g_add = allowedTo('themes_add');

	// MyFiles
	if ($g_add && !($user_info['is_guest']))
		$context['downloads']['buttons']['myfiles'] =  array(
			'text' => 'tema_text_myfiles2',
			'url' =>$scripturl . '?action=tema;sa=myfiles;u=' . $user_info['id'],
			'lang' => true,

		);

	// Search
	$context['downloads']['buttons']['search'] =  array(
		'text' => 'tema_text_search2',
		'url' => $scripturl . '?action=tema;sa=search',
		'lang' => true,

	);

	// Setup Intial Link Tree
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=tema',
					'name' => $txt['tema_text_title']
				);
}

function Downloads_GetParentLink($ID_CAT)
{
	global $context, $scripturl, $smcFunc;
	if ($ID_CAT == 0)
		return;

			$dbresult1 = $smcFunc['db_query']('', "
		SELECT
			ID_PARENT,title
		FROM {db_prefix}tema_cat
		WHERE ID_CAT = $ID_CAT LIMIT 1");
		$row1 = $smcFunc['db_fetch_assoc']($dbresult1);

		$smcFunc['db_free_result']($dbresult1);

		Downloads_GetParentLink($row1['ID_PARENT']);

		$context['linktree'][] = array(
					'url' => $scripturl . '?action=tema;cat=' . $ID_CAT ,
					'name' => $row1['title']
				);
}

function Downloads_DoToolBarStrip($button_strip, $direction )
{
	global $settings, $txt;

	if (!empty($settings['use_tabs']))
	{
		template_button_strip($button_strip, $direction);
	}
	else
	{

			echo '<td>';

			foreach ($button_strip as $tab)
			{


				echo '
							<a href="', $tab['url'], '">', $txt[$tab['text']], '</a>';

				if (empty($tab['is_last']))
					echo ' | ';
			}


			echo '</td>';

	}

}

function Downloads_format_size($size, $round = 0)
{
    //Size must be bytes!
    $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    for ($i=0; $size > 1024 && $i < count($sizes) - 1; $i++) $size /= 1024;
    return round($size,$round).$sizes[$i];
}

function Downloads_ImportTinyPortalDownloads()
{
	global $txt, $smcFunc, $boarddir, $context, $modSettings, $sourcedir, $downloadSettings;
	isAllowedTo('themes_manage');

	// No limit on how long it takes
	ini_set('max_execution_time', 0);
	ini_set('display_errors', 1);

	$testGD = get_extension_funcs('gd');
	$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
	unset($testGD);


	require_once($sourcedir . '/Subs-Graphics.php');

	$catCount = 0;
	$fileCount = 0;


	$catArray = array();

	// Process Categories
	$catResult = $smcFunc['db_query']('',"
	SELECT
		id, name, description, parent
	FROM {db_prefix}tp_dlmanager
	WHERE type = 'dlcat' ORDER by parent ASC");
	while ($catRow = $smcFunc['db_fetch_assoc']($catResult))
	{


		$ID_PARENT = 0;
		// Get the new parent id
		if ($catRow['parent'] != 0)
			$ID_PARENT = $catArray[$catRow['parent']];
			
		if (empty($ID_PARENT))
			$ID_PARENT = 0;

		$title = $smcFunc['db_escape_string']($catRow['name']);
		$description = $smcFunc['db_escape_string']($catRow['description']);

		// Insert the category
		$smcFunc['db_query']('',"INSERT INTO {db_prefix}tema_cat
				(title, description, ID_PARENT)
			VALUES ('$title', '$description',$ID_PARENT)");


		// Get the Category ID
		$cat_id = $smcFunc['db_insert_id']('{db_prefix}tema_cat', 'id_cat');
		ReOrderCats($cat_id);

		$catArray[$catRow['id']] = $cat_id;

		$catCount++;

	}
	$smcFunc['db_free_result']($catResult);

	// Process Files
	$fileResult = $smcFunc['db_query']('',"
	SELECT
		id, name, description, category, downloads, views,
		created, last_access, filesize, authorid, screenshot, rating, voters,
		file
	FROM {db_prefix}tp_dlmanager
	WHERE type = 'dlitem'");
	while ($fileRow = $smcFunc['db_fetch_assoc']($fileResult))
	{
		$category = (int) $catArray[$fileRow['category']];
		$filesize = $fileRow['filesize'];
		$orginalfilename = $smcFunc['db_escape_string']($fileRow['file']);
		$filename =   $smcFunc['db_escape_string']($fileRow['file']);
		$description = $smcFunc['db_escape_string']($fileRow['description']);
		$title = $smcFunc['db_escape_string']($fileRow['name']);
		$authorid = $fileRow['authorid'];
		$filedate =  $fileRow['created'];
		$lastdownload = $fileRow['last_access'];
		$views = $fileRow['views'];
		$totaldownloads = $fileRow['downloads'];
//		$screenshot = $smcFunc['db_escape_string']($fileRow['screenshot']);

		$smcFunc['db_query']('',"INSERT INTO {db_prefix}tema_file
							(ID_CAT, filesize, filename, orginalfilename, title, description,ID_MEMBER,date,approved, views, totaldownloads, lastdownload)
						VALUES ($category, $filesize, '" . $filename . "', '$orginalfilename','$title', '$description',$authorid,$filedate,1, $views, $totaldownloads, $lastdownload )");

		$file_id = $smcFunc['db_insert_id']('{db_prefix}tema_file', 'id_file');

		// Copy the files to the main downloads folder
		copy($boarddir . '/tp-downloads/' . $filename , $modSettings['tema_path'] . $filename);
		@chmod($modSettings['tema_path'] .  $filename, 0644);

		// Do screenshots if any add them to file pictures
		/*
		if (!empty($screenshot))
		{

			$orginalScreenshot = $screenshot;
			$screenshot = str_replace('tp-images/Image/','',$orginalScreenshot);
			// Copy screenshot to downloads folder

			copy($boarddir . '/'.  $orginalScreenshot, $modSettings['tema_path'] . $screenshot);

			@chmod($modSettings['tema_path'] .  $screenshot, 0644);

			$picFileSize = filesize($modSettings['tema_path'] .  $screenshot);


			$sizes = getimagesize($modSettings['tema_path'] .  $screenshot);

				createThumbnail($modSettings['tema_path'] .  $screenshot, $downloadSettings['screenshot_thumb_width'], $downloadSettings['screenshot_thumb_height']);
				rename($modSettings['tema_path'] .   $screenshot . '_thumb',  $modSettings['tema_path'] .   'thumb_' . $screenshot);
				$thumbname = 'thumb_' . $screenshot;
				@chmod($modSettings['tema_path'] .   'thumb_' . $screenshot, 0755);

				// Medium Image
				$mediumimage = '';

				if ($downloadSettings['screenshot_make_medium'])
				{
					createThumbnail($modSettings['tema_path'] .  $screenshot, $downloadSettings['screenshot_medium_width'], $downloadSettings['screenshot_medium_height']);
					rename($modSettings['tema_path'] .  $screenshot . '_thumb',  $modSettings['tema_path'] .   'medium_' . $screenshot);
					$mediumimage = 'medium_' . $screenshot;
					@chmod($modSettings['tema_path'] . 'medium_' . $screenshot, 0755);

					// Check for Watermark
					if ($downloadSettings['screenshot_set_water_enabled'])
						DoWaterMark($modSettings['tema_path'] .   'medium_' .  $screenshot);

				}

				// Create the Database entry

				$tema_pic_id = 0;
						$smcFunc['db_query']('',"INSERT INTO {db_prefix}tema_file_pic
								(ID_FILE, filesize,thumbfilename,filename, height, width, ID_MEMBER, date, approved, mediumfilename)
							VALUES ($file_id, $picFileSize,'" .  $thumbname . "', '" .  $screenshot. "', $sizes[1], $sizes[0],$authorid,$filedate,1, '" . $mediumimage . "')");

				$tema_pic_id = db_insert_id();



			// If there is no Picture set make it the primary picture
				$smcFunc['db_query']('',"
				UPDATE {db_prefix}tema_file
				SET ID_PICTURE = $tema_pic_id
				WHERE ID_FILE = $file_id AND ID_PICTURE = 0");


		}
		*/


		// Do rating conversions
		$ratingsArray = explode(",",$fileRow['rating']);
		$votersArray = explode(",",$fileRow['voters']);

		foreach($ratingsArray as $key => $rating)
		{
			if (empty($votersArray[$key]))
				continue;
			if ($rating == '')
				continue;

			$smcFunc['db_query']('',"INSERT INTO {db_prefix}tema_rating (ID_MEMBER, ID_FILE, value) VALUES (" . $votersArray[$key] . ", $file_id,$rating)");

			// Add rating information to the download
			$smcFunc['db_query']('',"
			UPDATE {db_prefix}tema_file
				SET totalratings = totalratings + 1, rating = rating + $rating
			WHERE ID_FILE = $file_id LIMIT 1");
		}

		if ($fileRow['filesize'] != 0)
			UpdateUserFileSizeTable($fileRow['authorid'],$fileRow['filesize']);

		UpdateCategoryTotals($category);
		//UpdateMemberTotalFiles($fileRow['authorid']);

		$fileCount++;

	}
	$smcFunc['db_free_result']($fileResult);


	$context['tp_imported_files'] = $fileCount;
	$context['tp_imported_categories'] = $catCount;
	$context['sub_template'] = 'import_results';
	$context['page_title'] = $txt['tema_txt_importtp_results'];

}

function Downloads_ImportDownloads()
{
	global $txt, $context;

	isAllowedTo('themes_manage');



	$context['sub_template'] = 'import';

	$context['page_title'] = $txt['tema_txt_import_downloads'];

}


function ShowTopDownloadBar($title = '&nbsp;')
{
	global $txt, $context;
		echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $title, '
        </h3>
</div>
    
				<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
						<tr>
							<td style="padding-right: 1ex;" align="right" width="100%">

						', Downloads_DoToolBarStrip($context['downloads']['buttons'], 'top'), '
		
						</td>
						</tr>
					</table>

<br />';
}

function Downloads_ShowUserBox($memCommID, $online_color = '')
{
	global $memberContext, $settings, $modSettings, $txt, $context, $scripturl, $options, $downloadSettings;

	
	echo '
	<b>', $memberContext[$memCommID]['link'], '</b>
							<div class="smalltext">';

		// Show the member's custom title, if they have one.
		if (isset($memberContext[$memCommID]['title']) && $memberContext[$memCommID]['title'] != '')
			echo '
								', $memberContext[$memCommID]['title'], '<br />';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (isset($memberContext[$memCommID]['group']) && $memberContext[$memCommID]['group'] != '')
			echo '
								', $memberContext[$memCommID]['group'], '<br />';

		// Don't show these things for guests.
		if (!$memberContext[$memCommID]['is_guest'])
		{
			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $memberContext[$memCommID]['group'] == '') && $memberContext[$memCommID]['post_group'] != '')
				echo '
								', $memberContext[$memCommID]['post_group'], '<br />';
			echo '
								', $memberContext[$memCommID]['group_stars'], '<br />';

			// Is karma display enabled?  Total or +/-?
			if ($modSettings['karmaMode'] == '1')
				echo '
								<br />
								', $modSettings['karmaLabel'], ' ', $memberContext[$memCommID]['karma']['good'] - $memberContext[$memCommID]['karma']['bad'], '<br />';
			elseif ($modSettings['karmaMode'] == '2')
				echo '
								<br />
								', $modSettings['karmaLabel'], ' +', $memberContext[$memCommID]['karma']['good'], '/-', $memberContext[$memCommID]['karma']['bad'], '<br />';

			// Is this user allowed to modify this member's karma?
			if ($memberContext[$memCommID]['karma']['allow'])
				echo '
								<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $memberContext[$memCommID]['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaApplaudLabel'], '</a>
								<a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $memberContext[$memCommID]['id'],  ';sesc=', $context['session_id'], '">', $modSettings['karmaSmiteLabel'], '</a><br />';

			// Show online and offline buttons?
			if (!empty($modSettings['onlineEnable']) && !$memberContext[$memCommID]['is_guest'])
				echo '
								', $context['can_send_pm'] ? '<a href="' . $memberContext[$memCommID]['online']['href'] . '" title="' . $memberContext[$memCommID]['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $memberContext[$memCommID]['online']['image_href'] . '" alt="' . $memberContext[$memCommID]['online']['text'] . '" border="0" style="margin-top: 2px;" />' : $memberContext[$memCommID]['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $memberContext[$memCommID]['online']['text'] . '</span>' : '', '<br /><br />';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $memberContext[$memCommID]['gender']['image'] != '')
				echo '
								', $txt['tema_txt_gender'], ': ', $memberContext[$memCommID]['gender']['image'], '<br />';

			// Show how many posts they have made.
			echo '
								', $txt['tema_txt_posts'], ': ', $memberContext[$memCommID]['posts'], '<br />
								<br />';

			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($memberContext[$memCommID]['avatar']['image']))
				echo '
								<div style="overflow: hidden; width: 100%;">', $memberContext[$memCommID]['avatar']['image'], '</div><br />';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $memberContext[$memCommID]['blurb'] != '')
				echo '
								', $memberContext[$memCommID]['blurb'], '<br />
								<br />';

			// This shows the popular messaging icons.
			echo '
								', $memberContext[$memCommID]['icq']['link'], '
								', $memberContext[$memCommID]['msn']['link'], '
								', $memberContext[$memCommID]['aim']['link'], '
								', $memberContext[$memCommID]['yim']['link'], '<br />';

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
	
					echo '
								<a href="', $memberContext[$memCommID]['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.gif" alt="' . $txt['tema_txt_view_profile'] . '" title="' . $txt['tema_txt_view_profile'] . '" border="0" />' : $txt['tema_txt_view_profile']), '</a>';

				// Don't show an icon if they haven't specified a website.
				if ($memberContext[$memCommID]['website']['url'] != '')
					echo '
								<a href="', $memberContext[$memCommID]['website']['url'], '" title="' . $memberContext[$memCommID]['website']['title'] . '" target="_blank">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $txt['tema_txt_www'] . '" border="0" />' : $txt['tema_txt_www']), '</a>';

					
					
				// Don't show the email address if they want it hidden.
			if (in_array($memberContext[$memCommID]['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
					echo '
								<a href="', $scripturl, '?action=emailuser;sa=email;uid=', $memberContext[$memCommID]['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['tema_txt_profile_email'] . '" title="' . $txt['tema_txt_profile_email'] . '" />' : $txt['tema_txt_profile_email']), '</a></li>';
			
					
		
					

				// Since we know this person isn't a guest, you *can* message them.
				if ($context['can_send_pm'])
					echo '
								<a href="', $scripturl, '?action=pm;sa=send;u=', $memberContext[$memCommID]['id'], '" title="', $memberContext[$memCommID]['online']['label'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($memberContext[$memCommID]['online']['is_online'] ? 'on' : 'off') . '.gif" alt="' . $memberContext[$memCommID]['online']['label'] . '" border="0" />' : $memberContext[$memCommID]['online']['label'], '</a>';
			}
		}
		// Otherwise, show the guest's email.
		elseif (empty($memberContext[$memCommID]['hide_email']))
			echo '
								<br />
								<br />
								<a href="mailto:', $memberContext[$memCommID]['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['tema_txt_profile_email'] . '" title="' . $txt['tema_txt_profile_email'] . '" border="0" />' : $txt['tema_txt_profile_email']), '</a>';

		// Done with the information about the poster... on to the post itself.
		echo '
							</div>';
}

function Downloads_GetStarsByPrecent($percent)
{
	global $settings, $txt, $context;

    if ($context['downloads21beta'] == false)
    {
    	if ($percent == 0)
    		return $txt['tema_text_catnone'];
    	else if ($percent <= 20)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 1);
    	else if ($percent <= 40)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 2);
    	else if ($percent <= 60)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 3);
    	else if ($percent <= 80)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 4);
    	else if ($percent <= 100)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 5);
            
       } 
    else
    {
        if ($percent == 0)
    		return $txt['tema_text_catnone'];
    	else if ($percent <= 20)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 1);
    	else if ($percent <= 40)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 2);
    	else if ($percent <= 60)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 3);
    	else if ($percent <= 80)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 4);
    	else if ($percent <= 100)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 5);
    }

}

function resmikclt($max_width, $max_height, $source_file, $dst_dir, $quality = 80){
    $imgsize = getimagesize($source_file);
    $width = $imgsize[0];
    $height = $imgsize[1];
    $mime = $imgsize['mime'];
 
    switch($mime){
        case 'image/gif':
            $image_create = "imagecreatefromgif";
            $image = "imagegif";
            break;
 
        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image = "imagepng";
            $quality = 7;
            break;
 
        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            $quality = 80;
            break;
 
        default:
            return false;
            break;
    }
     
    $dst_img = imagecreatetruecolor($max_width, $max_height);
    $src_img = $image_create($source_file);
     
    $width_new = $height * $max_width / $max_height;
    $height_new = $width * $max_height / $max_width;
    //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
    if($width_new > $width){
        //cut point by height
        $h_point = (($height - $height_new) / 2);
        //copy image
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
    }else{
        //cut point by width
        $w_point = (($width - $width_new) / 2);
        imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
    }
     
    $image($dst_img, $dst_dir, $quality);
 
    if($dst_img)imagedestroy($dst_img);
    if($src_img)imagedestroy($src_img);
}


?>