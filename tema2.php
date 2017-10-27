<?php
/*
Theme System
Version 1
by:ceemoo
http://www.smf.konusal.com
*/
if (!defined('SMF'))
	die('Hacking attempt...');

function ThemesMain()
{
	global $boardurl, $modSettings, $boarddir, $currentVersion, $context;
	$currentVersion = '2.0';
	if (empty($modSettings['tema_url']))
		$modSettings['tema_url'] = $boardurl . '/tema/';
	if (empty($modSettings['tema_path']))
		$modSettings['tema_path'] = $boarddir . '/tema/';
	if (loadlanguage('tema') == false)
		loadLanguage('tema','english');
   loadtemplate('tema2.1');
   $context['downloads21beta'] = true;
   $context['html_headers'] .='<link rel="stylesheet" type="text/css" href="'. $modSettings['tema_url']. 'css/tema.css" />';
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
		'reportlist' => 'Themes_ReportList',
		'rate' => 'Downloads_RateDownload',
		'viewrating' => 'Downloads_ViewRating',
		'delrating' => 'Downloads_DeleteRating',
		'catup' => 'Downloads_CatUp',
		'catdown' => 'Downloads_CatDown',
		'catperm' => 'Downloads_CatPerm',
		'catperm2' => 'Downloads_CatPerm2',
		'catpermlist' => 'Themes_CatPermList',
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
		'approvelist' => 'Themes_ApproveList',
		'approve' => 'Downloads_ApproveDownload',
		'unapprove' => 'Downloads_UnApproveDownload',
		'add' => 'Downloads_AddDownload',
		'add2' => 'Downloads_AddDownload2',
		'search' => 'Downloads_Search',
		'search2' => 'Downloads_Search2',
		'stats' => 'Downloads_Stats',
		'filespace' => 'Themes_FileSpaceAdmin',
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

	);

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
	global $context, $scripturl, $mbname, $txt, $modSettings, $user_info, $smcFunc,$sourcedir;
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=tema',
					'name' => $txt['tema_text_title']
				);
	isAllowedTo('themes_view');
	$context['sub_template']  = 'mainview';
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
		require_once($sourcedir . '/Subs-tema2.php');
		GetCatPermission($cat,'view');
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

		GetParentLink($row1['ID_PARENT']);

		$context['linktree'][] = array(
					'url' => $scripturl . '?action=tema;cat=' . $cat,
					'name' => $context['downloads_cat_name']
				);

		$context['page_title'] = $mbname . ' - ' . $context['downloads_cat_name'];

		$total = GetTotalByCATID($cat);


		$context['start'] = (int) $_REQUEST['start'];

		$context['downloads_total'] = $total;


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
	require_once($sourcedir . '/Subs-tema2.php');
	AddCategory();

	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_addcategory'];
	$context['sub_template']  = 'add_category';
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	require_once($sourcedir . '/Subs-Editor.php');
	$editorOptions = array(
		'id' => 'description',
		'value' => '',
		'width' => '90%',
		'form' => 'catform',
		'labels' => array(
			'post_button' => '',
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];
}

function Downloads_AddCategory2()
{
	global $txt, $sourcedir, $smcFunc;
	isAllowedTo('themes_manage');
	$title = $smcFunc['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);
	$description = $smcFunc['htmlspecialchars']($_REQUEST['description'],ENT_QUOTES);
	$image = $smcFunc['htmlspecialchars']($_REQUEST['image'],ENT_QUOTES);
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

	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
	EditCategory($cat);
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_editcategory'];
	$context['sub_template']  = 'edit_category';
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	require_once($sourcedir . '/Subs-Editor.php');
	$editorOptions = array(
		'id' => 'description',
		'value' => $context['tema_catinfo']['description'],
		'width' => '90%',
		'form' => 'catform',
		'labels' => array(
			'post_button' => '',
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];
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
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
	DeleteCategory($catid);
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_delcategory'];
	$context['sub_template']  = 'delete_category';
}

function Downloads_DeleteCategory2()
{
	global $sourcedir;
	isAllowedTo('themes_manage');
	$catid = (int) $_REQUEST['catid'];
	require_once($sourcedir . '/Subs-tema2.php');
	DeleteCategory2($catid);
	Downloads_RecountFileQuotaTotals(false);
	redirectexit('action=tema;sa=admincat');
}

function Downloads_ViewDownload()
{
	global $context, $mbname, $modSettings, $txt,$scripturl, $sourcedir;
	isAllowedTo('themes_view');
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=tema',
					'name' => $txt['tema_text_title']
				);
	if (isset($_REQUEST['down']))
		$id = (int) $_REQUEST['down'];
	if (isset($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected'], false);
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
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
		require_once($sourcedir . '/Subs-tema2.php');
	EditDownload2($id,$title,$description,$keywords,$cat,$fileurl,$pictureurl,$demourl,$filesize,$approved);
}

function Downloads_DeleteDownload()
{
	global $context, $mbname, $txt, $sourcedir, $user_info;
	is_not_guest();
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
		require_once($sourcedir . '/Subs-tema2.php');
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
		require_once($sourcedir . '/Subs-tema2.php');
		DeleteDownload2($id);

}
function Downloads_ReportDownload()
{
	global $context, $mbname,$scripturl, $txt;
	isAllowedTo('themes_report');
	is_not_guest();
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
    $context['linktree'][] = array(
		'url' => $scripturl . '?action=tema',
		'name' => $txt['tema_text_title']
	);
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
	require_once($sourcedir . '/Subs-tema2.php');
	ReportDownload2($comment,$id,$commentdate,$memid);
	redirectexit('action=tema;sa=view;down=' . $id);
}

function Downloads_CatUp()
{
	global  $sourcedir;
	isAllowedTo('themes_manage');
	$cat = (int) $_REQUEST['cat'];
	require_once($sourcedir . '/Subs-tema2.php');
	ReOrderCats($cat);
	CatUp($cat);
}

function Downloads_CatDown()
{
	global  $sourcedir;
	isAllowedTo('themes_manage');
	$cat = (int) $_REQUEST['cat'];
	require_once($sourcedir . '/Subs-tema2.php');
	ReOrderCats($cat);
	CatDown($cat);
}

function Downloads_MyFiles()
{
	global $context, $mbname, $txt, $sourcedir,$scripturl,$modSettings;
	isAllowedTo('themes_view');
    $context['linktree'][] = array(
		'url' => $scripturl . '?action=tema',
		'name' => $txt['tema_text_title']
	);
	$u = (int) $_REQUEST['u'];
	if (empty($u))
		fatal_error($txt['tema_error_no_user_selected']);
	require_once($sourcedir . '/Subs-tema2.php');
	MyFiles($u);
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $context['downloads_userdownloads_name'];
	$context['sub_template']  = 'myfiles';
	$context['page_index'] = constructPageIndex($scripturl . '?action=tema;sa=myfiles;u=' . $context['downloads_userid'], $_REQUEST['start'], $context['downloads_total'], $modSettings['tema_set_files_per_page']);
}

function Themes_ApproveList()
{
	global $context, $mbname, $txt, $scripturl, $sourcedir;
	isAllowedTo('themes_manage');
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_form_approvedownloads'];
	$context['sub_template']  = 'approvelist';
	$al = (int) $_REQUEST['start'];
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
	UnApproveFileByID($id);
	redirectexit('action=admin;area=tema;sa=approvelist');
}

function Themes_ReportList()
{
	global $context, $mbname, $txt, $sourcedir;
	isAllowedTo('themes_manage');
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_form_reportdownloads'];
	$context['sub_template']  = 'reportlist';
	require_once($sourcedir . '/Subs-tema2.php');
	ReportList();
}

function Downloads_DeleteReport()
{
	global $txt, $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_report_selected']);
	require_once($sourcedir . '/Subs-tema2.php');
	DeleteReport($id);
	redirectexit('action=admin;area=tema;sa=reportlist');
}

function Downloads_Search()
{
	global $context, $mbname, $txt, $user_info, $scripturl, $sourcedir;
    $context['linktree'][] = array(
		'url' => $scripturl . '?action=tema',
		'name' => $txt['tema_text_title']
	);
	isAllowedTo('themes_view');
	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];
	require_once($sourcedir . '/Subs-tema2.php');
	Search($groupid);
	$context['sub_template']  = 'search';
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_search'];
}

function Downloads_Search2()
{
	global $context, $mbname, $txt, $scripturl,$smcFunc;

	// Is the user allowed to view the downloads?
	isAllowedTo('themes_view');

    $context['linktree'][] = array(
		'url' => $scripturl . '?action=tema',
		'name' => $txt['tema_text_title']
	);
	
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
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
	DeleteRating($id);
}

function Downloads_Stats()
{
	global $context, $mbname,$txt,$scripturl, $sourcedir, $user_info;
	isAllowedTo('themes_view');
    $context['linktree'][] = array(
		'url' => $scripturl . '?action=tema',
		'name' => $txt['tema_text_title']
	);
	$context['sub_template']  = 'stats';
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_stats'];
	require_once($sourcedir . '/Subs-tema2.php');
	Stats();
}


function Themes_FileSpaceAdmin()
{
	global $mbname, $txt, $context, $scripturl, $sourcedir;
	isAllowedTo('themes_manage');
	loadLanguage('Admin');
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_filespace'];
	$context['sub_template']  = 'filespace';
	$al = (int) $_REQUEST['start'];
	require_once($sourcedir . '/Subs-tema2.php');
	FileSpaceAdmin($al);
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=tema;sa=filespace', $_REQUEST['start'],$context['downloads_total'], 20);
}

function Downloads_FileSpaceList()
{
	global $mbname, $txt, $context, $scripturl, $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_user_selected']);
	$al = (int) $_REQUEST['start'];
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
	AddQuota($groupid,$filelimit);
	redirectexit('action=admin;area=tema;sa=filespace');
}

function Downloads_DeleteQuota()
{
	global $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
	CatPerm2($groupname,$cat,$view,$viewdownload,$add,$edit,$delete);
	redirectexit('action=tema;sa=catperm;cat=' . $cat);
}

function Themes_CatPermList()
{
	global $mbname, $txt, $context, $sourcedir;
	isAllowedTo('themes_manage');
	$context['sub_template']  = 'catpermlist';
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_catpermlist'];
	require_once($sourcedir . '/Subs-tema2.php');
	CatPermList();
}

function Downloads_CatPermDelete()
{
	global $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	require_once($sourcedir . '/Subs-tema2.php');
	CatPermDelete($id);
	redirectexit('action=admin;area=tema;sa=catpermlist');
}

function Downloads_PreviousDownload()
{
	global $txt, $sourcedir;
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
	require_once($sourcedir . '/Subs-tema2.php');
	PreviousDownload($id);
}

function Downloads_NextDownload()
{
	global $txt, $sourcedir;
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
	require_once($sourcedir . '/Subs-tema2.php');
	NextDownload($id);
}

function Downloads_CatImageDelete()
{
	global $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		exit;
	require_once($sourcedir . '/Subs-tema2.php');
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
	require_once($sourcedir . '/Subs-tema2.php');
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
		require_once($sourcedir . '/Subs-tema2.php');
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
	global $txt, $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	require_once($sourcedir . '/Subs-tema2.php');
	CustomUp($id);
}

function Downloads_CustomDown()
{
	global $txt, $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	require_once($sourcedir . '/Subs-tema2.php');
	CustomDown($id);
}

function Downloads_CustomAdd()
{
	global $txt, $smcFunc,$sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	$title = $smcFunc['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);
	$defaultvalue = $smcFunc['htmlspecialchars']($_REQUEST['defaultvalue'],ENT_QUOTES);
	$required = isset($_REQUEST['required']) ? 1 : 0;
	if ($title == '')
		fatal_error($txt['tema_custom_err_title'], false);
	require_once($sourcedir . '/Subs-tema2.php');
	CustomAdd($id,$title,$defaultvalue,$required);
	redirectexit('action=tema;sa=editcat;cat=' . $id);

}

function Downloads_CustomDelete()
{
	global $sourcedir;
	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];
	require_once($sourcedir . '/Subs-tema2.php');
	CustomDelete($id);
}


function Downloads_GetFileTotals($ID_CAT)
{
	global $sourcedir;
	require_once($sourcedir . '/Subs-tema2.php');
	GetFileTotals($ID_CAT);
	return GetFileTotals($ID_CAT);
}

function Downloads_DownloadFile()
{
	global $sourcedir;
	isAllowedTo('themes_view');
	isAllowedTo('themes_viewdownload');
	if (isset($_REQUEST['down']))
		$id = (int) $_REQUEST['down'];
	else
		$id = (int) $_REQUEST['id'];
	require_once($sourcedir . '/Subs-tema2.php');
	DownloadFile($id);
}

function Downloads_ShowSubCats($cat,$g_manage)
{
	global $txt, $scripturl, $modSettings, $subcats_linktree, $smcFunc, $user_info, $context;
	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];
		$dbresult = $smcFunc['db_query']('', "
		SELECT
			c.ID_CAT, c.title, p.view, c.roworder, c.description, c.image, c.filename
		FROM {db_prefix}tema_cat AS c
			LEFT JOIN {db_prefix}tema_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
		WHERE c.ID_PARENT = $cat ORDER BY c.roworder ASC");
		if ($smcFunc['db_affected_rows']() != 0)
		{
		  $context['subthemecat']=array();
			while($row = $smcFunc['db_fetch_assoc']($dbresult))
			{
				if ($row['view'] == '0')
					continue;
				$totalfiles = Downloads_GetFileTotals($row['ID_CAT']);
				$context['subthemecat'][]=array(
				'image' => $row['image'],
				'filename' => $row['filename'],
				'ID_CAT' => $row['ID_CAT'],
				'description' => $row['description'],
				'title' => $row['title'],
				'totalfiles' => $totalfiles,
				'subcats_linktree' => $subcats_linktree,
				 );
			}
			$smcFunc['db_free_result']($dbresult);
		}
		return;
}

function MainPageBlock($title, $type = 'recent')
{
	global $scripturl, $txt, $modSettings, $context, $user_info, $smcFunc;


	if (!$user_info['is_guest'])
		$groupsdata = implode($user_info['groups'],',');
	else
		$groupsdata = -1;


	$maxrowlevel = 4;
	echo '<br class="clear"/>
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
	      </table><br class="clear"/>';

	$smcFunc['db_free_result']($dbresult);

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
