<?php

if (!defined('SMF'))
	die('Hacking attempt...');

function AddCategory()
{
	global $context,  $smcFunc;

	$context['downloads_boards'] = array('');
	$request = $smcFunc['db_query']('', "
	SELECT
		b.ID_BOARD, b.name AS bName, c.name AS cName
	FROM {db_prefix}boards AS b, {db_prefix}categories AS c
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['downloads_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	$smcFunc['db_free_result']($request);

	 $dbresult = $smcFunc['db_query']('', "
	 SELECT
	 	c.ID_CAT, c.title,c.roworder
	 FROM {db_prefix}tema_cat AS c
	 ORDER BY c.roworder ASC");
	$context['downloads_cat'] = array();
	 while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_cat'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'roworder' => $row['roworder'],
			);
		}
	$smcFunc['db_free_result']($dbresult);

	if (isset($_REQUEST['cat']))
		$parent  = (int) $_REQUEST['cat'];
	else
		$parent = 0;

	$context['cat_parent'] = $parent;
}

function AddCategory2($title,$description,$image,$boardselect,$parent,$locktopic,$disablerating,$sortby,$orderby)
{
	global $txt, $sourcedir, $modSettings, $smcFunc;
	
	// Do the order
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		MAX(roworder) as cat_order
	FROM {db_prefix}tema_cat
	WHERE ID_PARENT = $parent
	ORDER BY roworder DESC");
	$row = $smcFunc['db_fetch_assoc']($dbresult);

	if ($smcFunc['db_affected_rows']() == 0)
		$order = 0;
	else
		$order = $row['cat_order'];
	$order++;

	// Insert the category
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}tema_cat
			(title, description,roworder,image,ID_BOARD,ID_PARENT,disablerating,locktopic,sortby,orderby)
		VALUES ('$title', '$description',$order,'$image',$boardselect,$parent,$disablerating,$locktopic,'$sortby','$orderby')");
	$smcFunc['db_free_result']($dbresult);

	// Get the Category ID
	$cat_id = $smcFunc['db_insert_id']('{db_prefix}tema_cat', 'id_cat');


	$testGD = get_extension_funcs('gd');
	$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
	unset($testGD);

	// Upload Category image File
	if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
	{

		$sizes = @getimagesize($_FILES['picture']['tmp_name']);

			// No size, then it's probably not a valid pic.
			if ($sizes === false)
				fatal_error($txt['tema_error_invalid_picture'],false);

			require_once($sourcedir . '/Subs-Graphics.php');

			if ((!empty($modSettings['tema_set_cat_width']) && $sizes[0] > $modSettings['tema_set_cat_width']) || (!empty($modSettings['tema_set_cat_height']) && $sizes[1] > $modSettings['tema_set_cat_height']))
			{

					// Delete the temp file
					@unlink($_FILES['picture']['tmp_name']);
					fatal_error($txt['tema_error_img_size_height'] . $sizes[1] . $txt['tema_error_img_size_width'] . $sizes[0],false);

			}

		// Move the file
		$extensions = array(
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
		$extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : '.bmp';


		$filename = $cat_id . '.' . $extension;

		move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['tema_path'] . 'catimgs/' . $filename);
		@chmod($modSettings['tema_path'] . 'catimgs/' . $filename, 0644);

		// Update the filename for the category
		$smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat
		SET filename = '$filename' WHERE ID_CAT = $cat_id LIMIT 1");


	}

}

function EditCategory($cat)
{
	global $context, $smcFunc;
	$context['downloads_boards'] = array('');
	$request = $smcFunc['db_query']('', "
	SELECT
		b.ID_BOARD, b.name AS bName, c.name AS cName
	FROM {db_prefix}boards AS b, {db_prefix}categories AS c
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['downloads_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	$smcFunc['db_free_result']($request);

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_CAT, title,roworder
	FROM {db_prefix}tema_cat
	ORDER BY roworder ASC");
	$context['downloads_cat'] = array();
	 while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_cat'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'roworder' => $row['roworder'],
			);
		}
	$smcFunc['db_free_result']($dbresult);

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_CAT, title, image, filename, description,ID_BOARD,
		ID_PARENT,disablerating, redirect, showpostlink, locktopic, sortby, orderby
	FROM {db_prefix}tema_cat
	WHERE ID_CAT = $cat LIMIT 1");

	$row = $smcFunc['db_fetch_assoc']($dbresult);
			$context['tema_catinfo'] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'image' => $row['image'],
			'filename' => $row['filename'],
			'description' => $row['description'],
			'ID_BOARD' => $row['ID_BOARD'],
			'ID_PARENT' => $row['ID_PARENT'],
			'disablerating' => $row['disablerating'],
			'redirect' => $row['redirect'],
			'showpostlink' => $row['showpostlink'],
			'locktopic' => $row['locktopic'],
			'sortby' => $row['sortby'],
			'orderby' => $row['orderby'],
			);
	$smcFunc['db_free_result']($dbresult);


	// Get all the custom fields
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		title, defaultvalue, is_required, ID_CUSTOM
	FROM  {db_prefix}tema_custom_field
	WHERE ID_CAT = " . $context['tema_catinfo']['ID_CAT'] . "
	ORDER BY roworder desc");
	$context['tema_custom'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
			$context['tema_custom'][] = array(
			'title' => $row['title'],
			'ID_CUSTOM' => $row['ID_CUSTOM'],
			'defaultvalue' => $row['defaultvalue'],
			'is_required' => $row['is_required'],

			);
	}
	$smcFunc['db_free_result']($dbresult);


	$context['catid'] = $cat;

}

function EditCategory2($title,$description,$catid,$image,$boardselect,$parent,$locktopic,$disablerating,$sortby,$orderby)
{
	global $txt, $modSettings, $sourcedir, $smcFunc;

	$smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat
		SET title = '$title', image = '$image', description = '$description', ID_BOARD = $boardselect,
		ID_PARENT = $parent, disablerating = $disablerating, locktopic = $locktopic,
		orderby = '$orderby', sortby = '$sortby'
		WHERE ID_CAT = $catid LIMIT 1");


	$testGD = get_extension_funcs('gd');
	$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
	unset($testGD);

	// Upload Category image File
	if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
	{
		$sizes = @getimagesize($_FILES['picture']['tmp_name']);

			// No size, then it's probably not a valid pic.
			if ($sizes === false)
				fatal_error($txt['tema_error_invalid_picture'],false);

			require_once($sourcedir . '/Subs-Graphics.php');

			if ((!empty($modSettings['tema_set_cat_width']) && $sizes[0] > $modSettings['tema_set_cat_width']) || (!empty($modSettings['tema_set_cat_height']) && $sizes[1] > $modSettings['tema_set_cat_height']))
			{

				// Delete the temp file
				@unlink($_FILES['picture']['tmp_name']);
				fatal_error($txt['tema_error_img_size_height'] . $sizes[1] . $txt['tema_error_img_size_width'] . $sizes[0],false);

			}
		// Move the file
		$extensions = array(
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
		$extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : '.bmp';


		$filename = $catid . '.' . $extension;

		move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['tema_path'] . 'catimgs/' . $filename);
		@chmod($modSettings['tema_path'] . 'catimgs/' . $filename, 0644);

		$smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat
		SET filename = '$filename' WHERE ID_CAT = $catid LIMIT 1");


	}
}
function DeleteCategory($catid)
{
	global $context, $mbname, $txt, $smcFunc;
	$context['catid'] = $catid;
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_CAT, title
	FROM {db_prefix}tema_cat
	WHERE ID_CAT = $catid");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$context['cat_title'] = $row['title'];
	$smcFunc['db_free_result']($dbresult);
	$dbresult2 = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) as totalfiles
	FROM {db_prefix}tema_file
	WHERE ID_CAT = $catid AND approved = 1");
	$row2 = $smcFunc['db_fetch_assoc']($dbresult2);
	$context['totalfiles'] = $row2['totalfiles'];
	$smcFunc['db_free_result']($dbresult2);

}

function DeleteCategory2($catid)
{
	global $modSettings, $smcFunc;
	@ini_set('max_execution_time', '300');
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_FILE, filename
	FROM {db_prefix}tema_file
	WHERE ID_CAT = $catid");
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		@unlink($modSettings['tema_path'] . $row['filename']);
		$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_rating WHERE ID_FILE  = " . $row['ID_FILE']);
		$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_report WHERE ID_FILE  = " . $row['ID_FILE']);
		$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_creport WHERE ID_FILE  = " . $row['ID_FILE']);
	}
	$smcFunc['db_free_result']($dbresult);
	$smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat SET ID_PARENT = 0 WHERE ID_PARENT = $catid");
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_file WHERE ID_CAT = $catid");
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_cat WHERE ID_CAT = $catid LIMIT 1");
}

function ViewDownload($id)
{
	global $context,  $modSettings, $user_info, $scripturl, $txt, $smcFunc;
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_FILE, ID_CAT
	FROM {db_prefix}tema_file
	WHERE ID_FILE = $id  LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		p.ID_FILE, p.totalratings, p.rating, p.ID_CAT, p.keywords, p.demourl,
		p.filesize, p.filename, p.orginalfilename, p.fileurl,
	 	p.approved, p.views, p.title, p.id_member, m.real_name, p.date, p.description,
	   	c.title CAT_TITLE, c.ID_PARENT, c.disablerating, p.credits, p.totaldownloads,  p.lastdownload, p.picture, p.pictureurl
	FROM ({db_prefix}tema_file as p,  {db_prefix}tema_cat AS c)
		LEFT JOIN {db_prefix}members AS m ON  (p.id_member = m.id_member)
	WHERE p.ID_FILE = $id AND p.ID_CAT = c.ID_CAT LIMIT 1");
    if ($smcFunc['db_affected_rows']()== 0)
    	fatal_error($txt['tema_error_no_downloadexist'],false);
    $row = $smcFunc['db_fetch_assoc']($dbresult);
    GetCatPermission($row['ID_CAT'],'view');
	if ($row['approved'] == 0 && $user_info['id'] != $row['id_member'])
	{
		if (!allowedTo('themes_manage'))
			fatal_error($txt['tema_error_file_notapproved'],false);
	}
	$context['downloads_file'] = array(
		'ID_FILE' => $row['ID_FILE'],
		'id_member' => $row['id_member'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => $row['filesize'],
		'filename' => $row['filename'],
		'ID_CAT' => $row['ID_CAT'],
		'date' => timeformat($row['date']),
		'keywords' => $row['keywords'],
		'real_name' => $row['real_name'],
		'totalratings' => $row['totalratings'],
		'rating' => $row['rating'],
		'CAT_TITLE' => $row['CAT_TITLE'],
		'disablerating' => @$row['disablerating'],
		'credits' => $row['credits'],
		'orginalfilename' => $row['orginalfilename'],
		'totaldownloads' => $row['totaldownloads'],
		'lastdownload' => $row['lastdownload'],
		'fileurl' => $row['fileurl'],
		'picture' => $row['picture'],
		'pictureurl' => $row['pictureurl'],
		'demourl' => $row['demourl'],
	);
	$smcFunc['db_free_result']($dbresult);
	GetParentLink($row['ID_PARENT']);
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=tema;cat=' . $row['ID_CAT'],
					'name' => $row['CAT_TITLE']
				);
	$result = $smcFunc['db_query']('', "
	SELECT
		f.title, d.value
	FROM  ({db_prefix}tema_custom_field as f,{db_prefix}tema_custom_field_data as d)
	WHERE d.ID_CUSTOM = f.ID_CUSTOM AND d.ID_FILE = " . $context['downloads_file']['ID_FILE'] .  "
	ORDER BY f.roworder desc");
	$context['downloads_custom'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['downloads_custom'][] = array(
			'value' => $row['value'],
			'title' => $row['title'],
		);
	}
	$smcFunc['db_free_result']($result);
	$dbresult = $smcFunc['db_query']('', "UPDATE {db_prefix}tema_file
		SET views = views + 1 WHERE ID_FILE = $id LIMIT 1");
	if (!empty($modSettings['tema_who_viewing']))
	{
		$context['can_moderate_forum'] = allowedTo('moderate_forum');

				$context['view_members'] = array();
				$context['view_members_list'] = array();
				$context['view_num_hidden'] = 0;
				$whoID = (string) $id;

				$request = $smcFunc['db_query']('', "
					SELECT
						lo.id_member, lo.log_time, mem.real_name, mem.member_name, mem.show_online,
						mg.online_color, mg.ID_GROUP, mg.group_name
					FROM {db_prefix}log_online AS lo
						LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lo.id_member)
						LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
					WHERE INSTR(lo.url, 's:9:\"downloads\";s:2:\"sa\";s:4:\"view\";s:2:\"id\";s:" . strlen($whoID ) .":\"$id\";') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'");
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

				$context['view_num_guests'] = $smcFunc['db_num_rows']($request) - count($context['view_members']);
				$smcFunc['db_free_result']($request);

				krsort($context['view_members']);
				krsort($context['view_members_list']);


	}
}

function AddDownload($cat)
{
	global $context, $txt,  $user_info, $smcFunc;
	$context['tema_cat'] = $cat;
	GetCatPermission($cat,'addfile');
	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];
		$dbresult = $smcFunc['db_query']('', "
		SELECT
			c.ID_CAT, c.title, p.view, p.addfile
		FROM {db_prefix}tema_cat AS c
			LEFT JOIN {db_prefix}tema_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
		WHERE c.redirect = 0 ORDER BY c.roworder ASC");
		if ($smcFunc['db_num_rows']($dbresult) == 0)
		 	fatal_error($txt['tema_error_no_catexists'] , false);

		$context['downloads_cat'] = array();
		 while($row = $smcFunc['db_fetch_assoc']($dbresult))
			{
				if ($row['view'] == '0' || $row['addfile'] == '0' )
					continue;

				$context['downloads_cat'][] = array(
					'ID_CAT' => $row['ID_CAT'],
					'title' => $row['title'],
				);
			}
		$smcFunc['db_free_result']($dbresult);

	$result = $smcFunc['db_query']('', "
	SELECT
		title, defaultvalue, is_required, ID_CUSTOM
	FROM  {db_prefix}tema_custom_field
	WHERE ID_CAT = " . $cat);
	$context['downloads_custom'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
			$context['downloads_custom'][] = array(
					'ID_CUSTOM' => $row['ID_CUSTOM'],
					'title' => $row['title'],
					'defaultvalue' => $row['defaultvalue'],
					'is_required' => $row['is_required'],
				);
	}
	$smcFunc['db_free_result']($result);
	$context['quotalimit'] = GetQuotaGroupLimit($user_info['id']);
	$context['userspace'] = GetUserSpaceUsed($user_info['id']);
}

function AddDownload2($title,$description,$keywords,$cat,$fileurl,$demourl,$pictureurl,$filesize,$approved )
{
	global $txt, $scripturl, $modSettings, $sourcedir, $gd2, $user_info, $smcFunc;
	GetCatPermission($cat,'addfile');
	if (!empty($modSettings['tema_set_enable_multifolder']))
		CreateDownloadFolder();
		$result = $smcFunc['db_query']('', "
		SELECT
			f.title, f.is_required, f.ID_CUSTOM
		FROM  {db_prefix}tema_custom_field as f
		WHERE f.is_required = 1 AND f.ID_CAT = " . $cat);
		while ($row2 = $smcFunc['db_fetch_assoc']($result))
		{
	 		if (!isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
	 		{
	 			fatal_error($txt['tema_err_req_custom_field'] . $row2['title'], false);
	 		}
	 		else
	 		{
	 			if ($_REQUEST['cus_' . $row2['ID_CUSTOM']] == '')
	 				fatal_error($txt['tema_err_req_custom_field'] . $row2['title'], false);
	 		}
	 	}
		$smcFunc['db_free_result']($result);
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_BOARD,locktopic
	FROM {db_prefix}tema_cat
	WHERE ID_CAT = $cat");
	$rowcat = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if (isset($_FILES['download']['name']) && $_FILES['download']['name'] != '')
	{
		$orginalfilename =  $smcFunc['db_escape_string']($_FILES['download']['name']);
		$filesize = $_FILES['download']['size'];

		if (!empty($modSettings['tema_max_filesize']) && $filesize > $modSettings['tema_max_filesize'])
		{
			@unlink($_FILES['download']['tmp_name']);
			fatal_error($txt['tema_error_file_filesize'] . format_size($modSettings['tema_max_filesize'] , 2),false);
		}

		$quotalimit = GetQuotaGroupLimit($user_info['id']);
		$userspace = GetUserSpaceUsed($user_info['id']);
		if ($quotalimit != 0  &&  ($userspace + $filesize) >  $quotalimit)
		{
			@unlink($_FILES['download']['tmp_name']);
			fatal_error($txt['tema_error_space_limit'] . format_size($userspace, 2) . ' / ' . format_size($quotalimit, 2),false);
		}
		$filename = $user_info['id'] . '_' . date('d_m_y_g_i_s'); //. '.' . $extension;
		$extrafolder = '';
		if (!empty($modSettings['tema_set_enable_multifolder']))
			$extrafolder = $modSettings['tema_folder_id'] . '/';
		move_uploaded_file($_FILES['download']['tmp_name'], $modSettings['tema_path'] . $extrafolder .  $filename);
		@chmod($modSettings['tema_path'] . $extrafolder .  $filename, 0644);
		$t = time();
		$file_id = 0;
		$smcFunc['db_query']('', "INSERT INTO {db_prefix}tema_file
							(ID_CAT, filesize,filename, orginalfilename, keywords, title, description,id_member,date,approved,pictureurl,demourl)
						VALUES ($cat, $filesize, '" . $extrafolder . $filename . "', '$orginalfilename',   '$keywords','$title', '$description'," . $user_info['id']  . ",$t,$approved,'$pictureurl','$demourl')");

		$file_id = $smcFunc['db_insert_id']('{db_prefix}tema_file', 'id_file');
		if (!empty($modSettings['tema_set_enable_multifolder']))
				ComputeNextFolderID($file_id);
	}
	else
	{
		if (empty($fileurl))
			fatal_error($txt['tema_error_no_download']);
		else
		{
			$t = time();
			$file_id = 0;
			$smcFunc['db_query']('', "INSERT INTO {db_prefix}tema_file
								(id_cat, fileurl, keywords, title, description,id_member,date,approved,pictureurl,demourl)
							VALUES ($cat, '$fileurl', '$keywords', '$title', '$description'," . $user_info['id'] . ",$t,$approved,'$pictureurl','$demourl')");
			$file_id = $smcFunc['db_insert_id']('{db_prefix}tema_file', 'id_file');;

		}

	}
	if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
	{

		$sizes = @getimagesize($_FILES['picture']['tmp_name']);
			if ($sizes === false)
				fatal_error($txt['tema_error_invalid_picture'],false);

			require_once($sourcedir . '/Subs-Graphics.php');
			if ((!empty($modSettings['tema_set_file_image_width']) && $sizes[0] > $modSettings['tema_set_file_image_width']) || (!empty($modSettings['tema_set_file_image_height']) && $sizes[1] > $modSettings['tema_set_file_image_height']))
			{
				$widthresim = $modSettings['tema_set_file_image_width'];
				$heightresim = $modSettings['tema_set_file_image_height'];
				resmikclt($widthresim,$heightresim,$_FILES['picture']['tmp_name'],$_FILES['picture']['tmp_name']);
			}
		$extensions = array(
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
		$extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : '.bmp';
		$filename = $file_id . '.' . $extension;
		move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['tema_path'] . 'temaresim/' . $filename);
		@chmod($modSettings['tema_path'] . 'temaresim/' . $filename, 0644);
		$smcFunc['db_query']('', "UPDATE {db_prefix}tema_file
		SET picture = '$filename' WHERE ID_FILE = $file_id LIMIT 1");
	}
					$result = $smcFunc['db_query']('', "
					SELECT
						f.title, f.is_required, f.ID_CUSTOM
					FROM  {db_prefix}tema_custom_field as f
					WHERE f.ID_CAT = " . $cat);
					while ($row2 = $smcFunc['db_fetch_assoc']($result))
					{
						if (isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
						{

							$custom_data = $smcFunc['htmlspecialchars']($_REQUEST['cus_' . $row2['ID_CUSTOM']],ENT_QUOTES);

							$smcFunc['db_query']('', "INSERT INTO {db_prefix}tema_custom_field_data
							(ID_FILE, ID_CUSTOM, value)
							VALUES('$file_id', " . $row2['ID_CUSTOM'] . ", '$custom_data')");
						}
					}
					$smcFunc['db_free_result']($result);

				if ($filesize != 0)
					UpdateUserFileSizeTable($user_info['id'],$filesize);

				if ($rowcat['ID_BOARD'] != 0 && $approved == 1)
				{
					require_once($sourcedir . '/Subs-Post.php');

					$showpostlink = '[url]' . $scripturl . '?action=tema;sa=view;down=' . $file_id . '[/url]';
					$msgOptions = array(
						'id' => 0,
						'subject' => $title,
						'body' => '[b]' . $title . "[/b]\n\n$showpostlink",
						'icon' => 'xx',
						'smileys_enabled' => 1,
						'attachments' => array(),
					);
					$topicOptions = array(
						'id' => 0,
						'board' => $rowcat['ID_BOARD'],
						'poll' => null,
						'lock_mode' => $rowcat['locktopic'],
						'sticky_mode' => null,
						'mark_as_read' => true,
					);
					$posterOptions = array(
						'id' => $user_info['id'],
						'update_post_count' => !$user_info['is_guest'] && !isset($_REQUEST['msg']),
					);
					preparsecode($msgOptions['body']);
					createPost($msgOptions, $topicOptions, $posterOptions);
					$ID_TOPIC = $topicOptions['id'];
					$smcFunc['db_query']('', "UPDATE {db_prefix}tema_file SET ID_TOPIC = $ID_TOPIC WHERE ID_FILE = $file_id LIMIT 1");
				}
				UpdateCategoryTotals($cat);
}

function EditDownload($id,$groupid)
{
	global $context, $txt, $user_info, $smcFunc;
    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	p.ID_FILE, p.ID_CAT, p.keywords, p.filesize,
    	p.filename, p.approved, p.views, p.title, p.id_member,
      	m.real_name, p.date, p.description, p.fileurl,p.orginalfilename, p.picture, p.pictureurl, p.demourl
    FROM {db_prefix}tema_file as p
       LEFT JOIN {db_prefix}members AS m ON (m.id_member = p.id_member)
     WHERE p.ID_FILE = $id  LIMIT 1");
	if ($smcFunc['db_affected_rows']()== 0)
    	fatal_error($txt['tema_error_no_downloadexist'],false);
    $row = $smcFunc['db_fetch_assoc']($dbresult);
	GetCatPermission($row['ID_CAT'],'editfile');

	$context['downloads_file'] = array(
		'ID_FILE' => $row['ID_FILE'],
		'id_member' => $row['id_member'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => $row['filesize'],
		'filename' => $row['filename'],
		'fileurl' => $row['fileurl'],
		'ID_CAT' => $row['ID_CAT'],
		'date' => timeformat($row['date']),
		'keywords' => $row['keywords'],
		'real_name' => $row['real_name'],
		'orginalfilename' => $row['orginalfilename'],
		'picture' => $row['picture'],
		'pictureurl' => $row['pictureurl'],
		'demourl' => $row['demourl'],
	);
	$smcFunc['db_free_result']($dbresult);

	$result = $smcFunc['db_query']('', "
	SELECT
		f.title, f.is_required, f.ID_CUSTOM, d.value
	FROM  {db_prefix}tema_custom_field as f
		LEFT JOIN {db_prefix}tema_custom_field_data as d ON (d.ID_CUSTOM = f.ID_CUSTOM)
	WHERE ID_FILE = " . $context['downloads_file']['ID_FILE'] . " AND ID_CAT = " . $context['downloads_file']['ID_CAT']);
	$context['downloads_custom'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['downloads_custom'][] = array(
			'ID_CUSTOM' => $row['ID_CUSTOM'],
			'title' => $row['title'],
			'is_required' => $row['is_required'],
			'value' => $row['value'],

		);
	}
	$smcFunc['db_free_result']($result);


	if (allowedTo('themes_manage') || (allowedTo('themes_edit') && $user_info['id'] == $context['downloads_file']['id_member']))
	{
		 	$dbresult = $smcFunc['db_query']('', "
		 	SELECT
		 		c.ID_CAT, c.title, p.view, p.addfile
		 	FROM {db_prefix}tema_cat AS c
		 		LEFT JOIN {db_prefix}tema_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
		 	WHERE c.redirect = 0 ORDER BY c.roworder ASC");
			$context['downloads_cat'] = array();
		 	while($row = $smcFunc['db_fetch_assoc']($dbresult))
			{
				if ($row['view'] == '0' || $row['addfile'] == '0' )
					continue;

				$context['downloads_cat'][] = array(
				'ID_CAT' => $row['ID_CAT'],
				'title' => $row['title'],
				);
			}
			$smcFunc['db_free_result']($dbresult);

		$context['quotalimit'] = GetQuotaGroupLimit($user_info['id']);
		$context['userspace'] = GetUserSpaceUsed($user_info['id']);
	}
	else
		fatal_error($txt['tema_error_noedit_permission']);
}

function EditDownload2($id,$title,$description,$keywords,$cat,$fileurl,$pictureurl,$demourl,$filesize,$approved)
{
	global $txt, $modSettings, $sourcedir, $smcFunc, $user_info;
    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	id_member,ID_CAT, filename,filesize
    FROM {db_prefix}tema_file
    WHERE ID_FILE = $id LIMIT 1");
    $row = $smcFunc['db_fetch_assoc']($dbresult);
	$memID = $row['id_member'];
	$oldfilesize = $row['filesize'];
	$oldfilename = $row['filename'];
	GetCatPermission($row['ID_CAT'],'editfile');

	$smcFunc['db_free_result']($dbresult);
	if (allowedTo('themes_manage') || (allowedTo('themes_edit') && $user_info['id'] == $memID))
	{

		if (!is_writable($modSettings['tema_path']))
			fatal_error($txt['tema_write_error'] . $modSettings['tema_path']);
		$result = $smcFunc['db_query']('', "
		SELECT
			f.title, f.is_required, f.ID_CUSTOM
		FROM  {db_prefix}tema_custom_field as f
		WHERE f.is_required = 1 AND f.ID_CAT = " . $cat);
		while ($row2 = $smcFunc['db_fetch_assoc']($result))
		{
	 		if (!isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
	 		{
	 			fatal_error($txt['tema_err_req_custom_field'] . $row2['title'], false);
	 		}
	 		else
	 		{
	 			if ($_REQUEST['cus_' . $row2['ID_CUSTOM']] == '')
	 				fatal_error($txt['tema_err_req_custom_field'] . $row2['title'], false);
	 		}
	 	}
		$smcFunc['db_free_result']($result);


	// Upload Category image File
	if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
	{

		$sizes = @getimagesize($_FILES['picture']['tmp_name']);

			// No size, then it's probably not a valid pic.
			if ($sizes === false)
				fatal_error($txt['tema_error_invalid_picture'],false);

			require_once($sourcedir . '/Subs-Graphics.php');

			if ((!empty($modSettings['tema_set_file_image_width']) && $sizes[0] > $modSettings['tema_set_file_image_width']) || (!empty($modSettings['tema_set_file_image_height']) && $sizes[1] > $modSettings['tema_set_file_image_height']))
			{

					// Delete the temp file
					@unlink($_FILES['picture']['tmp_name']);
					fatal_error($txt['tema_error_img_size_height'] . $sizes[1] . $txt['tema_error_img_size_width'] . $sizes[0],false);

			}

		// Move the file
		$extensions = array(
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
		$extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : '.bmp';


		$filename = $id . '.' . $extension;

		move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['tema_path'] . 'temaresim/' . $filename);
		@chmod($modSettings['tema_path'] . 'temaresim/' . $filename, 0644);

		// Update the filename for the category
		$smcFunc['db_query']('', "UPDATE {db_prefix}tema_file
		SET picture = '$filename' WHERE ID_FILE = $id LIMIT 1");


	}


		// Process Uploaded file
		if (isset($_FILES['download']['name']) && $_FILES['download']['name'] != '')
		{

			// Store the orginal filename
			$orginalfilename =  $smcFunc['db_escape_string']($_FILES['download']['name']);
			$filesize = $_FILES['download']['size'];


			if (!empty($modSettings['tema_max_filesize']) && $filesize > $modSettings['tema_max_filesize'])
			{
				// Delete the temp file
				@unlink($_FILES['download']['tmp_name']);
				fatal_error($txt['tema_error_file_filesize'] . format_size($modSettings['tema_max_filesize'], 2) ,false);
			}
			// Check Quota
			$quotalimit = GetQuotaGroupLimit($user_info['id']);
			$userspace = GetUserSpaceUsed($user_info['id']);
			// Check if exceeds quota limit or if there is a quota
			if ($quotalimit != 0  &&  ($userspace + $filesize) >  $quotalimit)
			{
				@unlink($_FILES['download']['tmp_name']);
				fatal_error($txt['tema_error_space_limit'] . format_size($userspace, 2) . ' / ' . format_size($quotalimit, 2) ,false);
			}

			// Delete the old files
			@unlink($modSettings['tema_path'] . $oldfilename );

			$extrafolder = '';

			if ($modSettings['tema_set_enable_multifolder'])
				$extrafolder = $modSettings['tema_folder_id'] . '/';


			// Filename Member Id + Day + Month + Year + 24 hour, Minute Seconds
			$filename = $user_info['id'] . '_' . date('d_m_y_g_i_s');
			move_uploaded_file($_FILES['download']['tmp_name'], $modSettings['tema_path'] . $extrafolder . $filename);
			@chmod($modSettings['tema_path'] . $extrafolder . $filename, 0644);


			// Update the Database entry
			$t = time();

			$smcFunc['db_query']('', "UPDATE {db_prefix}tema_file
					SET ID_CAT = $cat, filesize = $filesize, filename = '" . $extrafolder . $filename . "', approved = $approved,
					 date =  $t, title = '$title', description = '$description', keywords = '$keywords',
					  orginalfilename = '$orginalfilename', pictureurl = '$pictureurl', demourl = '$demourl'
					  WHERE ID_FILE = $id LIMIT 1");

			UpdateUserFileSizeTable($memID,$oldfilesize * -1);
			UpdateUserFileSizeTable($memID,$filesize);


			// Update the file totals
			if ($cat != $row['ID_CAT'])
			{
				UpdateCategoryTotals($cat);
				UpdateCategoryTotals($row['ID_CAT']);
			}


					// Change the file owner if selected
					if (allowedTo('themes_manage') && isset($_REQUEST['pic_postername']))
					{
						$pic_postername = str_replace('"','', $_REQUEST['pic_postername']);
						$pic_postername = str_replace("'",'', $pic_postername);
						$pic_postername = str_replace('\\','', $pic_postername);
						$pic_postername = $smcFunc['htmlspecialchars']($pic_postername, ENT_QUOTES);

						$memid = 0;

						$dbresult = $smcFunc['db_query']('', "
						SELECT
							real_name, id_member
						FROM {db_prefix}members
						WHERE real_name = '$pic_postername' OR member_name = '$pic_postername'  LIMIT 1");
						$row = $smcFunc['db_fetch_assoc']($dbresult);
						$smcFunc['db_free_result']($dbresult);

						if ($smcFunc['db_affected_rows']() != 0)
						{
							// Member found update the file owner

							$memid = $row['id_member'];
							$smcFunc['db_query']('', "UPDATE {db_prefix}tema_file
							SET id_member = $memid WHERE ID_FILE = $id LIMIT 1");

						}

					}
					UpdateCategoryTotalByFileID($id);
					// Redirect to the users files page.
					redirectexit('action=tema;sa=myfiles;u=' . $user_info['id']);


		}
		else
		{
			// Update the download properties if no upload has been set

				$smcFunc['db_query']('', "UPDATE {db_prefix}tema_file
				SET ID_CAT = $cat, title = '$title', description = '$description', keywords = '$keywords',
				approved = $approved,
				fileurl = '$fileurl', pictureurl = '$pictureurl', demourl = '$demourl'

				WHERE ID_FILE = $id LIMIT 1");


					// Update the file totals
					if ($cat != $row['ID_CAT'])
					{
						UpdateCategoryTotals($cat);
						UpdateCategoryTotals($row['ID_CAT']);
					}

				// Change the file owner if selected

					if (allowedTo('themes_manage') && isset($_REQUEST['pic_postername']))
					{
						$pic_postername = str_replace('"','', $_REQUEST['pic_postername']);
						$pic_postername = str_replace("'",'', $pic_postername);
						$pic_postername = str_replace('\\','', $pic_postername);
						$pic_postername = $smcFunc['htmlspecialchars']($pic_postername, ENT_QUOTES);

						$memid = 0;

						$dbresult = $smcFunc['db_query']('', "
						SELECT
							real_name, id_member
						FROM {db_prefix}members
						WHERE real_name = '$pic_postername' OR member_name = '$pic_postername'  LIMIT 1");
						$row = $smcFunc['db_fetch_assoc']($dbresult);
						$smcFunc['db_free_result']($dbresult);

						if ($smcFunc['db_affected_rows']() != 0)
						{
							// Member found update the file owner
							$memid = $row['id_member'];
							$smcFunc['db_query']('', "UPDATE {db_prefix}tema_file
							SET id_member = $memid WHERE ID_FILE = $id LIMIT 1");


						}

					}

					UpdateCategoryTotalByFileID($id);

					// Check for any custom fields

					$smcFunc['db_query']('', "DELETE FROM  {db_prefix}tema_custom_field_data
							WHERE ID_FILE = " . $id);

					$result = $smcFunc['db_query']('', "
					SELECT
						f.title, f.is_required, f.ID_CUSTOM
					FROM  {db_prefix}tema_custom_field as f
					WHERE f.ID_CAT = " . $cat);
					while ($row2 = $smcFunc['db_fetch_assoc']($result))
					{
						if (isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
						{

							$custom_data = $smcFunc['htmlspecialchars']($_REQUEST['cus_' . $row2['ID_CUSTOM']],ENT_QUOTES);

							$smcFunc['db_query']('', "INSERT INTO {db_prefix}tema_custom_field_data
							(ID_FILE, ID_CUSTOM, value)
							VALUES('$id', " . $row2['ID_CUSTOM'] . ", '$custom_data')");
						}
					}
					$smcFunc['db_free_result']($result);


			// Redirect to the users files page.
			redirectexit('action=tema;sa=myfiles;u=' . $user_info['id']);

		}

	}
	else
		fatal_error($txt['tema_error_noedit_permission']);


}

function DeleteDownload($id)
{
	global $context, $txt, $smcFunc;
    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	p.ID_FILE, p.fileurl, p.ID_CAT, p.keywords, p.totaldownloads,
     	p.filesize, p.filename, p.approved, p.views, p.title, p.id_member, p.date, m.real_name, p.description
    FROM {db_prefix}tema_file as p
    LEFT JOIN {db_prefix}members AS m ON (p.id_member = m.id_member)
    WHERE ID_FILE = $id  LIMIT 1");
	if ($smcFunc['db_affected_rows']()== 0)
    	fatal_error($txt['tema_error_no_downloadexist'],false);
    $row = $smcFunc['db_fetch_assoc']($dbresult);
	// Check the category permission
	GetCatPermission($row['ID_CAT'],'delfile');
	// File information
	$context['downloads_file'] = array(
		'ID_FILE' => $row['ID_FILE'],
		'id_member' => $row['id_member'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => $row['filesize'],
		'filename' => $row['filename'],
		'ID_CAT' => $row['ID_CAT'],
		'date' => timeformat($row['date']),
		'keywords' => $row['keywords'],
		'real_name' => $row['real_name'],
		'fileurl' => $row['fileurl'],
		'totaldownloads' => $row['totaldownloads'],
	);
	$smcFunc['db_free_result']($dbresult);
}

function DeleteDownload2($id)
{
	global $txt, $smcFunc;
    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	p.ID_FILE, p.ID_CAT, p.id_member
    FROM {db_prefix}tema_file as p
    WHERE ID_FILE = $id LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	if (empty($row['ID_FILE']))
		fatal_error($txt['tema_error_no_file_selected'],false);
	$memID = $row['id_member'];
	$smcFunc['db_free_result']($dbresult);
	GetCatPermission($row['ID_CAT'],'delfile');
}

function DeleteFileByID($id)
{
	global $modSettings, $smcFunc, $sourcedir,$boarddir;

	require_once($sourcedir . '/RemoveTopic.php');
    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	p.ID_FILE,  p.ID_CAT, p.filesize, p.filename,  p.id_member, p.ID_TOPIC, p.picture
    FROM {db_prefix}tema_file as p
    WHERE ID_FILE = $id LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$oldfilesize = $row['filesize'];
	$memID = $row['id_member'];
	$smcFunc['db_free_result']($dbresult);
	if ($row['filename'] != '')
		@unlink($modSettings['tema_path'] . $row['filename']);
	$oldfilesize = $oldfilesize * -1;
	if ($oldfilesize != 0)
	UpdateUserFileSizeTable($memID,$oldfilesize);
	UpdateCategoryTotalByFileID($id);
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_rating WHERE ID_FILE  = $id");
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_report WHERE ID_FILE  = $id");
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_creport WHERE ID_FILE  = $id");
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_custom_field_data WHERE ID_FILE  = $id");
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_file WHERE ID_FILE = $id LIMIT 1");
	$yolumuz = $boarddir.'/tema/temaresim/';
	@unlink($yolumuz.$row['picture']);
 	if ($row['ID_TOPIC'] != 0)
		removeTopics($row['ID_TOPIC']);
}
function ReportDownload2($comment,$id,$commentdate,$memid)
{
	global  $smcFunc;
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}tema_report
			(id_member, comment, date, ID_FILE)
		VALUES (" . $memid . ",'$comment', $commentdate,$id)");
}

function CatUp($cat)
{
	global $txt, $smcFunc;
	$dbresult1 = $smcFunc['db_query']('', "
	SELECT
		roworder,ID_PARENT
	FROM {db_prefix}tema_cat
	WHERE ID_CAT = $cat");
	$row = $smcFunc['db_fetch_assoc']($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o--;
	$smcFunc['db_free_result']($dbresult1);
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_CAT, roworder
	FROM {db_prefix}tema_cat
	WHERE ID_PARENT = $ID_PARENT AND roworder = $o");
	if ($smcFunc['db_affected_rows']() == 0)
		fatal_error($txt['tema_error_nocat_above'],false);
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat
		SET roworder = $oldrow WHERE ID_CAT = " .$row2['ID_CAT']);
	$smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat
		SET roworder = $o WHERE ID_CAT = $cat");
	$smcFunc['db_free_result']($dbresult);
	redirectexit('action=tema;cat=' . $ID_PARENT);
}

function CatDown($cat)
{
	global $txt, $smcFunc;
	$dbresult1 = $smcFunc['db_query']('', "
	SELECT
		ID_PARENT, roworder
	FROM {db_prefix}tema_cat
	WHERE ID_CAT = $cat LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o++;

	$smcFunc['db_free_result']($dbresult1);
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_CAT, roworder
	FROM {db_prefix}tema_cat
	WHERE ID_PARENT = $ID_PARENT AND roworder = $o");
	if ($smcFunc['db_affected_rows']()== 0)
		fatal_error($txt['tema_error_nocat_below'],false);
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat
		SET roworder = $oldrow WHERE ID_CAT = " .$row2['ID_CAT']);
	$smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat
		SET roworder = $o WHERE ID_CAT = $cat");
	$smcFunc['db_free_result']($dbresult);
	redirectexit('action=tema;cat=' . $ID_PARENT);
}

function MyFiles($u)
{
	global $context, $modSettings,$smcFunc, $user_info;
	$context['downloads_userid'] = $u;
    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	real_name
    FROM {db_prefix}members
    WHERE id_member = $u LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$context['downloads_userdownloads_name'] = $row['real_name'];
	$smcFunc['db_free_result']($dbresult);
	$userid = $context['downloads_userid'];
	$context['start'] = (int) $_REQUEST['start'];
	$extra_page = '';
	if ($user_info['id'] == $userid)
		$extra_page = '';
	else
		$extra_page = ' AND p.approved = 1';
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}tema_file as p
	WHERE p.id_member = $userid " . $extra_page);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);
	$context['downloads_total'] = $total;
	if ($user_info['id'] == $userid)
    	$dbresult = $smcFunc['db_query']('', "
    	SELECT
    		p.ID_FILE, p.filesize, p.approved, p.views, p.id_member,
    		 m.real_name, p.date, p.totaldownloads, p.rating, p.totalratings, p.title
    	FROM {db_prefix}tema_file as p, {db_prefix}members AS m
    	WHERE p.id_member = $userid AND p.id_member = m.id_member
    	ORDER BY p.ID_FILE DESC LIMIT $context[start]," . $modSettings['tema_set_files_per_page']);
	else
    	$dbresult = $smcFunc['db_query']('', "
    	SELECT
    		p.ID_FILE, p.filesize, p.approved, p.views,
    		p.id_member, m.real_name, p.date, p.totaldownloads, p.rating, p.totalratings, p.title
    	FROM {db_prefix}tema_file as p, {db_prefix}members AS m
    	WHERE p.id_member = $userid AND p.id_member = m.id_member AND p.approved = 1
    	ORDER BY p.ID_FILE DESC LIMIT $context[start]," . $modSettings['tema_set_files_per_page']);

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
			'approved' => $row['approved'],

			);

		}
		$smcFunc['db_free_result']($dbresult);
}

function ApproveList()
{
	global $context, $mbname, $txt, $scripturl, $smcFunc;

	isAllowedTo('themes_manage');

	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_form_approvedownloads'];



	$context['sub_template']  = 'approvelist';


	$context['start'] = (int) $_REQUEST['start'];

	// Get Total Pages
		$dbresult = $smcFunc['db_query']('', "
		SELECT
			COUNT(*) AS total
		FROM {db_prefix}tema_file as p
		WHERE p.approved = 0 ORDER BY ID_FILE DESC");
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$total = $row['total'];
		$smcFunc['db_free_result']($dbresult);
	$context['downloads_total'] = $total;

	// List all the unapproved downloads
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		p.ID_FILE, p.ID_CAT, p.title, p.id_member, m.real_name, p.date, p.description, c.title catname
	FROM {db_prefix}tema_file AS p
		LEFT JOIN {db_prefix}members AS m ON (m.id_member = p.id_member)
		LEFT JOIN {db_prefix}tema_cat AS c ON (c.ID_CAT = p.ID_CAT)
	WHERE p.approved = 0
	ORDER BY p.ID_FILE DESC LIMIT $context[start],10");
	$context['downloads_file'] = array();
	 while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_file'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'id_member' => $row['id_member'],
			'real_name' => $row['real_name'],
			'date' => $row['date'],
			'description' => $row['description'],
			'catname' => $row['catname'],
			);
		}
	$smcFunc['db_free_result']($dbresult);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=tema;sa=approvelist', $_REQUEST['start'], $total, 10);


}

function ApproveDownload()
{
	global $txt;
	isAllowedTo('themes_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);

	// Approve the download
	ApproveFileByID($id);

	// Redirect to approval list
	redirectexit('action=admin;area=tema;sa=approvelist');

}

function ApproveFileByID($id)
{
	global $scripturl, $sourcedir, $user_info, $smcFunc;

	// Look up the download and get the category
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		p.ID_FILE, p.id_member, p.filename, p.title, p.description, c.ID_BOARD,
		p.ID_CAT, c.locktopic
	FROM {db_prefix}tema_file AS p
	LEFT JOIN {db_prefix}tema_cat AS c ON (c.ID_CAT = p.ID_CAT)
	WHERE p.ID_FILE = $id LIMIT 1");
	$rowcat = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if ($rowcat['ID_BOARD'] != 0  && $rowcat['ID_BOARD'] != '' )
	{

		$showpostlink = '[url]' . $scripturl . '?action=tema;sa=view;down=' . $id . '[/url]';


					// Create the post
					require_once($sourcedir . '/Subs-Post.php');
					$msgOptions = array(
						'id' => 0,
						'subject' => $rowcat['title'],
						'body' => '[b]' . $rowcat['title'] . "[/b]\n\n$showpostlink\n\n" . $rowcat['description'],
						'icon' => 'xx',
						'smileys_enabled' => 1,
						'attachments' => array(),
					);
					$topicOptions = array(
						'id' => 0,
						'board' => $rowcat['ID_BOARD'],
						'poll' => null,
						'lock_mode' => $rowcat['locktopic'],
						'sticky_mode' => null,
						'mark_as_read' => true,
					);
					$posterOptions = array(
						'id' => $rowcat['id_member'],
						'update_post_count' => !$user_info['is_guest'] && !isset($_REQUEST['msg']),
					);


					preparsecode($msgOptions['body']);
					createPost($msgOptions, $topicOptions, $posterOptions);

				}


	// Update the approval
	$smcFunc['db_query']('', "UPDATE {db_prefix}tema_file SET approved = 1 WHERE ID_FILE = $id LIMIT 1");


	UpdateCategoryTotals($rowcat['ID_CAT']);

}

function UnApproveDownload()
{
	global $txt;
	isAllowedTo('themes_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);

	UnApproveFileByID($id);

	// Redirect to approval list
	redirectexit('action=admin;area=tema;sa=approvelist');
}

function UnApproveFileByID($id)
{
	global $smcFunc;

	// Update the approval
	$smcFunc['db_query']('', "UPDATE {db_prefix}tema_file SET approved = 0 WHERE ID_FILE = $id LIMIT 1");

	UpdateCategoryTotalByFileID($id);
}

function ReportList()
{
	global $context, $mbname, $txt, $smcFunc;

	isAllowedTo('themes_manage');

	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_form_reportdownloads'];


	$context['sub_template']  = 'reportlist';

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		r.ID, r.ID_FILE, r.id_member, m.real_name, r.date, r.comment
	FROM {db_prefix}tema_report as r
		  LEFT JOIN {db_prefix}members AS m ON  (m.id_member = r.id_member)
	ORDER BY r.ID_FILE DESC");

	$context['downloads_reports'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
			$context['downloads_reports'][] = array(
			'ID' => $row['ID'],
			'ID_FILE' => $row['ID_FILE'],
			'comment' => $row['comment'],
			'id_member' => $row['id_member'],
			'real_name' => $row['real_name'],
			'date' => $row['date'],


			);
	}
	$smcFunc['db_free_result']($dbresult);


}

function DeleteReport()
{
	global $txt, $smcFunc;
	// Check the permission
	isAllowedTo('themes_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_report_selected']);

	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_report WHERE ID = $id LIMIT 1");

	// Redirect to redirect list
	redirectexit('action=admin;area=tema;sa=reportlist');
}


function Search()
{
	global $context, $mbname, $txt, $user_info, $smcFunc;

	TopDownloadTabs();

	// Is the user allowed to view the downloads?
	isAllowedTo('themes_view');

	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		c.ID_CAT, c.title, p.view
	FROM {db_prefix}tema_cat as c
	LEFT JOIN {db_prefix}tema_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
	ORDER BY c.roworder ASC");
	$context['downloads_cat'] = array();
	 while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			// Check if they have permission to search these categories
			if ($row['view'] == '0')
					continue;

			$context['downloads_cat'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title']
			);
		}
	$smcFunc['db_free_result']($dbresult);

	$context['sub_template']  = 'search';

	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_search'];
}

function Search2()
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

function RateDownload()
{
	global $txt, $smcFunc, $user_info;

	is_not_guest();

	// Check if they are allowed to rate download
	isAllowedTo('themes_ratefile');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);
	$rating = (int) $_REQUEST['rating'];
	if (empty($rating))
		fatal_error($txt['tema_error_no_rating_selected']);

	// Check if they rated this download?
    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	id_member, ID_FILE
    FROM {db_prefix}tema_rating
    WHERE id_member = " . $user_info['id'] . " AND ID_FILE = $id");

    $found = $smcFunc['db_affected_rows']();
 	$smcFunc['db_free_result']($dbresult);

	// Get the download owner
    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	id_member
    FROM {db_prefix}tema_file
    WHERE ID_FILE = $id LIMIT 1");
    $row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	// Check if they are rating their own download.
	if ($user_info['id'] == $row['id_member'])
		fatal_error($txt['tema_error_norate_own'],false);

	if ($found != 0)
		fatal_error($txt['tema_error_already_rated'],false);

	// Check the Rating
	if ($rating < 1 || $rating > 5)
		$rating = 3;

	// Add the Rating
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}tema_rating (id_member, ID_FILE, value) VALUES (" . $user_info['id'] . ", $id,$rating)");

	// Add rating information to the download
	$smcFunc['db_query']('', "
	UPDATE {db_prefix}tema_file
		SET totalratings = totalratings + 1, rating = rating + $rating
	WHERE ID_FILE = $id LIMIT 1");

	// Redirect to the download
	redirectexit('action=tema;sa=view;down=' . $id);

}

function ViewRating()
{
	global $context, $mbname, $txt, $smcFunc;

	// Get the download ID for the ratings
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);

	$context['downloads_id'] = $id;

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		r.ID, r.value, r.ID_FILE, r.id_member, m.real_name
	FROM {db_prefix}tema_rating as r, {db_prefix}members AS m
	WHERE r.ID_FILE = $id AND r.id_member = m.id_member");
	$context['downloads_rating'] = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_rating'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'ID' => $row['ID'],
			'value' => $row['value'],
			'id_member' => $row['id_member'],
			'real_name' => $row['real_name'],

			);

		}
	$smcFunc['db_free_result']($dbresult);

	isAllowedTo('themes_manage');

	$context['sub_template']  = 'view_rating';

	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_form_viewratings'];

}

function DeleteRating()
{
	global $scripturl, $txt, $smcFunc;
	isAllowedTo('themes_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_rating_selected']);

	// First lookup the ID to get the download id and value of rating
	 $dbresult = $smcFunc['db_query']('', "
	 SELECT
	 	ID, ID_FILE, value
	 FROM {db_prefix}tema_rating
	 WHERE ID = $id LIMIT 1");
	 $row = $smcFunc['db_fetch_assoc']($dbresult);
	 $value = $row['value'];
	 $fileid = $row['ID_FILE'];
	 $smcFunc['db_free_result']($dbresult);
	// Delete the Rating
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_rating
	WHERE ID = " . $id . ' LIMIT 1');
	// Update the download rating information
	$dbresult = $smcFunc['db_query']('', "UPDATE {db_prefix}tema_file SET totalratings = totalratings - 1, rating = rating - $value WHERE ID_FILE = $fileid LIMIT 1");
	// Redirect to the ratings
	redirectexit('action=tema;sa=viewrating&id=' .  $fileid);
}

function Stats()
{
	global $context, $mbname,$txt, $context, $scripturl, $smcFunc;

	// Is the user allowed to view the downloads?
	isAllowedTo('themes_view');

	TopDownloadTabs();

	// Get views total and comments total and total filesize
	$result = $smcFunc['db_query']('', "
	SELECT
		SUM(views) AS views, SUM(filesize) AS filesize, SUM(totaldownloads) AS totaldownloads,
	 	COUNT(*) AS filetotal
	FROM {db_prefix}tema_file");
	$row = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	$result2 = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS filetotal
	FROM {db_prefix}tema_file");
	$row2 = $smcFunc['db_fetch_assoc']($result2);
	$smcFunc['db_free_result']($result2);

	$context['total_files'] = $row2['filetotal'];
	$context['total_views'] = $row['views'];
	$context['total_filesize'] =  format_size($row['filesize'], 2) ;
	$context['total_downloads'] = $row['totaldownloads'];


	// Top Viewed Downloads
	$result = $smcFunc['db_query']('', "
	SELECT
		ID_FILE, title,views
	FROM {db_prefix}tema_file
	WHERE approved = 1 AND views > 0
	ORDER BY views DESC LIMIT 10");
	$context['top_viewed'] = array();
	$max_views = 1;
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['top_viewed'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'views' => $row['views'],
			'link' => '<a href="' . $scripturl . '?action=tema;sa=view;down=' . $row['ID_FILE'] . '">' . $row['title'] . '</a>',
		);

		if ($max_views < $row['views'])
			$max_views = $row['views'];
	}
	$smcFunc['db_free_result']($result);

	foreach ($context['top_viewed'] as $i => $file)
		$context['top_viewed'][$i]['percent'] = round(($file['views'] * 100) / $max_views);

	// Top Rated
	$result = $smcFunc['db_query']('', "
	SELECT
		ID_FILE, title,rating
	FROM {db_prefix}tema_file
	WHERE approved = 1 AND totalratings > 0
	ORDER BY rating DESC LIMIT 10");
	$context['top_rating'] = array();
	$max_rating = 1;
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['top_rating'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'rating' => $row['rating'],
			'link' => '<a href="' . $scripturl . '?action=tema;sa=view;down=' . $row['ID_FILE'] . '">' . $row['title'] . '</a>',
		);

		if ($max_rating < $row['rating'])
			$max_rating = $row['rating'];
	}
	$smcFunc['db_free_result']($result);

	foreach ($context['top_rating'] as $i => $file)
		$context['top_rating'][$i]['percent'] = round(($file['rating'] * 100) / $max_rating);

	// Top download list
	$result = $smcFunc['db_query']('', "
	SELECT
		ID_FILE, title, totaldownloads
	FROM {db_prefix}tema_file
	WHERE approved = 1
	ORDER BY totaldownloads DESC LIMIT 10");
	$context['totaldownloads'] = array();
	$max_totaldownloads = 1;
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['totaldownloads'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'totaldownloads' => $row['totaldownloads'],
			'link' => '<a href="' . $scripturl . '?action=tema;sa=view;down=' . $row['ID_FILE'] . '">' . $row['title'] . '</a>',
		);

		if ($max_totaldownloads < $row['totaldownloads'])
			$max_totaldownloads = $row['totaldownloads'];
	}
	$smcFunc['db_free_result']($result);

	foreach ($context['totaldownloads'] as $i => $file)
		$context['totaldownloads'][$i]['percent'] = round(($file['totaldownloads'] * 100) / $max_totaldownloads);

	// Last 10 downloads uploaded
	$result = $smcFunc['db_query']('', "
	SELECT
		ID_FILE, title
	FROM {db_prefix}tema_file
	WHERE approved = 1
	ORDER BY ID_FILE DESC LIMIT 10");
	$context['last_upload'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['last_upload'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'link' => '<a href="' . $scripturl . '?action=tema;sa=view;down=' . $row['ID_FILE'] . '">' . $row['title'] . '</a>',
		);
	}
	$smcFunc['db_free_result']($result);


	// Load the template
	$context['sub_template']  = 'stats';
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_stats'];

}

function UpdateUserFileSizeTable($memberid, $filesize)
{
	global $smcFunc;

	// Check if a record exits
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_member,totalfilesize
	FROM {db_prefix}tema_userquota
	WHERE id_member = $memberid LIMIT 1");
	$count = $smcFunc['db_affected_rows']();
	$smcFunc['db_free_result']($dbresult);

	if ($count == 0)
	{
		// Create the record
		$smcFunc['db_query']('', "INSERT INTO {db_prefix}tema_userquota (id_member, totalfilesize) VALUES ($memberid, $filesize)");
	}
	else
	{
		// Update the record
		if ($filesize >= 0)
			$smcFunc['db_query']('', "UPDATE {db_prefix}tema_userquota SET totalfilesize = totalfilesize + $filesize WHERE id_member = $memberid LIMIT 1");
		else
			$smcFunc['db_query']('', "UPDATE {db_prefix}tema_userquota SET totalfilesize = totalfilesize + $filesize WHERE id_member = $memberid LIMIT 1");
	}
}

function FileSpaceAdmin()
{
	global $mbname, $txt, $context, $scripturl, $smcFunc;
	// Check if they are allowed to manage the downloads
	isAllowedTo('themes_manage');

	loadLanguage('Admin');

	// Set the page tile
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_filespace'];
	// Load the subtemplate for the file manager
	$context['sub_template']  = 'filespace';

	// Load the membergroups
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_GROUP, group_name
	FROM {db_prefix}membergroups
	WHERE min_posts = -1 ORDER BY group_name");
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
			);
	}
	$smcFunc['db_free_result']($dbresult);


	$dbresult = $smcFunc['db_query']('', "
	SELECT
		q.totalfilesize,  q.ID_GROUP, m.group_name
	FROM {db_prefix}tema_groupquota as q, {db_prefix}membergroups AS m
	WHERE  q.ID_GROUP = m.ID_GROUP ORDER BY q.totalfilesize");
	$context['downloads_membergroups'] = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_membergroups'][] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'totalfilesize' => $row['totalfilesize'],
			'group_name' => $row['group_name'],


			);

		}
	$smcFunc['db_free_result']($dbresult);

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		q.totalfilesize, q.ID_GROUP
	FROM {db_prefix}tema_groupquota as q
	WHERE  q.ID_GROUP = 0 LIMIT 1");
	$context['downloads_reggroup'] = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_reggroup'][] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'totalfilesize' => $row['totalfilesize'],
			);

		}
	$smcFunc['db_free_result']($dbresult);


	$context['start'] = (int) $_REQUEST['start'];

	// Get Total Pages
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}tema_userquota as q");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);
	$context['downloads_total'] = $total;

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		q.totalfilesize,  q.id_member, m.real_name
	FROM {db_prefix}tema_userquota as q, {db_prefix}members AS m
	WHERE  q.id_member = m.id_member
	ORDER BY q.totalfilesize DESC  LIMIT $context[start],20");
	$context['downloads_members'] = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_members'][] = array(
			'id_member' => $row['id_member'],
			'totalfilesize' => $row['totalfilesize'],
			'real_name' => $row['real_name'],


			);

		}
	$smcFunc['db_free_result']($dbresult);


	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=tema;sa=filespace', $_REQUEST['start'], $total, 20);


}

function FileSpaceList()
{
	global $mbname, $txt, $context, $scripturl, $smcFunc;
	// Check if they are allowed to manage the downloads
	isAllowedTo('themes_manage');


	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_user_selected']);

    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	m.real_name
    FROM {db_prefix}members AS m
    WHERE m.id_member = $id  LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$context['downloads_filelist_real_name'] = $row['real_name'];
	$context['downloads_filelist_userid'] = $id;
	$smcFunc['db_free_result']($dbresult);

	// Set the page tile
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_filespace'] . ' - ' . $context['downloads_filelist_real_name'];
	// Load the subtemplate for the file manager
	$context['sub_template']  = 'filelist';

	$context['start'] = (int) $_REQUEST['start'];

	// Get Total Pages
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}tema_file
	WHERE id_member = " . $context['downloads_filelist_userid']);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);
	$context['downloads_total'] = $total;



	$dbresult = $smcFunc['db_query']('', "
	SELECT
		p.ID_FILE,p.title, p.filesize,p.id_member
	FROM {db_prefix}tema_file as p
	WHERE p.id_member = " . $context['downloads_filelist_userid'] . "
	ORDER BY p.filesize DESC  LIMIT $context[start],20");
	$context['downloads_files'] = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_files'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'filesize' => $row['filesize'],
			'id_member' => $row['id_member'],


			);

		}
	$smcFunc['db_free_result']($dbresult);


	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=tema;sa=filelist&id=' . $context['downloads_filelist_userid'], $_REQUEST['start'], $total, 20);

}

function RecountFileQuotaTotals($redirect = true)
{
	global $smcFunc;

	if ($redirect == true)
		isAllowedTo('themes_manage');

	// Show all the user's with quota information
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_member
	FROM {db_prefix}tema_userquota");
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		// Loop though the all the files for the member and get the total
		$dbresult2 = $smcFunc['db_query']('', "
		SELECT
			SUM(filesize) as total
		FROM {db_prefix}tema_file
		WHERE id_member = " . $row['id_member']);

		$row2 = $smcFunc['db_fetch_assoc']($dbresult2);
		$total = $row2['total'];

		if ($total == '')
			$total = 0;

		$smcFunc['db_free_result']($dbresult2);
		// Update the quota
		$smcFunc['db_query']('', "UPDATE {db_prefix}tema_userquota SET totalfilesize = $total WHERE id_member = " . $row['id_member'] . " LIMIT 1");

	}
	$smcFunc['db_free_result']($dbresult);

	if ($redirect == true)
		redirectexit('action=admin;area=tema;sa=filespace');
}

function GetQuotaGroupLimit($memberid)
{
	global $smcFunc;

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		m.id_member, q.ID_GROUP, q.totalfilesize
	FROM {db_prefix}tema_groupquota as q, {db_prefix}members as m
	WHERE m.id_member = $memberid AND q.ID_GROUP = m.ID_GROUP LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	if ($smcFunc['db_affected_rows']() == 0)
	{
		$smcFunc['db_free_result']($dbresult);
		return 0;
	}
	else
	{
		$smcFunc['db_free_result']($dbresult);

		return $row['totalfilesize'];
	}

}

function GetUserSpaceUsed($memberid)
{
	global $smcFunc;

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_member,totalfilesize
	FROM {db_prefix}tema_userquota
	WHERE id_member = $memberid LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	if ($smcFunc['db_affected_rows']()== 0)
	{
		$smcFunc['db_free_result']($dbresult);
		return 0;
	}
	else
	{
		$smcFunc['db_free_result']($dbresult);

		return $row['totalfilesize'];
	}

}

function AddQuota()
{
	global $txt, $smcFunc;

	isAllowedTo('themes_manage');

	$groupid = (int) $_REQUEST['groupname'];

	$filelimit = (double) $_REQUEST['filelimit'];
	if (empty($filelimit))
	{
		fatal_error($txt['tema_error_noquota'],false);
	}

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_GROUP
	FROM {db_prefix}tema_groupquota
	WHERE ID_GROUP = $groupid LIMIT 1");
	$count = $smcFunc['db_affected_rows']();
	$smcFunc['db_free_result']($dbresult);

	if ($count == 0)
	{
		// Create the record
		$smcFunc['db_query']('', "INSERT INTO {db_prefix}tema_groupquota (ID_GROUP, totalfilesize) VALUES ($groupid, $filelimit)");
	}
	else
	{
		fatal_error($txt['tema_error_quotaexist'],false);
	}

	redirectexit('action=admin;area=tema;sa=filespace');
}

function DeleteQuota()
{
	global $smcFunc;

	isAllowedTo('themes_manage');
	$id = (int) $_REQUEST['id'];

	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_groupquota WHERE ID_GROUP = " . $id . ' LIMIT 1');

	redirectexit('action=admin;area=tema;sa=filespace');
}

function CatPerm()
{
	global $mbname, $txt, $context, $smcFunc;
	isAllowedTo('themes_manage');

	$cat = (int) $_REQUEST['cat'];
	if (empty($cat))
		fatal_error($txt['tema_error_no_cat']);

	$dbresult1 = $smcFunc['db_query']('', "
	SELECT
		ID_CAT, title
	FROM {db_prefix}tema_cat
	WHERE ID_CAT = $cat LIMIT 1");
	$row1 = $smcFunc['db_fetch_assoc']($dbresult1);
	$context['downloads_cat_name'] = $row1['title'];
	$smcFunc['db_free_result']($dbresult1);

	loadLanguage('Admin');

	$context['downloads_cat'] = $cat;

	// Load the template
	$context['sub_template']  = 'catperm';
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_catperm'] . ' -' . $context['downloads_cat_name'];

	// Load the membergroups
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_GROUP, group_name
	FROM {db_prefix}membergroups
	WHERE min_posts = -1 ORDER BY group_name");
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
			);
	}
	$smcFunc['db_free_result']($dbresult);


	// Membergroups
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		c.ID_CAT, c.ID, c.view, c.viewdownload, c.addfile, c.editfile, c.delfile, c.ID_GROUP, m.group_name,a.title catname
	FROM ({db_prefix}tema_catperm as c, {db_prefix}membergroups AS m,{db_prefix}tema_cat as a)
	WHERE  c.ID_CAT = " . $context['downloads_cat'] . " AND c.ID_GROUP = m.ID_GROUP AND a.ID_CAT = c.ID_CAT");
	$context['downloads_membergroups'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_membergroups'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'ID' => $row['ID'],
			'view' => $row['view'],
			'viewdownload' => $row['viewdownload'],
			'addfile' => $row['addfile'],
			'editfile' => $row['editfile'],
			'delfile' => $row['delfile'],
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
			'catname' => $row['catname'],
			);

		}
	$smcFunc['db_free_result']($dbresult);


	$dbresult = $smcFunc['db_query']('', "
	SELECT
		c.ID_CAT, c.ID, c.view, c.viewdownload, c.addfile, c.editfile, c.delfile, c.ID_GROUP,a.title catname
	FROM {db_prefix}tema_catperm as c,{db_prefix}tema_cat as a
	WHERE c.ID_CAT = " . $context['downloads_cat'] . " AND c.ID_GROUP = 0 AND a.ID_CAT = c.ID_CAT LIMIT 1");
	$context['downloads_reggroup'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_reggroup'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'ID' => $row['ID'],
			'view' => $row['view'],
			'viewdownload' => $row['viewdownload'],
			'addfile' => $row['addfile'],
			'editfile' => $row['editfile'],
			'delfile' => $row['delfile'],
			'ID_GROUP' => $row['ID_GROUP'],
			'catname' => $row['catname'],
			);

		}
	$smcFunc['db_free_result']($dbresult);


	$dbresult = $smcFunc['db_query']('', "
	SELECT
		c.ID_CAT, c.ID, c.view, c.viewdownload, c.addfile, c.editfile, c.delfile, c.ID_GROUP,a.title catname
	FROM {db_prefix}tema_catperm as c,{db_prefix}tema_cat as a
	WHERE c.ID_CAT = " . $context['downloads_cat'] . " AND c.ID_GROUP = -1 AND a.ID_CAT = c.ID_CAT LIMIT 1");
	$context['downloads_guestgroup'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_guestgroup'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'ID' => $row['ID'],
			'view' => $row['view'],
			'viewdownload' => $row['viewdownload'],
			'addfile' => $row['addfile'],
			'editfile' => $row['editfile'],
			'delfile' => $row['delfile'],
			'ID_GROUP' => $row['ID_GROUP'],
			'catname' => $row['catname'],
			);

		}
	$smcFunc['db_free_result']($dbresult);

}

function CatPerm2()
{
	global $txt, $smcFunc;
	isAllowedTo('themes_manage');

	$groupname = (int) $_REQUEST['groupname'];
	$cat = (int) $_REQUEST['cat'];

	// Check if permission exits
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_GROUP,ID_CAT
	FROM {db_prefix}tema_catperm
	WHERE ID_GROUP = $groupname AND ID_CAT = $cat");
	if ($smcFunc['db_affected_rows']()!= 0)
	{
		$smcFunc['db_free_result']($dbresult);
		fatal_error($txt['tema_permerr_permexist'],false);
	}
	$smcFunc['db_free_result']($dbresult);

	// Permissions
	$view = isset($_REQUEST['view']) ? 1 : 0;
	$viewdownload = isset($_REQUEST['viewdownload']) ? 1 : 0;
	$add = isset($_REQUEST['add']) ? 1 : 0;
	$edit = isset($_REQUEST['edit']) ? 1 : 0;
	$delete = isset($_REQUEST['delete']) ? 1 : 0;

	// Insert into database
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}tema_catperm
			(ID_GROUP,ID_CAT,view,addfile,editfile,delfile,viewdownload)
		VALUES ($groupname,$cat,$view,$add,$edit,$delete,$viewdownload)");

	redirectexit('action=tema;sa=catperm;cat=' . $cat);
}

function CatPermList()
{
	global $mbname, $txt, $context, $smcFunc;
	isAllowedTo('themes_manage');


	// Load the template
	$context['sub_template']  = 'catpermlist';

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['tema_text_title'] . ' - ' . $txt['tema_text_catpermlist'];

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile,
		c.ID_GROUP, m.group_name,a.title catname
	FROM ({db_prefix}tema_catperm as c, {db_prefix}membergroups AS m,{db_prefix}tema_cat as a)
	WHERE  c.ID_GROUP = m.ID_GROUP AND a.ID_CAT = c.ID_CAT");
	$context['downloads_membergroups'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_membergroups'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'ID' => $row['ID'],
			'view' => $row['view'],
			'addfile' => $row['addfile'],
			'editfile' => $row['editfile'],
			'delfile' => $row['delfile'],
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
			'catname' => $row['catname'],
			);

		}
	$smcFunc['db_free_result']($dbresult);

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile,  c.ID_GROUP,a.title catname
	FROM {db_prefix}tema_catperm as c,{db_prefix}tema_cat as a
	WHERE  c.ID_GROUP = 0 AND a.ID_CAT = c.ID_CAT LIMIT 1");
	$context['downloads_regmem'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_regmem'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'ID' => $row['ID'],
			'view' => $row['view'],
			'addfile' => $row['addfile'],
			'editfile' => $row['editfile'],
			'delfile' => $row['delfile'],
			'ID_GROUP' => $row['ID_GROUP'],
			'catname' => $row['catname'],
			);

		}
	$smcFunc['db_free_result']($dbresult);


	$dbresult = $smcFunc['db_query']('', "
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile,  c.ID_GROUP,a.title catname
	FROM {db_prefix}tema_catperm as c,{db_prefix}tema_cat as a
	WHERE  c.ID_GROUP = -1 AND a.ID_CAT = c.ID_CAT LIMIT 1");
	$context['downloads_guestmem'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['downloads_guestmem'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'ID' => $row['ID'],
			'view' => $row['view'],
			'addfile' => $row['addfile'],
			'editfile' => $row['editfile'],
			'delfile' => $row['delfile'],
			'ID_GROUP' => $row['ID_GROUP'],
			'catname' => $row['catname'],
			);

		}
	$smcFunc['db_free_result']($dbresult);

}

function CatPermDelete()
{
	global $smcFunc;

	isAllowedTo('themes_manage');

	$id = (int) $_REQUEST['id'];

	// Delete the Permission
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}tema_catperm WHERE ID = " . $id . ' LIMIT 1');
	// Redirect to the ratings
	redirectexit('action=admin;area=tema;sa=catpermlist');

}

function GetCatPermission($cat,$perm)
{
	global $txt, $user_info, $smcFunc;
	$cat = (int) $cat;
	if (!$user_info['is_guest'])
	{
		$dbresult = $smcFunc['db_query']('', "
		SELECT
			m.id_member, c.view, c.viewdownload, c.addfile, c.editfile, c.delfile,c.ratefile, c.report
		FROM {db_prefix}tema_catperm as c, {db_prefix}members as m
		WHERE m.id_member = " . $user_info['id'] . " AND c.ID_GROUP = m.ID_GROUP AND c.ID_CAT = $cat LIMIT 1");
	}
	else
		$dbresult = $smcFunc['db_query']('', "
		SELECT
			c.view, c.viewdownload, c.addfile, c.editfile, c.delfile,c.ratefile, c.report
		FROM {db_prefix}tema_catperm as c
		WHERE c.ID_GROUP = -1 AND c.ID_CAT = $cat LIMIT 1");

	if ($smcFunc['db_affected_rows']()== 0)
	{
		$smcFunc['db_free_result']($dbresult);
	}
	else
	{
		$row = $smcFunc['db_fetch_assoc']($dbresult);

		$smcFunc['db_free_result']($dbresult);
		if ($perm == 'view' && $row['view'] == 0)
			fatal_error($txt['tema_perm_no_view'],false);
		else if ($perm == 'viewdownload' && $row['viewdownload'] == 0)
			fatal_error($txt['tema_perm_no_viewdownload'],false);
		else if ($perm == 'addfile' && $row['addfile'] == 0)
			fatal_error($txt['tema_perm_no_add'],false);
		else if ($perm == 'editfile' && $row['editfile'] == 0)
			fatal_error($txt['tema_perm_no_edit'],false);
		else if ($perm == 'delfile' && $row['delfile'] == 0)
			fatal_error($txt['tema_perm_no_delete'],false);
		else if ($perm == 'ratefile' && $row['ratefile'] == 0)
			fatal_error($txt['tema_perm_no_ratefile'],false);
		else if ($perm == 'report' && $row['report'] == 0)
			fatal_error($txt['tema_perm_no_report'],false);

	}


}

function PreviousDownload()
{
	global $txt, $smcFunc;

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);

	// Get the category
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_FILE, ID_CAT
	FROM {db_prefix}tema_file
	WHERE ID_FILE = $id  LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$ID_CAT = $row['ID_CAT'];

	$smcFunc['db_free_result']($dbresult);

	// Get previous download
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_FILE
	FROM {db_prefix}tema_file
	WHERE ID_CAT = $ID_CAT AND approved = 1 AND ID_FILE > $id  LIMIT 1");
	if ($smcFunc['db_affected_rows']() != 0)
	{
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$ID_FILE = $row['ID_FILE'];
	}
	else
		$ID_FILE = $id;

	$smcFunc['db_free_result']($dbresult);

	redirectexit('action=tema;sa=view;down=' . $ID_FILE);
}

function NextDownload()
{
	global $txt, $smcFunc;

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['tema_error_no_file_selected']);

	// Get the category
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_FILE, ID_CAT
	FROM {db_prefix}tema_file
	WHERE ID_FILE = $id  LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$ID_CAT = $row['ID_CAT'];

	$smcFunc['db_free_result']($dbresult);

	// Get next download
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_FILE
	FROM {db_prefix}tema_file
	WHERE ID_CAT = $ID_CAT AND approved = 1 AND ID_FILE < $id
	ORDER BY ID_FILE DESC LIMIT 1");
	if ($smcFunc['db_affected_rows']() != 0)
	{
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$ID_FILE = $row['ID_FILE'];
	}
	else
		$ID_FILE = $id;
	$smcFunc['db_free_result']($dbresult);

	redirectexit('action=tema;sa=view;down=' . $ID_FILE);
}

function CatImageDelete()
{
	global $smcFunc;

	isAllowedTo('themes_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		exit;

		$smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat
		SET filename = '' WHERE ID_CAT = $id LIMIT 1");

	redirectexit('action=tema;sa=editcat;cat=' . $id);
}
function FileImageDelete()
{
	global $smcFunc;

	isAllowedTo('themes_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		exit;

		$smcFunc['db_query']('', "UPDATE {db_prefix}tema_file
		SET picture = '' WHERE id_file = $id LIMIT 1");

	redirectexit('action=tema;sa=edit&id=' . $id);
}

function ReOrderCats($cat)
{
	global $smcFunc;

	$dbresult1 = $smcFunc['db_query']('', "
	SELECT
		roworder,ID_PARENT
	FROM {db_prefix}tema_cat
	WHERE ID_CAT = $cat");
	$row = $smcFunc['db_fetch_assoc']($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	$smcFunc['db_free_result']($dbresult1);

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_CAT, roworder
	FROM {db_prefix}tema_cat
	WHERE ID_PARENT = $ID_PARENT
	ORDER BY roworder ASC");
	if ($smcFunc['db_affected_rows']() != 0)
	{
		$count = 1;
		while($row2 = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$smcFunc['db_query']('', "UPDATE {db_prefix}tema_cat
			SET roworder = $count WHERE ID_CAT = " . $row2['ID_CAT']);
			$count++;
		}
	}
	$smcFunc['db_free_result']($dbresult);
}

function BulkActions()
{
	isAllowedTo('themes_manage');

	if (isset($_REQUEST['files']))
	{
		$baction = $_REQUEST['doaction'];

		foreach ($_REQUEST['files'] as $value)
		{

			if ($baction == 'approve')
				ApproveFileByID($value);
			if ($baction == 'delete')
				DeleteFileByID($value);

		}
	}

	// Redirect to approval list
	redirectexit('action=admin;area=tema;sa=approvelist');
}

function UpdateCategoryTotals($ID_CAT)
{
	global $smcFunc;
	
	if (empty($ID_CAT))
		return;

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

}

function UpdateCategoryTotalByFileID($id)
{
	global $smcFunc;

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_CAT FROM {db_prefix}tema_file
	WHERE ID_FILE = $id");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	UpdateCategoryTotals($row['ID_CAT']);

}

function CustomUp()
{
	global $txt, $smcFunc;

	// Check Permission
	isAllowedTo('themes_manage');
	// Get the id
	$id = (int) $_REQUEST['id'];

	ReOrderCustom($id);

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

function CustomDown()
{
	global $txt, $smcFunc;

	isAllowedTo('themes_manage');

	// Get the id
	$id = (int) $_REQUEST['id'];

	ReOrderCustom($id);

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

function CustomAdd()
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

function CustomDelete()
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

function ReOrderCustom($id)
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


function ComputeNextFolderID($ID_FILE)
{
	global $modSettings;

	$folderid = floor($ID_FILE / 1000);

	// If the current folder ID does not match the new folder ID update the settings
	if ($modSettings['tema_folder_id'] != $folderid)
		updateSettings(array('tema_folder_id' => $folderid));


}

function CreateDownloadFolder()
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

function GetFileTotals($ID_CAT)
{
	global $modSettings, $subcats_linktree, $scripturl, $smcFunc;

	$total = 0;

	$total += GetTotalByCATID($ID_CAT);
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
	
		$total += GetFileTotalsByParent($ID_CAT,$childArray);

	}


	return $total;
}

function GetFileTotalsByParent($ID_PARENT,$data)
{
	$total = 0;
	foreach($data as $row)
	{
		if ($row['ID_PARENT'] == $ID_PARENT)
		{
			$total += $row['total'];
			$total += GetFileTotalsByParent($row['ID_CAT'],$data);
		}
	}
	
	return $total;
}




function GetTotalByCATID($ID_CAT)
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

function DownloadFile()
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

	// Check if they can download from this category
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

function ShowSubCats($cat,$g_manage)
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

				$totalfiles = GetFileTotals($row['ID_CAT']);

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


function GetParentLink($ID_CAT)
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

		GetParentLink($row1['ID_PARENT']);

		$context['linktree'][] = array(
					'url' => $scripturl . '?action=tema;cat=' . $ID_CAT ,
					'name' => $row1['title']
				);
}

function DoToolBarStrip($button_strip, $direction )
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

function format_size($size, $round = 0)
{
    //Size must be bytes!
    $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    for ($i=0; $size > 1024 && $i < count($sizes) - 1; $i++) $size /= 1024;
    return round($size,$round).$sizes[$i];
}

function ImportTinyPortalDownloads()
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

function ImportDownloads()
{
	global $txt, $context;

	isAllowedTo('themes_manage');



	$context['sub_template'] = 'import';

	$context['page_title'] = $txt['tema_txt_import_downloads'];

}




function ShowUserBox($memCommID, $online_color = '')
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

function GetStarsByPrecent($percent)
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


?>