<?php

function template_mainview()
{
	global $scripturl, $txt, $context, $modSettings, $subcats_linktree, $user_info;


	// Permissions
	$g_manage = allowedTo('themes_manage');
	$g_add = allowedTo('themes_add');


	if ($g_manage)
	{
		// Warn the user if they are managing the downloads that the path it is not writable
		if (!is_writable($modSettings['tema_path']))
			echo '<font color="#FF0000"><b>', $txt['tema_write_error'], $modSettings['tema_path'], '</b></font>';
	}


	// Get the Category if present
	@$cat = (int) $_REQUEST['cat'];

	// Check if a category is selected
	if (!empty($cat))
	{
		// Show the items in the category

		// Permissions if they are allowed to edit or delete their own downloads.
		$g_edit_own = allowedTo('themes_edit');
		$g_delete_own = allowedTo('themes_delete');




		// Show sub catigories
		Downloads_ShowSubCats($cat,$g_manage);


		if (!isset($context['downloads_cat_norate']))
			$context['downloads_cat_norate'] = 0;

		// Show table header
		$count = 0;

		echo '<br /><table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">

		<thead>
		<tr class="title_bar">';

			if ($context['downloads_orderby2'] == 'asc')
				$neworder = 'desc';
			else
				$neworder = 'asc';


			if (!empty($modSettings['tema_set_t_title']))
			{
				echo  '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . ';sortby=title;orderby=' . $neworder . '">',$txt['tema_cat_title'], '</a></th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_rating']) && $context['downloads_cat_norate'] != 1)
			{
				echo '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . ';sortby=mostrated;orderby=' . $neworder . '">', $txt['tema_cat_rating'], '</a></th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_views']))
			{
				echo '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . ';sortby=mostview;orderby=' . $neworder . '">', $txt['tema_cat_views'], '</a></th>';
				$count++;
			}


			if (!empty($modSettings['tema_set_t_downloads']))
			{
				echo '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . ';sortby=mostdowns;orderby=' . $neworder . '">', $txt['tema_cat_downloads'] , '</a></th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_filesize']))
			{
				echo '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . 'sortby=filesize;orderby=' . $neworder . '">',$txt['tema_cat_filesize'], '</a></th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_date']))
			{
				echo '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . ';sortby=date;orderby=' . $neworder . '">',$txt['tema_cat_date'], '</a></th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_username']))
			{
				echo '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . ';sortby=membername;orderby=' . $neworder . '">',$txt['tema_cat_membername'],'</a></th>';
				$count++;
			}


			// Options
			if ($g_manage ||  ($g_delete_own) || ($g_edit_own) )
			{
				echo '<th  class="lefttext">',$txt['tema_cat_options'],'</th>';
				$count++;
			}

		echo '</tr>
		</thead>
		';



		foreach ($context['downloads_files'] as $i => $file)
		{

			echo '<tr  class="windowbg2">';

			if (!empty($modSettings['tema_set_t_title']))
				echo  '<td><a href="' . $scripturl . '?action=tema;sa=view;down=', $file['ID_FILE'], '">', $file['title'], '</a></td>';


			if (!empty($modSettings['tema_set_t_rating']) && $context['downloads_cat_norate'] != 1)
				echo '<td>', Downloads_GetStarsByPrecent(($file['totalratings'] != 0) ? ($file['rating'] / ($file['totalratings']* 5) * 100) : 0), '</td>';

			if (!empty($modSettings['tema_set_t_views']))
				echo '<td>', $file['views'], '</td>';

			if (!empty($modSettings['tema_set_t_downloads']))
				echo '<td>', $file['totaldownloads'], '</td>';

			if (!empty($modSettings['tema_set_t_filesize']))
				echo '<td>', Downloads_format_size($file['filesize'], 2) . '</td>';

			if (!empty($modSettings['tema_set_t_date']))
				echo '<td>', timeformat($file['date']), '</td>';

			if (!empty($modSettings['tema_set_t_username']))
			{
				if ($file['real_name'] != '')
					echo '<td><a href="' . $scripturl . '?action=profile;u=' . $file['id_member'] . '">'  . $file['real_name'] . '</a></td>';
				else
					echo '<td>', $txt['tema_guest'], '</td>';
			}


			// Options
			if ($g_manage ||  ($g_delete_own && $file['id_member'] == $user_info['id']) || ($g_edit_own && $file['id_member'] == $user_info['id']) )
			{
				echo '<td>';
				if ($g_manage)
					echo '<a href="' . $scripturl . '?action=tema;sa=unapprove&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_unapprove'] . '</a>';
				if ($g_manage || $g_edit_own && $file['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=edit&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_edit'] . '</a>';
				if ($g_manage || $g_delete_own && $file['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=delete&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_delete'] . '</a>';

				echo '</td>';
			}



			echo '</tr>';

		}


		// Display who is viewing the downloads.
		if (!empty($modSettings['tema_who_viewing']))
		{
			echo '<tr>
			<td align="center" colspan="', $count, '"><span class="smalltext">';

			// Show just numbers...?
			// show the actual people viewing the topic?
			echo empty($context['view_members_list']) ? '0 ' . $txt['tema_who_members'] : implode(', ', $context['view_members_list']) . (empty($context['view_num_hidden']) || $context['can_moderate_forum'] ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['tema_who_hidden'] . ')');

			// Now show how many guests are here too.
			echo $txt['who_and'], @$context['view_num_guests'], ' ', @$context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['tema_who_viewdownload'], '</span></td></tr>';
		}


			echo '<tr class="titlebg">
					<td align="left" colspan="', $count, '">
					';

					echo $context['page_index'];


			echo '
					</td>
				</tr>';
		echo '
			</table>';
            
            
            	// Show return to downloads link and Show add download if they can
            	echo '
                    <div class="tborder">
            <div class="roundframe centertext">';
    	
			if ($g_manage)
					echo '<a href="', $scripturl, '?action=tema;sa=addcat;cat=', $cat, '">', $txt['tema_text_addsubcat'], '</a>&nbsp;&nbsp;';

				if ($g_add)
					echo '<a href="', $scripturl, '?action=tema;sa=add;cat=', $cat, '">', $txt['tema_text_adddownload'], '</a> ';


				echo '
				<a href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a
            </div>
        </div>';

	}
	else
	{
		// No Category is set then show the main category list
		ShowTopDownloadBar2($txt['tema_text_title']);



		// Show the index page blocks
		if (!empty($modSettings['tema_index_showtop']))
		{
			// Recent
			if (!empty($modSettings['tema_index_recent']))
				MainPageBlock($txt['tema_main_recent'], 'recent');
		}


		// List all the catagories
		echo '<table border="0" cellspacing="1" cellpadding="4" class="table_grid"  align="center" width="100%">
<thead>
<tr class="title_bar">
				<th  class="lefttext first_th" colspan="2">', $txt['tema_text_categoryname'], '</th>
				<th  class="centertext' . ($g_manage ? '' : ' last_th') . '" align="center">', $txt['tema_text_totalfiles'], '</th>
				';
		if	($g_manage)
			echo '
				<th  class="lefttext">', $txt['tema_text_reorder'], '</th>';

		echo '</tr>
		</thead>';


		foreach ($context['downloads_cats'] as $i => $cat_info)
		{
			$cat_url = '';

			// Check permission to show this category
			if ($cat_info['view'] == '0')
				continue;


			$totalfiles  = Downloads_GetFileTotals($cat_info['ID_CAT']);
			$cat_url = $scripturl . '?action=tema;cat=' . $cat_info['ID_CAT'];

			echo '<tr class="windowbg">';

				if ($cat_info['image'] == '' && $cat_info['filename'] == '')
					echo '<td ></td><td  class="windowbg2"><b><a href="' . $cat_url . '">' . parse_bbc($cat_info['title']) . '</a></b><br />' . parse_bbc($cat_info['description']) . '</td>';
				else
				{
					if ($cat_info['filename'] == '')
						echo '<td class="windowbg" width="10%"><a href="' . $cat_url . '"><img src="' . $cat_info['image'] . '" /></a></td>';
					else
						echo '<td class="windowbg" width="10%"><a href="' . $cat_url . '"><img src="' . $modSettings['tema_url'] . 'catimgs/' . $cat_info['filename'] . '" /></a></td>';


					echo '<td class="windowbg2"><b><a href="' . $cat_url . '">' . parse_bbc($cat_info['title']) . '</a></b><br />' . parse_bbc($cat_info['description']) . '</td>';
				}



			// Show total downloads in the category
			echo '<td align="center" valign="middle" class="windowbg">', $totalfiles, '</td>';

			// Show Edit Delete and Order category
			if ($g_manage)
			{
				echo '<td class="windowbg2"><a href="' . $scripturl . '?action=tema;sa=catup;cat=' . $cat_info['ID_CAT'] . '">' . $txt['tema_text_up'] . '</a>&nbsp;<a href="' . $scripturl . '?action=tema;sa=catdown;cat=' . $cat_info['ID_CAT'] . '">' . $txt['tema_text_down'] . '</a></td>
				<td class="windowbg"><a href="' . $scripturl . '?action=tema;sa=editcat;cat=' . $cat_info['ID_CAT'] . '">' . $txt['tema_text_edit'] . '</a>&nbsp;
				<a href="' . $scripturl . '?action=tema;sa=deletecat;cat=' . $cat_info['ID_CAT'] . '">' . $txt['tema_text_delete'] . '</a>
				<br /><br />
					<a href="' . $scripturl . '?action=tema;sa=catperm;cat=' . $cat_info['ID_CAT'] . '">[' . $txt['tema_text_permissions'] . ']</a>
				</td>';

			}


			echo '</tr>';

			// Show any subcategory links
			if ($subcats_linktree != '')
			echo '
			<tr class="windowbg2">
				<td colspan="', ($g_manage ? '5' : '3'),'">&nbsp;<span class="smalltext">',($subcats_linktree != '' ? '<b>' . $txt['tema_sub_cats'] . '</b>' . $subcats_linktree : ''),'</span></td>
			</tr>';



		}
		echo '</table><br /><br />';


	// Show the index page blocks
	if (empty($modSettings['tema_index_showtop']))
	{
		// Recent
		if (!empty($modSettings['tema_index_recent']))
			MainPageBlock($txt['tema_main_recent'], 'recent');
	}

		// Show stats link
			echo '<br />
            <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['tema_stats_title'], '
        </h3>
</div>
            <table class="table_grid">
 					<tr class="windowbg2">
						<td align="center"><a href="' . $scripturl . '?action=tema;sa=stats">', $txt['tema_stats_viewstats'] ,'</a></td>
					</tr>
				</table><br />';

		// See if they are allowed to add catagories Main Index only
		if ($g_manage)
		{
			echo '
            <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['tema_text_adminpanel'], '
        </h3>
</div>

            <table class="table_grid">
 				<tr class="windowbg2">
			<td align="center"><a href="' . $scripturl . '?action=tema;sa=addcat">' . $txt['tema_text_addcategory'] . '</a>&nbsp;
			<a href="' . $scripturl . '?action=admin;area=tema;sa=adminset">' . $txt['tema_text_settings'] . '</a>&nbsp;';


			if (allowedTo('manage_permissions'))
				echo '<a href="', $scripturl, '?action=admin;area=permissions">', $txt['tema_text_permissions'], '</a>';

			// Downloads waiting for approval
			echo '<br />' . $txt['tema_text_fileswaitapproval'] . '<b>',$context['downloads_waitapproval'],'</b>&nbsp;&nbsp;<a href="' . $scripturl . '?action=admin;area=tema;sa=approvelist">' . $txt['tema_text_filecheckapproval'] . '</a>';
			// Reported Downloads
			echo '<br />' . $txt['tema_text_filereported'] . '<b>',$context['downloads_totalreport'],'</b>&nbsp;&nbsp;<a href="' . $scripturl . '?action=admin;area=tema;sa=reportlist">' . $txt['tema_text_filecheckreported'] . '</a>';
			echo '</td></tr></table><br /><br />';
		}
	}

}

function template_add_category()
{
	global $scripturl, $txt, $context, $settings, $modSettings;

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';


	echo '
<form method="post" enctype="multipart/form-data" name="catform" id="catform" action="' . $scripturl . '?action=tema;sa=addcat2" onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['tema_text_addcategory'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr class="windowbg2">
    <td width="28%" align="right"><span class="gen"><b>' . $txt['tema_form_title'] .'</b>&nbsp;</span></td>
    <td width="72%"><input type="text" name="title" size="64" maxlength="100" /></td>
  </tr>
  <tr class="windowbg2">
    <td width="28%" align="right"><span class="gen"><b>' . $txt['tema_text_parentcategory'] .'</b>&nbsp;</span></td>
    <td width="72%"><select name="parent">
    <option value="0">',$txt['tema_text_catnone'],'</option>
    ';

	foreach ($context['downloads_cat'] as $i => $category)
		echo '<option value="' . $category['ID_CAT']  . '" ' . (($context['cat_parent'] == $category['ID_CAT']) ? ' selected="selected"' : '') .'>' . $category['title'] . '</option>';

	echo '</select>
	</td>
  </tr>
  <tr  class="windowbg2">
    <td width="28%"  valign="top" align="right"><span class="gen"><b>' . $txt['tema_form_description'] . '</b>&nbsp;</span><br />'. $txt['tema_text_bbcsupport'] .'</td>
    <td width="72%"><textarea rows="6" name="description" cols="54"></textarea>';

   	if ($context['show_spellchecking'])
   		echo '
   									<br /><input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'catform\', \'description\');" />';
echo '</td>
  </tr>
  <tr class="windowbg2">
    <td width="28%" align="right"><span class="gen"><b>' . $txt['tema_form_icon'] . '</b>&nbsp;</span></td>
    <td width="72%"><input type="text" name="image" size="64" maxlength="100" /></td>
  </tr>
   <tr  class="windowbg2">
    <td width="28%" align="right"><span class="gen"><b>' . $txt['tema_form_uploadicon'] . '</b>&nbsp;</span></td>
    <td width="72%">';


		// Warn the user if the category image path is not writable
		if (!is_writable($modSettings['tema_path'] . 'catimgs'))
			echo '<font color="#FF0000"><b>' . $txt['tema_write_catpatherror']  . $modSettings['tema_path'] . 'catimgs' . '</b></font>';


echo '
    <input type="file" size="48" name="picture" /></td>
  </tr>
  <tr class="windowbg2">
    <td width="28%"   align="right"><span class="gen"><b>' .   $txt['tema_text_cat_disableratings'] . '</b>&nbsp;</span></td>
    <td width="72%"><input type="checkbox" name="disablerating" /></td>
  </tr>
  <tr  class="windowbg2">
    <td width="28%"  align="right"><span class="gen"><b>' .   $txt['tema_txt_sortby']  . '</b>&nbsp;</span></td>
    <td width="72%"><select name="sortby">
		<option value="date">',$txt['tema_txt_sort_date'],'</option>
		<option value="title">',$txt['tema_txt_sort_title'],'</option>
		<option value="mostview">',$txt['tema_txt_sort_mostviewed'],'</option>
		<option value="mostrated">',$txt['tema_txt_sort_mostrated'],'</option>
		<option value="mostdowns">',$txt['tema_txt_sort_mostdowns'],'</option>
		<option value="filesize">',$txt['tema_txt_sort_filesize'],'</option>
		<option value="membername">',$txt['tema_txt_sort_membername'],'</option>
		</select></td>
  </tr>
  <tr  class="windowbg2">
    <td width="28%" align="right"><span class="gen"><b>' .   $txt['tema_txt_orderby'] . '</b>&nbsp;</span></td>
    <td width="72%"><select name="orderby">
		<option value="desc">',$txt['tema_txt_sort_desc'],'</option>
		<option value="asc">',$txt['tema_txt_sort_asc'],'</option>
		</select></td>
  </tr>
  <tr class="windowbg2">
  	<td colspan="2" align="center">
  	<b>' . $txt['tema_text_postingoptions'] . '</b>
  	<hr />
  	' . $txt['tema_postingoptions_info'] . '
  	</td>
  </tr>
  <tr  class="windowbg2">
    <td width="28%" align="right"><span class="gen"><b>' . $txt['tema_text_boardname'] . '</b>&nbsp;</span></td>
    <td width="72%">
  	<select name="boardselect" id="boardselect">
  ';

	foreach ($context['downloads_boards'] as $key => $option)
		 echo '<option value="' . $key . '">' . $option . '</option>';

echo '</select>
	</td>
  </tr>
   <tr  class="windowbg2">
    <td colspan="2" align="center">
    <input type="checkbox" name="locktopic" /><span class="gen"><b>' . $txt['tema_posting_locktopic'] . '</b>&nbsp;</span>
    </td>
  </tr>
   <tr class="windowbg2">
  	<td colspan="2"><hr /></td>
  </tr>
  <tr  class="windowbg2">
    <td width="28%" colspan="2"  align="center">
    <input type="submit" value="', $txt['tema_text_addcategory'], '" name="submit" /></td>

  </tr>
</table>
</form>';

	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';



}

function template_edit_category()
{
	global $scripturl, $txt, $context, $settings, $context, $modSettings;


	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';


	echo '
<form method="post" enctype="multipart/form-data" name="catform" id="catform" action="', $scripturl, '?action=tema;sa=editcat2" onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['tema_text_editcategory'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr class="windowbg2">
    <td width="28%"  align="right"><span class="gen"><b>', $txt['tema_form_title'], '</b>&nbsp;</span></td>
    <td width="72%"><input type="text" name="title" size="64" maxlength="100" value="', $context['tema_catinfo']['title'], '" /></td>
  </tr>
    <tr class="windowbg2">
    <td width="28%"  align="right"><span class="gen"><b>', $txt['tema_text_parentcategory'], '</b>&nbsp;</span></td>
    <td width="72%"><select name="parent">
    <option value="0">', $txt['tema_text_catnone'], '</option>
    ';

		foreach ($context['downloads_cat'] as $i => $category)
		{
			echo '<option value="' . $category['ID_CAT']  . '" ' . (($context['tema_catinfo']['ID_PARENT'] == $category['ID_CAT']) ? ' selected="selected"' : '') .'>' . $category['title'] . '</option>';
		}

	echo '</select>
	</td>
  </tr>
  <tr class="windowbg2">
    <td width="28%"  valign="top" align="right"><span class="gen"><b>' . $txt['tema_form_description'] . '</b>&nbsp;</span><br />' . $txt['tema_text_bbcsupport'] . '</td>
    <td width="72%"><textarea rows="6" name="description" cols="54">' . $context['tema_catinfo']['description'] . '</textarea>';

   	if ($context['show_spellchecking'])
   		echo '
   									<br /><input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'catform\', \'description\');" />';
echo '</td>
  </tr>
  <tr class="windowbg2">
    <td width="28%"  align="right"><span class="gen"><b>' . $txt['tema_form_icon'] . '</b>&nbsp;</span></td>
    <td width="72%"><input type="text" name="image" size="64" maxlength="100" value="' . $context['tema_catinfo']['image'] . '" /></td>
  </tr>
   <tr class="windowbg2">
    <td width="28%"  align="right"><span class="gen"><b>' . $txt['tema_form_uploadicon'] . '</b>&nbsp;</span></td>
    <td width="72%">';


		// Warn the user if the category image path is not writable
		if (!is_writable($modSettings['tema_path'] . 'catimgs'))
			echo '<font color="#FF0000"><b>' . $txt['tema_write_catpatherror']  . $modSettings['tema_path'] . 'catimgs' . '</b></font>';


echo '
    <input type="file" size="48" name="picture" /></td>
  </tr>';

if ($context['tema_catinfo']['filename'] != '')
echo '
  <tr class="windowbg2">
    <td width="28%"  align="right"><span class="gen"><b>' .   $txt['tema_form_filenameicon'] . '</b>&nbsp;</span></td>
    <td width="72%">' . $context['tema_catinfo']['filename'] .  '&nbsp;<a href="' . $scripturl . '?action=tema;sa=catimgdel&id=' . $context['tema_catinfo']['ID_CAT'] . '">' . $txt['tema_rep_deletefile'] . '</a></td>
  </tr>';


		$sortselect = '';
		$orderselect = '';

			switch ($context['tema_catinfo']['sortby'])
			{
				case 'p.ID_FILE':
					$sortselect = '<option value="date">' . $txt['tema_txt_sort_date'] . '</option>';

				break;
				case 'p.title':
					$sortselect = '<option value="title">' . $txt['tema_txt_sort_title'] . '</option>';
				break;

				case 'p.views':
					$sortselect = '<option value="mostview">' . $txt['tema_txt_sort_mostviewed']  . '</option>';
				break;

				case 'p.totalratings':
					$sortselect = '<option value="mostrated">' . $txt['tema_txt_sort_mostrated'] . '</option>';
				break;

				case 'p.totaldownloads':
					$sortselect = '<option value="mostdowns">' . $txt['tema_txt_sort_mostdowns'] . '</option>';
				break;

				case 'p.filesize':
					$sortselect = '<option value="filesize">' . $txt['tema_txt_sort_filesize'] . '</option>';
				break;

				case 'm.real_name':
					$sortselect = '<option value="membername">' . $txt['tema_txt_sort_membername'] . '</option>';
				break;



				default:
					$sortselect = '<option value="date">' . $txt['tema_txt_sort_date'] . '</option>';
				break;
			}



			switch ($context['tema_catinfo']['orderby'])
			{
				case 'ASC':
					$orderselect = '<option value="asc">' .$txt['tema_txt_sort_asc'] .'</option>';

				break;
				case 'DESC':
					$orderselect = '<option value="desc">' . $txt['tema_txt_sort_desc'] . '</option>';
				break;

				default:
					$orderselect = '<option value="DESC">' . $txt['tema_txt_sort_desc'] .' </option>';
				break;
			}



	echo '
	  <tr class="windowbg2">
	    <td width="28%"  align="right"><span class="gen"><b>' .   $txt['tema_text_cat_disableratings'] . '</b>&nbsp;</span></td>
	    <td width="72%"><input type="checkbox" name="disablerating" ' . ($context['tema_catinfo']['disablerating'] ? ' checked="checked"' : '') . ' /></td>
	  </tr>
  <tr class="windowbg2">
    <td width="28%" align="right"><span class="gen"><b>' .   $txt['tema_txt_sortby']  . '</b>&nbsp;</span></td>
    <td width="72%" ><select name="sortby">
    	',$sortselect,'
		<option value="date">',$txt['tema_txt_sort_date'],'</option>
		<option value="title">',$txt['tema_txt_sort_title'],'</option>
		<option value="mostview">',$txt['tema_txt_sort_mostviewed'],'</option>
		<option value="mostrated">',$txt['tema_txt_sort_mostrated'],'</option>
		<option value="mostdowns">',$txt['tema_cat_downloads'],'</option>
		<option value="filesize">',$txt['tema_cat_filesize'],'</option>
		<option value="membername">',$txt['tema_cat_membername'],'</option>
		</select></td>
  </tr>
  <tr class="windowbg2">
    <td width="28%"  align="right"><span class="gen"><b>' .   $txt['tema_txt_orderby'] . '</b>&nbsp;</span></td>
    <td width="72%"><select name="orderby">
    	',$orderselect,'
		<option value="desc">',$txt['tema_txt_sort_desc'],'</option>
		<option value="asc">',$txt['tema_txt_sort_asc'],'</option>
		</select></td>
  </tr>
	  <tr class="windowbg2">
	  	<td colspan="2" align="center">
	  	<b>' . $txt['tema_text_postingoptions'] . '</b>
	  	<hr />
	  	' . $txt['tema_postingoptions_info'] . '
	  	</td>
	  </tr>
	  <tr  class="windowbg2">
	    <td width="28%" align="right"><span class="gen"><b>' . $txt['tema_text_boardname'] . '</b>&nbsp;</span></td>
	    <td width="72%">
	  	<select name="boardselect" id="boardselect">
	  ';

		foreach ($context['downloads_boards'] as $key => $option)
			 echo '<option value="' . $key . '"' . (($context['tema_catinfo']['ID_BOARD']==$key) ? ' selected="selected"' : '') . '>' . $option . '</option>';

	echo '</select>
		</td>
	  </tr>
	   <tr  class="windowbg2">
	    <td colspan="2"  align="center">
    <input type="checkbox" name="locktopic" ' . ($context['tema_catinfo']['locktopic'] ? ' checked="checked"' : '') . ' /><span class="gen"><b>' . $txt['tema_posting_locktopic'] . '</b>&nbsp;</span>
	    </td>
	  </tr>
   <tr  class="windowbg2">
  	<td colspan="2"><hr /></td>
  </tr>
  <tr class="windowbg2">
    <td width="28%" colspan="2"  align="center">
    <input type="hidden" value="' . $context['tema_catinfo']['ID_CAT'] . '" name="catid" />
    <input type="submit" value="' . $txt['tema_text_editcategory'] . '" name="submit" /></td>

  </tr>
</table>
</form><br />';



	echo'
	<hr />
  <div align="center">
  <b>',  $txt['tema_custom_fields'],'</b><br />
  	<form method="post" action="', $scripturl, '?action=tema;sa=cusadd">
  	', $txt['tema_custom_title'], '<input type="text" name="title" />
  	', $txt['tema_custom_default_value'], '<input type="text" name="defaultvalue" />
  	<input type="hidden" name="id" value="',$context['tema_catinfo']['ID_CAT'],'" />
  	<input type="checkbox" name="required" />', $txt['tema_custom_required'], '
  	<input type="submit" name="addfield" value="',$txt['tema_custom_addfield'],'" />
  	</form>
  	</div><br />

 	 <table cellspacing="0" cellpadding="4" border="0" align="center" class="tborder">
 	 	<tr class="catbg">
 	 		<td>', $txt['tema_custom_title'], '</td>
 	 		<td>', $txt['tema_custom_default_value'], '</td>
			<td>', $txt['tema_custom_required'], '</td>
			<td>', $txt['tema_text_options'], '</td>
 	 	</tr>
 	 ';


	// Get all the custom fields
	foreach ($context['tema_custom'] as $i => $custom)
	{
		echo '<tr  class="windowbg2">
 	 		<td>', $custom['title'], '</td>
 	 		<td>', $custom['defaultvalue'], '</td>
			<td>', ($custom['is_required'] ? 'TRUE' : 'FALSE'), '</td>
			<td><a href="' . $scripturl . '?action=tema;sa=cusup&id=' . $custom['ID_CUSTOM'] . '">' . $txt['tema_text_up'] . '</a>&nbsp;<a href="' . $scripturl . '?action=tema;sa=cusdown&id=' . $custom['ID_CUSTOM'] . '">' . $txt['tema_text_down'] . '</a>
			&nbsp;&nbsp;<a href="' . $scripturl . '?action=tema;sa=cusdelete&id=' . $custom['ID_CUSTOM'] . '">' . $txt['tema_text_delete'] . '</a>
			</td>
 	 	</tr>
 	 ';
	}



echo '</table>
    <br />
    <br />
	<div align="center">
	<a href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a>
	</div>
';


	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';


}

function template_delete_category()
{
	global $context, $scripturl, $txt;

	echo '
	<form method="post" action="' . $scripturl . '?action=tema&sa=deletecat2">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="50%" colspan="2"  align="center" class="catbg">
    <b>' . $txt['tema_text_delcategory'] . '</b></td>
  </tr>
  <tr>
    <td width="28%" colspan="2"  align="center" class="windowbg2">
    <b>' . $txt['tema_warn_category'] . '</b>
    <br />
    <i>' . $txt['tema_text_categoryname'] . '&nbsp;"' . $context['cat_title'] . '"&nbsp;' . $txt['tema_text_totalfiles'] . '&nbsp;' . $context['totalfiles'] . '</i>
     <br />
    <input type="hidden" value="' . $context['catid'] . '" name="catid" />
    <input type="submit" value="' . $txt['tema_text_delcategory'] . '" name="submit" /></td>
  </tr>
</table>
</form>';

}

function template_add_download()
{
	global $scripturl, $modSettings, $txt, $context, $settings;

	// Get the category
	$cat = $context['tema_cat'];

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';
	
	   
	echo '<form method="post" enctype="multipart/form-data" name="picform" id="picform" action="' . $scripturl . '?action=tema&sa=add2" onsubmit="submitonce(this);">
    <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['tema_form_adddownload'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['tema_form_title'] . '</b>&nbsp;</td>
  	<td><input type="text" name="title" size="50" /></td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['tema_form_category'] . '</b>&nbsp;</td>
  	<td><select name="cat">';

 	foreach ($context['downloads_cat'] as $i => $category)
	{
		echo '<option value="' . $category['ID_CAT']  . '" ' . (($cat == $category['ID_CAT']) ? ' selected="selected"' : '') .'>' . $category['title'] . '</option>';
	}

 echo '</select>
  	</td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['tema_form_description'] . '</b>&nbsp;</td>
  	<td> <table>
   ';

 	if (!function_exists('getLanguages'))
	{
		// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
								<tr class="windowbg2">

									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'bbc'), '
									</td>
								</tr>';
		}

		// What about smileys?
		if (!empty($context['smileys']['postform']))
			echo '
								<tr class="windowbg2">

									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'smileys'), '
									</td>
								</tr>';

		// Show BBC buttons, smileys and textbox.
		echo '
								<tr class="windowbg2">

									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'message'), '
									</td>
								</tr>';
	}
	else
	{
		echo '
								<tr class="windowbg2">
		<td>';
			// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
					<div id="bbcBox_message"></div>';
		}

		// What about smileys?
		if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
			echo '
					<div id="smileyBox_message"></div>';

		// Show BBC buttons, smileys and textbox.
		echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');


		echo '</td></tr>';
	}


		echo '
			</table>';

     	if ($context['show_spellchecking'])
     		echo '
     									<br /><input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'picform\', \'description\');" />';

echo '
  	</td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>', $txt['tema_form_keywords'], '</b>&nbsp;</td>
  	<td><input type="text" name="keywords" maxlength="100" size="50" />
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>', $txt['tema_form_demourl'], '</b>&nbsp;</td>
  	<td><input type="text" name="demourl" size="50" />
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>', $txt['tema_form_uploadfile'], '</b>&nbsp;</td>

    <td><input type="file" size="48" name="download" />
    </td>
  </tr>
   <tr class="windowbg2">
  	<td align="right"><b>', $txt['tema_form_uploadurl'], '</b>&nbsp;</td>
  	<td><input type="text" name="fileurl" size="50" />
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>', $txt['tema_form_uploadresimfile'], '</b>&nbsp;</td>

    <td><input type="file" size="48" name="picture" />
    </td>
  </tr>
   <tr class="windowbg2">
  	<td align="right"><b>', $txt['tema_form_uploadresimurl'], '</b>&nbsp;</td>
  	<td><input type="text" name="pictureurl" size="50" />
  </tr>
  <tr  class="windowbg2">
  	<td colspan="2"></td>
  </tr>';


	foreach ($context['downloads_custom'] as $i => $custom)
	{
		echo '<tr class="windowbg2">
 	 		<td align="right"><b>', $custom['title'], ($custom['is_required'] ? '<font color="#FF0000">*</font>' : ''), '</b></td>
 	 		<td><input type="text" name="cus_', $custom['ID_CUSTOM'],'" value="' , $custom['defaultvalue'], '" /></td>

 	 	</tr>
 	 ';
	}

  // Display the file quota information
  if ($context['quotalimit'] != 0)
  {
	echo '
	   <tr class="windowbg2">
		<td align="right">',$txt['tema_quotagrouplimit'],'&nbsp;</td>
		<td>',Downloads_format_size($context['quotalimit'], 2),'</td>
	  </tr>
	   <tr class="windowbg2">
		<td align="right">',$txt['tema_quotagspaceused'],'&nbsp;</td>
		<td>',Downloads_format_size($context['userspace'], 2),'</td>
	  </tr>
	   <tr class="windowbg2">
		<td align="right">',$txt['tema_quotaspaceleft'],'&nbsp;</td>
		<td><b>' . Downloads_format_size(($context['quotalimit']-$context['userspace']), 2) . '</b></td>
	  </tr>

	  ';
  }

echo '
  <tr class="windowbg2">
    <td width="28%" colspan="2"  align="center">

    <input type="submit" value="', $txt['tema_form_adddownload'], '" name="submit" /><br />';

  	if (!allowedTo('themes_autoapprove'))
  		echo $txt['tema_form_notapproved'];

echo '
    </td>
  </tr>
</table>

		</form>
';

	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';



}

function template_edit_download()
{
	global $scripturl, $modSettings, $txt, $context, $settings;

	$g_manage = allowedTo('themes_manage');


	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';


	echo '<form method="post" enctype="multipart/form-data" name="picform" id="picform" action="' . $scripturl . '?action=tema&sa=edit2" onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['tema_form_editdownload'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['tema_form_title'] . '</b>&nbsp;</td>
  	<td><input type="text" name="title" size="50" value="' . $context['downloads_file']['title'] . '" /></td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['tema_form_category'] . '</b>&nbsp;</td>
  	<td><select name="cat">';

 	foreach ($context['downloads_cat'] as $i => $category)
	{
		echo '<option value="' . $category['ID_CAT']  . '" ' . (($context['downloads_file']['ID_CAT'] == $category['ID_CAT']) ? ' selected="selected"' : '') .'>' . $category['title'] . '</option>';
	}


 echo '</select>
  	</td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['tema_form_description'] . '</b>&nbsp;</td>
  	<td><table>
   ';

 	if (!function_exists('getLanguages'))
	{
		// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
								<tr class="windowbg2">

									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'bbc'), '
									</td>
								</tr>';
		}

		// What about smileys?
		if (!empty($context['smileys']['postform']))
			echo '
								<tr class="windowbg2">

									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'smileys'), '
									</td>
								</tr>';

		// Show BBC buttons, smileys and textbox.
		echo '
								<tr class="windowbg2">

									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'message'), '
									</td>
								</tr>';
	}
	else
	{
		echo '
								<tr class="windowbg2">
		<td>';
			// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
					<div id="bbcBox_message"></div>';
		}

		// What about smileys?
		if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
			echo '
					<div id="smileyBox_message"></div>';

		// Show BBC buttons, smileys and textbox.
		echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');


		echo '</td></tr>';
	}




		echo '
			</table>';

     	if ($context['show_spellchecking'])
     		echo '
     									<br /><input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'picform\', \'description\');" />';



echo '
  	</td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['tema_form_keywords'] . '</b>&nbsp;</td>
  	<td><input type="text" name="keywords" size="50" maxlength="100" value="' . $context['downloads_file']['keywords'] . '" />
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['tema_form_demourl'] . '</b>&nbsp;</td>
  	<td><input type="text" name="demourl" size="50" value="' . $context['downloads_file']['demourl'] . '" />
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>', $txt['tema_form_uploadfile'], '</b>&nbsp;</td>

    <td><input type="file" size="48" name="download" />
    </td>
  </tr>
   <tr class="windowbg2">
  	<td align="right"><b>', $txt['tema_form_uploadurl'], '</b>&nbsp;</td>
  	<td><input type="text" name="fileurl" size="50" value="' . $context['downloads_file']['fileurl'] . '" />
  </tr>';

if ($context['downloads_file']['picture'] != ''){
echo '
    <td width="28%"  class="windowbg2" align="right"><span class="gen"><b>' .   $txt['tema_form_filenameicon'] . '</b>&nbsp;</span></td>
    <td width="72%"  class="windowbg2">' . $context['downloads_file']['picture'] .  '&nbsp;<a href="' . $scripturl . '?action=tema;sa=fileimgdel&id=' . $context['downloads_file']['ID_FILE'] . '">' . $txt['tema_rep_deletefile'] . '</a></td>
  </tr>';	
}
echo '
  <tr class="windowbg2">
  	<td align="right"><b>', $txt['tema_form_uploadresimfile'], '</b>&nbsp;</td>

    <td><input type="file" size="48" name="picture" />
    </td>
  </tr>
   <tr class="windowbg2">
  	<td align="right"><b>', $txt['tema_form_uploadresimurl'], '</b>&nbsp;</td>
  	<td><input type="text" name="pictureurl" size="50" value="' . $context['downloads_file']['pictureurl'] . '" />
  </tr>


   <tr class="windowbg2">
  	<td colspan="2"><hr /></td>
  </tr>';

	foreach ($context['downloads_custom'] as $i => $custom)
	{
		echo '<tr class="windowbg2">
 	 		<td align="right"><b>', $custom['title'], ($custom['is_required'] ? '<font color="#FF0000">*</font>' : ''), '</b></td>
 	 		<td><input type="text" name="cus_', $custom['ID_CUSTOM'],'" value="' , $custom['value'], '" /></td>

 	 	</tr>';
	}


  // If the user can manage the downloads give them the option to change the download owner.
  if ($g_manage == true)
  {
	  echo '<tr class="windowbg2">
	  <td align="right">', $txt['tema_text_changeowner'], '</td>
	  <td><input type="text" name="pic_postername" id="pic_postername" value="" />
	  <a href="', $scripturl, '?action=findmember;input=pic_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/members.png" alt="', $txt['find_members'], '" /></a>
	  <a href="', $scripturl, '?action=findmember;input=pic_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a>
	  </td>
	  </tr>
	  ';
  }


  // Display the file quota information
  if ($context['quotalimit'] != 0)
  {
	echo '
	   <tr class="windowbg2">
		<td align="right">',$txt['tema_quotagrouplimit'],'&nbsp;</td>
		<td>',Downloads_format_size($context['quotalimit'], 2),'</td>
	  </tr>
	   <tr class="windowbg2">
		<td align="right">',$txt['tema_quotagspaceused'],'&nbsp;</td>
		<td>',Downloads_format_size($context['userspace'], 2),'</td>
	  </tr>
	   <tr class="windowbg2">
		<td align="right">',$txt['tema_quotaspaceleft'],'&nbsp;</td>
		<td><b>', Downloads_format_size(($context['quotalimit']-$context['userspace']), 2), '</b></td>
	  </tr>

	  ';
  }

echo '
  <tr class="windowbg2">
    <td width="28%" colspan="2"  align="center">
	<input type="hidden" name="id" value="' . $context['downloads_file']['ID_FILE'] . '" />
    <input type="submit" value="' . $txt['tema_form_editdownload'] . '" name="submit" /><br />';

  	if (!allowedTo('themes_autoapprove'))
  		echo $txt['tema_form_notapproved'];

echo '<div align="center"><br /><b>' . $txt['tema_text_olddownload'] . '</b><br />
' . $context['downloads_file']['orginalfilename'] . '<br />
			<span class="smalltext">' . $txt['tema_text_views']  . $context['downloads_file']['views'] . '<br />
			' . $txt['tema_text_filesize']  . Downloads_format_size($context['downloads_file']['filesize'],2) . '<br />
			' . $txt['tema_text_date'] . $context['downloads_file']['date'] . '<br />
	</div>
    </td>
  </tr>
</table>

		</form>
';

	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';

}

function template_view_download()
{
	global $scripturl, $context, $txt, $modSettings, $settings, $memberContext, $user_info, $sourcedir, $boardurl;

	// Load permissions
	$g_manage = allowedTo('themes_manage');
	$g_viewdownload = allowedTo('themes_viewdownload');
	$g_edit_own = allowedTo('themes_edit');
	$g_delete_own = allowedTo('themes_delete');
	$g_report = allowedTo('themes_report');


	// Keywords
	$keywords = explode(' ',$context['downloads_file']['keywords']);
 	$keywordscount = count($keywords);


	ShowTopDownloadBar2($context['downloads_file']['title']);
    
    
	// Show the title of the download
        if ($modSettings['tema_set_file_title'])
        echo '
        <div class="cat_bar">
        		<h3 class="catbg centertext">
                ', $context['downloads_file']['title'], '
                </h3>
        </div>';

	echo '<table cellspacing="0" cellpadding="10" border="0" align="center" width="100%" class="tborder">';

		// Show the main download
		echo '
			<tr class="windowbg2">
				<td align="center">';

				//if($g_viewdownload){
					echo '<a href="' . $scripturl . '?action=tema;sa=downfile&id=', $context['downloads_file']['ID_FILE'], '">', ($context['downloads_file']['fileurl'] == '' ? $context['downloads_file']['orginalfilename'] : $txt['tema_app_download']), '</a><br />';

					if($context['downloads_file']['demourl'] != ''){
						echo '<a href="'.$boardurl.'/demo/index.php?tema='.$context['downloads_file']['title'].'">Demo</a>';
						echo '<br />';
					}
					
				//}

				if($modSettings['tema_set_file_thumb'] != 0){
					echo '<img src="',$context['downloads_file']['picture'] == '' ? $context['downloads_file']['pictureurl'] : $modSettings['tema_url'].'temaresim/'.$context['downloads_file']['picture'],'" alt="'.$context['downloads_file']['title'].'">';
				}
			echo '
				</td>
			</tr>';

		echo '
			<tr class="windowbg2">
				<td align="center"><span class="smalltext"><b>';

			if ($modSettings['tema_set_file_showfilesize'] && $context['downloads_file']['fileurl'] == '')
				echo $txt['tema_text_filesize']  . Downloads_format_size($context['downloads_file']['filesize'],2) . '&nbsp;&nbsp;';


			if ($modSettings['tema_set_file_views'])
				echo $txt['tema_text_views'] . ' (' . $context['downloads_file']['views'] . ')&nbsp;&nbsp;';

			if ($modSettings['tema_set_file_downloads'])
				echo $txt['tema_cat_downloads'] . ' (' . $context['downloads_file']['totaldownloads'] . ')&nbsp;&nbsp;';

			if ($modSettings['tema_set_file_lastdownload'])
				echo $txt['tema_text_lastdownload'] . ' ' . ($context['downloads_file']['lastdownload'] != 0 ? timeformat($context['downloads_file']['lastdownload']) : $txt['tema_text_lastdownload2'] ) . '&nbsp;';

			echo '</b></span>
				</td>
			</tr>';

		// Show the previous and next links
		if ($modSettings['tema_set_file_prevnext'])
			echo '<tr class="windowbg2">
			<td align="center"><b>
				<a href="', $scripturl, '?action=tema;sa=prev&id=', $context['downloads_file']['ID_FILE'], '">', $txt['tema_text_prev'], '</a> |
				<a href="', $scripturl, '?action=tema;sa=next&id=', $context['downloads_file']['ID_FILE'], '">', $txt['tema_text_next'], '</a>
				</b>
				
			</td>
			</tr>';

			echo '
			<tr class="windowbg2">
				<td>';

			// Show description
			if ($modSettings['tema_set_file_desc'])
				echo '<b>' . $txt['tema_form_description'] . ' </b>' . parse_bbc($context['downloads_file']['description']);

			echo '
				<hr />';


			if ($modSettings['tema_set_file_keywords'])
				if ($context['downloads_file']['keywords'] != '')
				{
					echo  $txt['tema_form_keywords'] . ' ';

					for($i = 0; $i < $keywordscount;$i++)
					{
						echo '<a href="' . $scripturl . '?action=tema;sa=search2;key=' . $keywords[$i] . '">' . $keywords[$i] . '</a>&nbsp;';

					}
					echo '<br />';
				}

			echo '<b>';
			if ($modSettings['tema_set_file_poster'])
			{

				if ($context['downloads_file']['real_name'] != '')
					echo $txt['tema_text_postedby'] . '<a href="' . $scripturl . '?action=profile;u=' . $context['downloads_file']['id_member'] . '">'  . $context['downloads_file']['real_name'] . '</a>&nbsp;';
				else
					echo $txt['tema_text_postedby'] . ' ' . $txt['tema_guest'] . '&nbsp;';

			}
			if ($modSettings['tema_set_file_date'])
				echo $context['downloads_file']['date'] . '<br />';

			echo '</b>';

				// Show Custom Fields
				foreach ($context['downloads_custom'] as $i => $custom)
				{
					// No reason to show empty custom fields on the display page
					if ($custom['value'] != '')
						echo '<b>', $custom['title'], ':</b>&nbsp;',$custom['value'], '<br />';

				}

		 	echo '<br />';

				// Show rating information
			if ($modSettings['tema_set_file_showrating'])
				if ($modSettings['tema_show_ratings'] == true && $context['downloads_file']['disablerating'] == 0)
				{

					$max_num_stars = 5;

					if ($context['downloads_file']['totalratings'] == 0)
					{
						// Display message that no ratings are in yet
						echo $txt['tema_form_rating'] . $txt['tema_form_norating'];
					}
					else
					{
						// Compute the rating in %
						$rating =($context['downloads_file']['rating'] / ($context['downloads_file']['totalratings']* $max_num_stars) * 100);

						echo $txt['tema_form_rating'] . Downloads_GetStarsByPrecent($rating)  . ' ' . $txt['tema_form_ratingby'] .$context['downloads_file']['totalratings'] . $txt['tema_form_ratingmembers'] . '<br />';
					}

					if (allowedTo('themes_ratefile'))
					{
						echo '<form method="post" action="' . $scripturl . '?action=tema;sa=rate">';
							for($i = 1; $i <= $max_num_stars;$i++)
								echo '<input type="radio" name="rating" value="' . $i .'" />' . str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', $i);


					echo '
							 <input type="hidden" name="id" value="' . $context['downloads_file']['ID_FILE'] . '" />
							 <input type="submit" name="submit" value="' . $txt['tema_form_ratedownload'] . '" />
						';

						// If the user can manage the downloads let them see who voted for what and option to delete rating
						if ($g_manage)
							echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=viewrating&id=' . $context['downloads_file']['ID_FILE'] . '">' . $txt['tema_form_viewratings'] . '</a>';
						echo '</form><br />';
					}
				}

				// Show linking codes

				if (!empty($modSettings['tema_set_showcode_directlink']) || !empty($modSettings['tema_set_showcode_htmllink']))
				{
					echo '<br /><b>',$txt['tema_txt_download_linking'],'</b><br />
					<table border="0">
					';


					if ($modSettings['tema_set_showcode_directlink'])
					{
						echo '<tr><td width="30%">', $txt['tema_txt_directlink'], '</td><td> <input type="text" value="' . $scripturl . '?action=tema;sa=downfile&id=' . $context['downloads_file']['ID_FILE']  . '" size="50"></td></tr>';
					}
					if ($modSettings['tema_set_showcode_htmllink'])
					{
						echo '<tr><td width="30%">', $txt['tema_set_showcode_htmllink'], '</td><td> <input type="text" value="<a href=&#34;' . $scripturl . '?action=tema;sa=downfile&id=' . $context['downloads_file']['ID_FILE']  . '&#34;>', ($context['downloads_file']['fileurl'] == '' ? $context['downloads_file']['orginalfilename'] : $txt['tema_app_download']), '</a>" size="50"></td></tr>';
					}

					echo '</table>';

				}

				// Show edit download links if allowed
				if ($g_manage)
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=unapprove&id=' . $context['downloads_file']['ID_FILE'] . '">' . $txt['tema_text_unapprove'] . '</a>';
				if ($g_manage || $g_edit_own && $context['downloads_file']['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=edit&id=' . $context['downloads_file']['ID_FILE']. '">' . $txt['tema_text_edit'] . '</a>';
				if ($g_manage || $g_delete_own && $context['downloads_file']['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=delete&id=' . $context['downloads_file']['ID_FILE'] . '">' . $txt['tema_text_delete'] . '</a>';


				// Show report download link
				if ($g_report)
				{
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=report&id=' . $context['downloads_file']['ID_FILE'] . '">' . $txt['tema_text_reportdownload'] . '</a>';
				}

				echo '
				</td>
			</tr>';

		// Display who is viewing the download.
		if (!empty($modSettings['tema_who_viewing']))
		{
			echo '<tr class="windowbg2">
			<td align="center"><span class="smalltext">';

			// Show just numbers...?
			// show the actual people viewing the topic?
			echo empty($context['view_members_list']) ? '0 ' . $txt['tema_who_members'] : implode(', ', $context['view_members_list']) . (empty($context['view_num_hidden']) || $context['can_moderate_forum'] ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['tema_who_hidden'] . ')');

			// Now show how many guests are here too.
			echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['tema_who_viewfile'], '</span></td></tr>';
		}

echo '
		</table><br />';
    
         	echo '
                    <div class="tborder">
            <div class="roundframe centertext">';
    	
				echo '
				<a href="' . $scripturl . '?action=tema;cat=' . $context['downloads_file']['ID_CAT'] . '">' . $txt['tema_text_returndownload'] . '</a>
            </div>
        </div>';


}

function template_delete_download()
{
	global $scripturl, $modSettings, $txt, $context, $settings;


	echo '
	<form method="post" action="', $scripturl, '?action=tema;sa=delete2">
    <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['tema_form_deldownload'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">

  <tr class="windowbg2">
    <td width="28%" colspan="2"  align="center" class="windowbg2">
	' . $txt['tema_warn_deletedownload'] . '
	<br />
<div align="center"><br /><b>' . $txt['tema_text_deldownload'] . '</b><br />
<a href="' . $scripturl . '?action=tema;sa=view;down=' . $context['downloads_file']['ID_FILE'] . '" target="blank">',$context['downloads_file']['title'],'</a><br />
			<span class="smalltext">Views: ' . $context['downloads_file']['views'] . '<br />
			' . $txt['tema_text_filesize']  . Downloads_format_size($context['downloads_file']['filesize'],2) . '<br />
			' . $txt['tema_text_date'] . $context['downloads_file']['date'] . '<br />
	</div><br />
	<input type="hidden" name="id" value="' . $context['downloads_file']['ID_FILE'] . '" />
    <input type="submit" value="' . $txt['tema_form_deldownload'] . '" name="submit" /><br />
    </td>
  </tr>
</table>

		</form>
';

}

function template_report_download()
{
	global $scripturl, $context, $txt;

    ShowTopDownloadBar2();

	echo '
<form method="post" name="cprofile" id="cprofile" action="' . $scripturl . '?action=tema;sa=report2">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['tema_form_reportdownload'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr class="windowbg2">
    <td width="28%"  valign="top" align="right"><span class="gen"><b>' . $txt['tema_form_comment'] . '</b>&nbsp;</span></td>
    <td width="72%" ><textarea rows="6" name="comment" cols="54"></textarea></td>
  </tr>
  <tr class="windowbg2">
    <td width="28%" colspan="2" align="center">
    <input type="hidden" name="id" value="' . $context['downloads_file_id'] . '" />
    <input type="submit" value="' . $txt['tema_form_reportdownload'] . '" name="submit" /></td>

  </tr>
</table>
</form>';

}


function template_approvelist()
{
	global $scripturl, $context, $modSettings, $txt;


echo '
			<div class="cat_bar">
								<h3 class="catbg">' . $txt['tema_form_approvedownloads']. '</h3>
            </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg">
			<td>
			<form method="post" action="', $scripturl, '?action=tema;sa=bulkactions">
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
				<thead>
			<tr class="title_bar">
				<td>&nbsp;</th>
				<th  class="lefttext first_th">', $txt['tema_app_download'], '</th>
				<th  class="lefttext">', $txt['tema_text_category'], '</th>
				<th  class="lefttext">', $txt['tema_app_title'], '</th>
				<th  class="lefttext">', $txt['tema_app_description'], '</th>
				<th  class="lefttext">', $txt['tema_app_date'], '</th>
				<th  class="lefttext">', $txt['tema_app_membername'], '</th>
				<th  class="lefttext last_th">', $txt['tema_text_options'], '</th>
				</tr>
				</thead>
				';

            $styleclass = 'windowbg';
			foreach ($context['downloads_file'] as $i => $file)
			{
				echo '<tr class="' . $styleclass . '">';
				echo '<td><input type="checkbox" name="files[]" value="',$file['ID_FILE'],'" /></td>';

				echo '<td><a href="' . $scripturl . '?action=tema;sa=view;down=' . $file['ID_FILE'] . '">',$txt['tema_rep_viewdownload'],'</a></td>';
				echo '<td>' . (empty($file['catname']) ? $file['catname2'] : $file['catname']) . '</td>';
				echo '<td>', $file['title'], '</td>';
				echo '<td>', $file['description'], '</td>';
				echo '<td>', timeformat($file['date']), '</td>';
				if ($file['real_name'] != '')
					echo '<td><a href="' . $scripturl . '?action=profile;u=' . $file['id_member'] . '">'  . $file['real_name'] . '</a></td>';
				else
					echo '<td>',$txt['tema_guest'],'</td>';

				echo '<td><a href="' . $scripturl . '?action=tema;sa=approve&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_approve']  . '</a><br /><a href="' . $scripturl . '?action=tema;sa=edit&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_edit'] . '</a><br /><a href="' . $scripturl . '?action=tema;sa=delete&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';

                if ($styleclass == 'windowbg')
    				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';

			}



		echo '<tr class="titlebg">
				<td align="left" colspan="8">
				';

				echo $context['page_index'];

			echo '<br /><br /><b>',$txt['tema_text_withselected'],'</b>

			<select name="doaction">
			<option value="approve">',$txt['tema_form_approvedownloads'],'</option>
			<option value="delete">',$txt['tema_form_deldownload'],'</option>
			</select>
			<input type="submit" value="',$txt['tema_text_performaction'],'" />
			</form>
			';
		echo '
				</td>
			</tr>
			</table>
			</td>
		</tr>

</table>';

}

function template_reportlist()
{
	global $scripturl, $txt, $context;
echo '
			<div class="cat_bar">
								<h3 class="catbg">' . $txt['tema_form_reportdownloads'] . '</h3>
            </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg">
			<td>
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">
				<thead>
			<tr class="title_bar">
				<th  class="lefttext first_th">', $txt['tema_rep_filelink'], '</th>
				<th  class="lefttext">', $txt['tema_rep_comment'], '</th>
				<th  class="lefttext">', $txt['tema_app_date'], '</th>
				<th  class="lefttext">', $txt['tema_rep_reportby'], '</th>
				<th  class="lefttext last_th">', $txt['tema_text_options'], '</th>
				</tr>
				</thead>
				';

			// List all reported downloads
            $styleclass = 'windowbg';
			foreach ($context['downloads_reports'] as $i => $report)
			{

				echo '<tr class="' . $styleclass . '">';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=view;down=' . $report['ID_FILE'] . '">' . $txt['tema_rep_viewdownload'] .'</a></td>';
				echo '<td>', $report['comment'], '</td>';
				echo '<td>', timeformat($report['date']), '</td>';

				if ($report['real_name'] != '')
					echo '<td><a href="' . $scripturl . '?action=profile;u=' . $report['id_member'] . '">'  . $report['real_name'] . '</a></td>';
				else
					echo '<td>',$txt['tema_guest'],'</td>';

				echo '<td><a href="' . $scripturl . '?action=tema;sa=delete&id=' . $report['ID_FILE'] . '">' . $txt['tema_form_deldownload2']  . '</a>';
				echo '<br /><br /><a href="' . $scripturl . '?action=tema;sa=deletereport&id=' . $report['ID'] . '">' . $txt['tema_rep_delete'] . '</a></td>';
				echo '</tr>';

                if ($styleclass == 'windowbg')
    				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';

			}


echo '
			</table>
			</td>
		</tr>
</table>';

}

function template_search()
{
	global $scripturl, $txt, $context, $settings;


	ShowTopDownloadBar2();

	echo '
<form method="post" action="', $scripturl, '?action=tema;sa=search2">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['tema_search_download'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tborder" align="center">

  <tr class="windowbg2">
    <td width="50%"   align="right"><b>' . $txt['tema_search_for'] . '</b>&nbsp;</td>
    <td width="50%" ><input type="text" name="searchfor" size= "50" />
    </td>
  </tr>
  <tr class="windowbg2" align="center">
  	<td colspan="2"><input type="checkbox" name="searchtitle" checked="checked" />' . $txt['tema_search_title'] . '&nbsp;<input type="checkbox" name="searchdescription" checked="checked" />' . $txt['tema_search_description'] . '&nbsp;
  	<input type="checkbox" name="searchkeywords" />' . $txt['tema_search_keyword'] . '</td>
  </tr>
  <tr class="windowbg2">
  	<td colspan="2" align="center">
  	<hr />
  	<b>',$txt['tema_search_advsearch'],'</b><br />
  	<hr />

  	</td>
  </tr>
    <tr class="windowbg2">
    <td width="30%"  align="right">' . $txt['tema_text_category'] . '&nbsp;</td>
  	<td width="70%">
		<select name="cat">
    	<option value="0">' . $txt['tema_text_catnone'] . '</option>
    ';

	foreach ($context['downloads_cat'] as $i => $category)
	{
		echo '<option value="' . $category['ID_CAT']  . '" >' . $category['title'] . '</option>';
	}

	echo '</select></td>
    </tr>
    <tr class="windowbg2">
     <td width="30%"  align="right">' . $txt['tema_search_daterange']. '&nbsp;</td>
  	<td width="70%">
		<select name="daterange">
    	<option value="0">' . $txt['tema_search_alltime']  . '</option>
    	<option value="30">' . $txt['tema_search_days30']  . '</option>
    	<option value="60">' . $txt['tema_search_days60']  . '</option>
    	<option value="90">' . $txt['tema_search_days90']  . '</option>
    	<option value="180">' . $txt['tema_search_days180']  . '</option>
    	<option value="365">' . $txt['tema_search_days365']  . '</option>

</select></td>
    </tr>

    <tr class="windowbg2">
     <td width="30%"  align="right">' . $txt['tema_search_membername']. '&nbsp;</td>
  	<td width="70%">
		<input type="text" name="pic_postername" id="pic_postername" value="" />
	  <a href="', $scripturl, '?action=findmember;input=pic_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/members.png" alt="', $txt['find_members'], '" /></a>
	  <a href="', $scripturl, '?action=findmember;input=pic_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a>
	  </td>
    </tr>


  <tr>
    <td width="100%" colspan="2"  align="center" class="windowbg2"><br />
    <input type="submit" value="' . $txt['tema_search'] . '" name="submit" />

    <br /></td>

  </tr>
</table>
</form>
<p align="center"><a href="' . $scripturl . '?action=tema">' . $txt['tema_text_returndownload'] . '</a></p>
<br />

';
}

function template_search_results()
{
	global $context, $modSettings, $scripturl, $txt, $user_info;

	// Get the permissions for the user
	$g_add = allowedTo('themes_add');
	$g_manage = allowedTo('themes_manage');
	$g_edit_own = allowedTo('themes_edit');
	$g_delete_own = allowedTo('themes_delete');

	ShowTopDownloadBar2($txt['tema_searchresults']);


	// Show table header
	$count = 0;

		echo '<br /><table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">

		<thead>
		<tr class="title_bar">';

			if (!empty($modSettings['tema_set_t_title']))
			{
				echo  '<th  class="lefttext">', $txt['tema_cat_title'], '</th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_rating']))
			{
				echo '<th  class="lefttext">', $txt['tema_cat_rating'], '</th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_views']))
			{
				echo '<th  class="lefttext">', $txt['tema_cat_views'], '</th>';
				$count++;
			}


			if (!empty($modSettings['tema_set_t_downloads']))
			{
				echo '<th  class="lefttext">', $txt['tema_cat_downloads'] , '</th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_filesize']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_filesize'], '</th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_date']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_date'], '</th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_comment']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_comments'],'</th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_username']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_membername'],'</th>';
				$count++;
			}


			// Options
			if ($g_manage ||  ($g_delete_own ) || ($g_edit_own) )
			{
				echo '<th>',$txt['tema_cat_options'],'</th>';
				$count++;
			}

		echo '</tr>
		</thead>';


	foreach ($context['downloads_files'] as $i => $file)
	{

			echo '<tr class="windowbg2">';

			if (!empty($modSettings['tema_set_t_title']))
				echo  '<td><a href="' . $scripturl . '?action=tema;sa=view;down=', $file['ID_FILE'], '">', $file['title'], '</a></td>';

			if (!empty($modSettings['tema_set_t_rating']))
				echo '<td>', Downloads_GetStarsByPrecent(($file['totalratings'] != 0) ? ($file['rating'] / ($file['totalratings']* 5) * 100) : 0), '</td>';

			if (!empty($modSettings['tema_set_t_views']))
				echo '<td>', $file['views'], '</td>';

			if (!empty($modSettings['tema_set_t_downloads']))
				echo '<td>', $file['totaldownloads'], '</td>';

			if (!empty($modSettings['tema_set_t_filesize']))
				echo '<td>', Downloads_format_size($file['filesize'], 2) . '</td>';

			if (!empty($modSettings['tema_set_t_date']))
				echo '<td>', timeformat($file['date']), '</td>';

			if (!empty($modSettings['tema_set_t_comment']))
				echo '<td><a href="' . $scripturl . '?action=tema;sa=view;down=', $file['ID_FILE'], '">', $file['commenttotal'], '</a></td>';
			if (!empty($modSettings['tema_set_t_username']))
			{
				if ($file['real_name'] != '')
					echo '<td><a href="' . $scripturl . '?action=profile;u=' . $file['id_member'] . '">'  . $file['real_name'] . '</a></td>';
				else
					echo '<td>', $txt['tema_guest'], '</td>';
			}

			// Options
			if ($g_manage ||  ($g_delete_own && $file['id_member'] == $user_info['id']) || ($g_edit_own && $file['id_member'] == $user_info['id']) )
			{
				echo '<td>';
				if ($g_manage)
					echo '<a href="' . $scripturl . '?action=tema;sa=unapprove&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_unapprove'] . '</a>';
				if ($g_manage || $g_edit_own && $file['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=edit&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_edit'] . '</a>';
				if ($g_manage || $g_delete_own && $file['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=delete&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_delete'] . '</a>';

				echo '</td>';
			}



			echo '</tr>';

	}



		if ($context['downloads_total'] > 0)
		{

			$q =  $context['downloads_search_query_encoded'];

			echo '<tr class="titlebg">
					<td align="left" colspan="' . $count . '">
					';


					$context['page_index'] = constructPageIndex($scripturl . '?action=tema;sa=search2;q=' .$q, $_REQUEST['start'], $context['downloads_total'], 10);

					echo $context['page_index'];

			echo '
					</td>
				</tr>';
		}

	echo '</table>';
    
    
    		// Show return to downloads link and Show add download if they can

    	echo '
                    <div class="tborder">
            <div class="roundframe centertext">';
    			if ($g_add)
					echo '<a href="' . $scripturl . '?action=tema;sa=add">' . $txt['tema_text_adddownload'] . '</a><br />';

				echo '
				<a href="' . $scripturl . '?action=tema">' . $txt['tema_text_returndownload'] . '</a>
            </div>
        </div>';



}

function template_myfiles()
{
	global $context, $modSettings, $scripturl, $txt, $user_info;

	// Get the permissions for the user
	$g_add = allowedTo('themes_add');
	$g_manage = allowedTo('themes_manage');
	$g_edit_own = allowedTo('themes_edit');
	$g_delete_own = allowedTo('themes_delete');


	ShowTopDownloadBar2($context['downloads_userdownloads_name']);

// Show table header
		$count = 0;


		echo '<br /><table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">

			<thead>
		<tr class="title_bar">';

			if (!empty($modSettings['tema_set_t_title']))
			{
				echo  '<th  class="lefttext">', $txt['tema_cat_title'], '</th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_rating']) )
			{
				echo '<th  class="lefttext">', $txt['tema_cat_rating'], '</th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_views']))
			{
				echo '<th  class="lefttext">', $txt['tema_cat_views'], '</th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_downloads']))
			{
				echo '<th  class="lefttext">', $txt['tema_cat_downloads'] , '</th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_filesize']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_filesize'], '</th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_date']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_date'], '</th>';
				$count++;
			}

			if (!empty($modSettings['tema_set_t_username']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_membername'],'</th>';
				$count++;
			}


			// Options
			if ($g_manage ||  ($g_delete_own ) || ($g_edit_own) )
			{
				echo '<th class="lefttext">',$txt['tema_cat_options'],'</th>';
				$count++;
			}

		echo '</tr>
		</thead>
		';


		// Show page listing
		if ($context['downloads_total'] > 0)
		{
			echo '<tr  class="windowbg2">
					<td align="left" colspan="' . $count . '">
					';



					echo $context['page_index'];

			echo '
					</td>
				</tr>';
		}


	foreach ($context['downloads_files'] as $i => $file)
	{

			echo '<tr class="windowbg2">';

			if (!empty($modSettings['tema_set_t_title']))
			{
				echo  '<td><a href="' . $scripturl . '?action=tema;sa=view;down=', $file['ID_FILE'], '">', $file['title'], '</a><br />';
				if ($file['approved'] == 1)
					echo '<b>', $txt['tema_myfiles_app'], '</b>';
				else
					echo '<b>', $txt['tema_myfiles_notapp'], '</b>';
				echo '</td>';
			}

			if (!empty($modSettings['tema_set_t_rating']))
				echo '<td>', Downloads_GetStarsByPrecent(($file['totalratings'] != 0) ? ($file['rating'] / ($file['totalratings']* 5) * 100) : 0), '</td>';

			if (!empty($modSettings['tema_set_t_views']))
				echo '<td>', $file['views'], '</td>';

			if (!empty($modSettings['tema_set_t_downloads']))
				echo '<td>', $file['totaldownloads'], '</td>';



			if (!empty($modSettings['tema_set_t_filesize']))
				echo '<td>', Downloads_format_size($file['filesize'], 2) . '</td>';

			if (!empty($modSettings['tema_set_t_date']))
				echo '<td>', timeformat($file['date']), '</td>';

			if (!empty($modSettings['tema_set_t_username']))
			{
				if ($file['real_name'] != '')
					echo '<td><a href="' . $scripturl . '?action=profile;u=' . $file['id_member'] . '">'  . $file['real_name'] . '</a></td>';
				else
					echo '<td>', $txt['tema_guest'], '</td>';
			}


			// Options
			if ($g_manage ||  ($g_delete_own && $file['id_member'] == $user_info['id']) || ($g_edit_own && $file['id_member'] == $user_info['id']) )
			{
				echo '<td>';
				if ($g_manage)
					echo '<a href="' . $scripturl . '?action=tema;sa=unapprove&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_unapprove'] . '</a>';
				if ($g_manage || $g_edit_own && $file['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=edit&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_edit'] . '</a>';
				if ($g_manage || $g_delete_own && $file['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=delete&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_delete'] . '</a>';

				echo '</td>';
			}

			echo '</tr>';

	}

		// Show page listing
		if ($context['downloads_total'] > 0)
		{
			echo '<tr class="titlebg">
					<td align="left" colspan="' . $count . '">
					';

					echo $context['page_index'];

			echo '
					</td>
				</tr>';
		}


		echo '</table>';
        
        
		// Show return to downloads link and Show add downloads if they can
        	echo '
                    <div class="tborder">
            <div class="roundframe centertext">';
    	
				if ($g_add)
					echo '<a href="' . $scripturl . '?action=tema;sa=add">' . $txt['tema_text_adddownload'] . '</a> - ';

				echo '
				<a href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a>
            </div>
        </div>';

        


}

function template_view_rating()
{
	global  $context, $settings, $scripturl, $txt;


	echo '<table cellspacing="0" cellpadding="5" border="0" align="center" width="50%" class="tborder">
				<tr class="titlebg">
					<td align="center" colspan="3">' . $txt['tema_form_viewratings'] . '</td>
				</tr>
				<tr class="titlebg">
					<td align="center">' . $txt['tema_app_membername'] . '</td>
					<td align="center">' . $txt['tema_text_rating'] . '</td>
					<td align="center">' . $txt['tema_text_options'] . '</td>
				</tr>';

	foreach ($context['downloads_rating'] as $i => $rating)
	{
		echo '<tr class="windowbg2">
				<td align="center"><a href="' . $scripturl . '?action=profile;u=' . $rating['id_member'] . '">'  . $rating['real_name'] . '</a></td>
				<td align="center">';
		// Show the star images
		for($i=0; $i < $rating['value']; $i++)
			echo '<img src="', $settings['images_url'], '/membericons/icon.png" alt="*" border="0" />';

		echo '</td>
			  <td align="center"><a href="' . $scripturl . '?action=tema;sa=delrating&id=' . $rating['ID'] . '">'  . $txt['tema_text_delete'] . '</a></td>
		      </tr>';
	}
	echo '
	</table>';
    
        	echo '
                    <div class="tborder">
            <div class="roundframe centertext">';
    	
				echo '
				<a href="' . $scripturl . '?action=tema;sa=view;down=' . $context['downloads_id'] . '">' . $txt['tema_text_returnfile'] . '</a>
            </div>
        </div>';


}

function template_stats()
{
	global $settings, $context, $txt, $scripturl;


		ShowTopDownloadBar2();


echo '
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['tema_stats_title'], '
        </h3>
</div>
<table border="0" cellpadding="1" cellspacing="0" width="100%" align="center" class="table_grid">

			<tr class="catbg">
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr class="windowbg2">
				<td width="50%" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr class="windowbg2">
							<td  width="50%">', $txt['tema_stats_totalfiles'] ,  '</td>
							<td width="50%"  align="right">', comma_format($context['total_files']) , '</td>
						</tr>
						<tr class="windowbg2">
							<td width="50%">', $txt['tema_stats_totalviews'] ,  '</td>
							<td width="50%"  align="right">', comma_format($context['total_views']) , '</td>
						</tr>

					</table>
				</td>
				<td width="50%" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr class="windowbg2">
							<td width="50%">', $txt['tema_stats_totaldownloads'] , '</td>
							<td  idth="50%"  align="right">', comma_format($context['total_downloads']), '</td>
						</tr>
						<tr class="windowbg2">
							<td width="50%">', $txt['tema_stats_totalfize'] ,  '</td>
							<td width="50%" align="right">', $context['total_filesize'] , '</td>
						</tr>
					</table>
				</td>

			</tr>
			<tr class="windowbg">
				<td width="50%"><b>', $txt['tema_stats_viewed'], '</b></td>
				<td width="50%"><b>', $txt['tema_stats_toprated'], '</b></td>
			</tr>
			<tr class="windowbg2">
				<td width="50%" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';
						foreach ($context['top_viewed'] as $file)
						{
							echo '<tr class="windowbg2">
									<td width="60%" valign="top">', $file['link'], '</td>
									<td width="20%" align="left" valign="top">', $file['views'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $file['percent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
									<td width="20%" align="right" valign="top">', $file['views'], '</td>
								</tr>';
						}
	echo '
					</table>
				</td>
				<td width="50%" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';
						foreach ($context['top_rating'] as $file)
						{
							echo '<tr class="windowbg2">
									<td width="60%" valign="top">', $file['link'], '</td>
									<td width="20%" align="left" valign="top">', $file['rating'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $file['percent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
									<td width="20%" align="right" valign="top">', $file['rating'], '</td>
								</tr>';
						}
	echo '
					</table>
				</td>
			</tr>
			<tr class="windowbg">
				<td width="50%"><b>', $txt['tema_stats_topfile'], '</b></td>
				<td width="50%"><b>',$txt['tema_stats_last'], '</b></td>
			</tr>
			<tr class="windowbg2">
				<td width="50%" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';
						foreach ($context['totaldownloads'] as $file)
						{
							echo '<tr class="windowbg2">
									<td width="60%" valign="top">', $file['link'], '</td>
									<td width="20%" align="left" valign="top">', $file['totaldownloads'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $file['percent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
									<td width="20%" align="right" valign="top">', $file['totaldownloads'], '</td>
								</tr>';
						}

	echo '
					</table>
				</td>
				<td width="50%" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';
						foreach ($context['last_upload'] as $file)
						{
							echo '<tr class="windowbg2">
									<td width="100%" colspan="3" valign="top">', $file['link'], '</td>
								</tr>';
						}
	echo '
					</table>
				</td>
			</tr>
		</table>';
        
            	echo '
                    <div class="tborder">
            <div class="roundframe centertext">';
    	
				echo '
				<a href="' . $scripturl . '?action=tema">' . $txt['tema_text_returndownload'] . '</a>
            </div>
        </div>';
        
        
}


function template_filespace()
{
	global $scripturl, $txt, $context;
    

	echo '
			<div class="cat_bar">
								<h3 class="catbg">' . $txt['tema_filespace']. '</h3>
            </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg">
			<td>
            <b>' .$txt['tema_filespace_groupquota_title'] . '</b><br />
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">
                <thead>
			<tr class="title_bar">
				<th  class="lefttext first_th">' . $txt['tema_filespace_groupname'] . '</th>
				<th  class="lefttext">' .$txt['tema_filespace_limit']  . '</th>
				<th  class="lefttext last_th">' .  $txt['tema_text_options']  . '</th>
				</tr>
                </thead>';

		// Show the member groups
        $styleclass = 'windowbg';
			foreach ($context['downloads_membergroups'] as $i => $group)
			{

				echo '<tr class="' . $styleclass . '">';
				echo '<td>'  . $group['group_name'] . '</td>';
				echo '<td>' . Downloads_format_size($group['totalfilesize'], 2) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=deletequota&id=' . $group['ID_GROUP'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';

                if ($styleclass == 'windowbg')
    				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';

			}

			// Show Regular members
			foreach ($context['downloads_reggroup'] as $i => $group)
			{
				echo '<tr class="' . $styleclass . '">';
				echo '<td>', $txt['membergroups_members'], '</td>';
				echo '<td>' . Downloads_format_size($group['totalfilesize'], 2) . '</td>';
				echo '<td><a href="',$scripturl, '?action=tema;sa=deletequota&id=' . $group['ID_GROUP'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';

                if ($styleclass == 'windowbg')
    				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';

			}

		echo '
				<tr class="windowbg">
					<td colspan="3" align="center">
						<form method="post" action="' . $scripturl . '?action=tema;sa=addquota">
						' . $txt['tema_filespace_groupname']  . '&nbsp;<select name="groupname">
								<option value="0">', $txt['membergroups_members'], '</option>';
								foreach ($context['groups'] as $group)
									echo '<option value="', $group['ID_GROUP'], '">', $group['group_name'], '</option>';

							echo '</select><br />' . $txt['tema_filespace_limit'] . '&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="filelimit" /> (bytes)
							<br /><br />
						<input type="submit" value="' . $txt['tema_filespace_addquota'] . '" />
						</form>
					</td>
				</tr>

				</table>
			</td>
		</tr>
		<tr class="windowbg">
			<td>
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">
				<thead>
			<tr class="title_bar">
				<th  class="lefttext first_th">' . $txt['tema_app_membername'] . '</th>
				<th  class="lefttext">' . $txt['tema_text_options'] . '</th>
				<th  class="lefttext last_th">' . $txt['tema_filespace_filesize']  . '</th>
				</tr>
                </thead>';

                
	// List all members filespace usage
    $styleclass = 'windowbg';
	foreach ($context['downloads_members'] as $i => $member)
	{

		echo '<tr class="' . $styleclass . '">';
		echo '<td><a href="' . $scripturl . '?action=profile;u=' . $member['id_member'] . '">'  . $member['real_name'] . '</a></td>';
		echo '<td><a href="' . $scripturl . '?action=tema;sa=filelist&id=' . $member['id_member'] . '">'  . $txt['tema_filespace_list'] . '</a></td>';
		echo '<td>' . Downloads_format_size($member['totalfilesize'], 2) . '</td>';
		echo '</tr>';

        if ($styleclass == 'windowbg')
		  $styleclass = 'windowbg2';
		else
		  $styleclass = 'windowbg';

	}


			echo '<tr class="titlebg">
					<td align="left" colspan="3">
					';


					echo $context['page_index'];

			echo '
					</td>
				</tr>
			<tr class="titlebg">
					<td align="left" colspan="3">
					<form method="post" action="' . $scripturl . '?action=tema;sa=recountquota">
					<input type="submit" value="' . $txt['tema_filespace_recount'] . '" />
					</form>
					</td>
			</tr>
			</table>
			</td>
		</tr>
</table>';

}

function template_filelist()
{
	global $scripturl, $txt, $context, $modSettings;

	echo '
	<div class="cat_bar">
								<h3 class="catbg">' . $txt['tema_filespace_list_title'] . ' - ' . $context['downloads_filelist_real_name'] . '</h3>
            </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">

		<tr class="windowbg">
			<td>
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">
				<tr class="catbg">
				<td>' . $txt['tema_app_title'] . '</td>
				<td>' . $txt['tema_filespace_filesize']  . '</td>
				<td>' . $txt['tema_text_options'] . '</td>

				</tr>';

		// List all user's downloads
        $styleclass = 'windowbg';
		  	foreach ($context['downloads_files'] as $i => $file)
			{

				echo '<tr class="' . $styleclass . '">';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=view;down=' . $file['ID_FILE'] . '">', $file['title'],'</a></td>';
				echo '<td>' . Downloads_format_size($file['filesize'], 2) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=delete&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';

                if ($styleclass == 'windowbg')
    				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';

			}


		if ($context['downloads_total'] > 0)
		{
			echo '<tr class="titlebg">
					<td align="left" colspan="3">
					';



					echo $context['page_index'];

			echo '
					</td>
				</tr>';
		}

echo '<tr class="titlebg">
					<td align="center" colspan="3">
					<a href="' . $scripturl . '?action=admin;area=tema;sa=filespace">' . $txt['tema_filespace'] . '</a>
					</td>
		</tr>

			</table>
			</td>
		</tr>
</table>';
}

function template_catpermlist()
{
	global $scripturl, $txt, $context;

	echo '
			<div class="cat_bar">
								<h3 class="catbg">' . $txt['tema_text_catpermlist'] . '</h3>
            </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg">
			<td>
			<table border="0" cellspacing="1" cellpadding="4" class="table_grid"  align="center" width="100%">
<thead>
<tr class="title_bar">
				<th  class="lefttext first_th">' . $txt['tema_filespace_groupname'] . '</th>
				<th  class="lefttext">' . $txt['tema_text_category']  . '</th>
				<th  class="lefttext">' .  $txt['tema_perm_view']  . '</th>
				<th  class="lefttext">' .  $txt['tema_perm_add']  . '</th>
				<th  class="lefttext">' .  $txt['tema_perm_edit']  . '</th>
				<th  class="lefttext">' .  $txt['tema_perm_delete']  . '</th>
				<th  class="lefttext last_th">' .  $txt['tema_text_options']  . '</th>
				</tr>
				</thead>';

		// Show the member groups
			foreach ($context['downloads_membergroups'] as $i => $row)
			{

				echo '<tr class="windowbg2">';
				echo '<td>'  . $row['group_name'] . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catperm;cat=' . $row['ID_CAT'] . '">'  . $row['catname'] . '</a></td>';
				echo '<td>' . ($row['view'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['addfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['editfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['delfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';

			}

			// Show Regular members
			foreach ($context['downloads_regmem'] as $i => $row)
			{

				echo '<tr class="windowbg2">';
				echo '<td>'  . $txt['membergroups_members'] . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catperm;cat=' . $row['ID_CAT'] . '">'  . $row['catname'] . '</a></td>';
				echo '<td>' . ($row['view'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['addfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['editfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['delfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';
			}

			// Show Guests
			foreach ($context['downloads_guestmem'] as $i => $row)
			{

				echo '<tr class="windowbg2">';
				echo '<td>'  . $txt['membergroups_guests'] . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catperm;cat=' . $row['ID_CAT'] . '">'  . $row['catname'] . '</a></td>';
				echo '<td>' . ($row['view'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['addfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['editfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['delfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';
			}


		echo '


				</table>
			</td>
		</tr>

</table>';
}

function template_catperm()
{
	global $scripturl, $txt, $context;

	echo '

    <div class="cat_bar">
		<h3 class="catbg centertext">
        ' .$txt['tema_text_catperm'] . ' - ' . $context['downloads_cat_name']  . '
        </h3>
</div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">

		<tr class="windowbg">
		<td>
		<form method="post" action="' . $scripturl . '?action=tema;sa=catperm2">
		<table align="center" class="tborder">
		<tr class="titlebg">
			<td colspan="2">'  . $txt['tema_text_addperm'] . '</td>
		</tr>

			  <tr class="windowbg2">
			  	<td align="right"><b>' . $txt['tema_filespace_groupname'] . '</b>&nbsp;</td>
			  	<td><select name="groupname">
			  					<option value="-1">' . $txt['membergroups_guests'] . '</option>
								<option value="0">' . $txt['membergroups_members'] . '</option>';
								foreach ($context['groups'] as $group)
									echo '<option value="', $group['ID_GROUP'], '">', $group['group_name'], '</option>';

							echo '</select>
				</td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="view" checked="checked" /></td>
			  	<td><b>' . $txt['tema_perm_view'] .'</b></td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="viewdownload" checked="checked" /></td>
			  	<td><b>'.$txt['tema_perm_viewdownload'].'</b></td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="add" checked="checked" /></td>
			  	<td><b>' . $txt['tema_perm_add'] .'</b></td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="edit" checked="checked" /></td>
			  	<td><b>' . $txt['tema_perm_edit'] .'</b></td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="delete" checked="checked" /></td>
			  	<td><b>' . $txt['tema_perm_delete'] .'</b></td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="center" colspan="2">
			  	<input type="hidden" name="cat" value="' . $context['downloads_cat'] . '" />
			  	<input type="submit" value="' . $txt['tema_text_addperm'] . '" /></td>

			  </tr>
		</table>
		</form>
		</td>
		</tr>
			<tr class="windowbg">
			<td>
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
			<tr class="catbg">
				<td>' . $txt['tema_filespace_groupname'] . '</td>
				<td>' .  $txt['tema_perm_view']  . '</td>
				<td>' .  $txt['tema_perm_viewdownload']  . '</td>
				<td>' .  $txt['tema_perm_add']  . '</td>
				<td>' .  $txt['tema_perm_edit']  . '</td>
				<td>' .  $txt['tema_perm_delete']  . '</td>
				<td>' .  $txt['tema_perm_addcomment']  . '</td>
				<td>' .  $txt['tema_text_options']  . '</td>
				</tr>';

		// Show the member groups
			foreach ($context['downloads_membergroups'] as $i => $row)
			{

				echo '<tr class="windowbg2">';
				echo '<td>', $row['group_name'], '</td>';
				echo '<td>' . ($row['view'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['viewdownload'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['addfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['editfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['delfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';

			}

			// Show Regular members
			foreach ($context['downloads_reggroup'] as $i => $row)
			{

				echo '<tr class="windowbg2">';
				echo '<td>', $txt['membergroups_members'], '</td>';
				echo '<td>' . ($row['view'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['viewdownload'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['addfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['editfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['delfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';
			}

			// Show Guests
			foreach ($context['downloads_guestgroup'] as $i => $row)
			{

				echo '<tr class="windowbg2">';
				echo '<td>', $txt['membergroups_guests'], '</td>';
				echo '<td>' . ($row['view'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['viewdownload'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['addfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['editfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['delfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';
			}


		echo '


				</table>
			</td>
		</tr>
</table>';
}




function template_import_results()
{
	global $txt, $context;

	echo '
    <div class="cat_bar">
		<h3 class="catbg">
        ', $txt['tema_txt_import_downloads'], '
        </h3>
	</div>

    <table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">


		<tr class="windowbg">
			<td>
				',$txt['tema_txt_categories_imported'],' ',$context['tp_imported_categories'],'<br />
				',$txt['tema_txt_files_imported'], ' ',$context['tp_imported_files'],'<br />

			</td>
		</tr>

</table>';


}

function template_import()
{
	global $txt, $scripturl;

echo '
<div class="cat_bar">
		<h3 class="catbg">
        ', $txt['tema_txt_import_downloads'], '
        </h3>
</div>
<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">

		<tr class="windowbg">
			<td>
				',$txt['tema_txt_import_note'],'<br />
			<form method="post" action="',$scripturl,'?action=tema;sa=importtp">
				<input type="submit" value="',$txt['tema_txt_import_tiny_portal'],'" />
			</form>

			</td>
		</tr>

</table>';

}


function ShowTopDownloadBar2($title = '&nbsp;')
{
	global $txt, $context;
		echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $title, '
        </h3>
</div>';
    

    echo '<div style="height:10px;">';
    echo template_button_strip($context['downloads']['buttons'], 'right');
    echo '</div>';
        
    echo '<br /><br />';

}

?>