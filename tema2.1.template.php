<?php
/*
Theme System
Version 1
by:ceemoo
http://www.smf.konusal.com
*/
function template_mainview()
{
	global $scripturl, $txt, $context, $modSettings, $subcats_linktree, $user_info;
	if (allowedTo('themes_manage'))
	{
		if (!is_writable($modSettings['tema_path']))
			echo '<div class="noticebox"><span class="alert">!</span> ', $txt['tema_write_error'], $modSettings['tema_path'], '</div>';
	}

	@$cat = (int) $_REQUEST['cat'];

	if (!empty($cat))
	{
		Downloads_ShowSubCats($cat,allowedTo('themes_manage'));
		if(!empty($context['subthemecat']))
		{
		
			echo '
			<div id="messageindex">';
			echo '<div class="title_bar" id="topic_header">
						<div class="board_icon">&nbsp;</div>
						<div class="info">' . $txt['tema_text_categoryname'] . '</div>
						<div class="board_stats centertext">' . $txt['tema_text_totalfiles'] . '</div>
							';
					if	(allowedTo('themes_manage'))
						echo '
						<div class="lastpost">' . $txt['tema_text_reorder'] . '/' . $txt['tema_text_options'] . '</div>';
				echo '
				</div>';

			echo '
				<div id="topic_container">';
			foreach($context['subthemecat'] as $subcat)
			{
				echo '<div class="windowbg">';
				if ($subcat['image'] == '' && $subcat['filename'] == '')
						echo '<div class="board_icon"><a href="' . $scripturl . '?action=tema;cat=' . $subcat['ID_CAT'] . '"></a></div>
						<div class="info">
						<a class="subject" href="' . $scripturl . '?action=tema;cat=' . $subcat['ID_CAT'] . '">' . parse_bbc($subcat['title']) . '</a>
						<p class="board_description">' . parse_bbc($subcat['description']) . '</p>
						',($subcat['subcats_linktree'] != '' ? '<p><strong>' . $txt['tema_sub_cats'] . '</strong>: ' . $subcat['subcats_linktree'].'</p>' : ''),'
						</div>';
					else
					{
						if ($subcat['filename'] == '')
							echo '<div class="iconla">
									<a href="' . $scripturl . '?action=tema;cat=' . $subcat['ID_CAT'] . '"><img src="' . $subcat['image'] . '" /></a>
									</div>';
						else
							echo '<div class="iconla">
									<a href="' . $scripturl . '?action=tema;cat=' . $subcat['ID_CAT'] . '"><img src="' . $modSettings['tema_url'] . 'catimgs/' . $subcat['filename'] . '" /></a>
									</div>';

						echo '<div class="info">
								<a class="subject" href="' . $scripturl . '?action=tema;cat=' . $subcat['ID_CAT'] . '">' . parse_bbc($subcat['title']) . '</a>
								<p class="board_description">' . parse_bbc($subcat['description']) . '</p>
								',($subcat['subcats_linktree'] != '' ? '<p><strong>' . $txt['tema_sub_cats'] . '</strong>: ' . $subcat['subcats_linktree'].'</p>' : ''),'
							</div>';
					}
					echo '<div class="board_stats centertext">' . $subcat['totalfiles'] . '</div>';
					if (allowedTo('themes_manage'))
					{
						echo '
						<div class="lastpost">
							<div class="buttonlist floatright">
								<a class="button" href="' . $scripturl . '?action=tema;sa=catup;cat=' . $subcat['ID_CAT'] . '">' . $txt['tema_text_up'] . '</a>
								<a class="button" href="' . $scripturl . '?action=tema;sa=catdown;cat=' . $subcat['ID_CAT'] . '">' . $txt['tema_text_down'] . '</a>
								<a class="button" href="' . $scripturl . '?action=tema;sa=editcat;cat=' . $subcat['ID_CAT'] . '">' . $txt['tema_text_edit'] . '</a>
								<a class="button" href="' . $scripturl . '?action=tema;sa=deletecat;cat=' . $subcat['ID_CAT'] . '">' . $txt['tema_text_delete'] . '</a>
								<a class="button" href="' . $scripturl . '?action=tema;sa=catperm;cat=' . $subcat['ID_CAT'] . '">' . $txt['tema_text_permissions'] . '</a>
							</div>
						</div>';
					}
				echo '</div>';
			}
				echo '</div>
				</div>';
		}
		
		
		if (!isset($context['downloads_cat_norate']))
			$context['downloads_cat_norate'] = 0;

		if (!empty($modSettings['tema_who_viewing']))
		{
			echo '<div id="description_board" class="generic_list_wrapper">';
			echo empty($context['view_members_list']) ? '0 ' . $txt['tema_who_members'] : implode(', ', $context['view_members_list']) . (empty($context['view_num_hidden']) || $context['can_moderate_forum'] ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['tema_who_hidden'] . ')');

			echo $txt['who_and'], @$context['view_num_guests'], ' ', @$context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['tema_who_viewdownload'], '</span>';
			echo '</div>';
		}
		echo '<table class="table_grid">
				<thead>
				<tr class="title_bar">';
					if ($context['downloads_orderby2'] == 'asc')
						$neworder = 'desc';
					else
						$neworder = 'asc';
					if (!empty($modSettings['tema_set_t_title']))
					{
						echo  '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . ';sortby=title;orderby=' . $neworder . '">',$txt['tema_cat_title'], '</a></th>';
					}

					if (!empty($modSettings['tema_set_t_rating']) && $context['downloads_cat_norate'] != 1)
					{
						echo '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . ';sortby=mostrated;orderby=' . $neworder . '">', $txt['tema_cat_rating'], '</a></th>';
					}

					if (!empty($modSettings['tema_set_t_views']))
					{
						echo '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . ';sortby=mostview;orderby=' . $neworder . '">', $txt['tema_cat_views'], '</a></th>';
					}


					if (!empty($modSettings['tema_set_t_downloads']))
					{
						echo '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . ';sortby=mostdowns;orderby=' . $neworder . '">', $txt['tema_cat_downloads'] , '</a></th>';
					}

					if (!empty($modSettings['tema_set_t_filesize']))
					{
						echo '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . 'sortby=filesize;orderby=' . $neworder . '">',$txt['tema_cat_filesize'], '</a></th>';
					}

					if (!empty($modSettings['tema_set_t_date']))
					{
						echo '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . ';sortby=date;orderby=' . $neworder . '">',$txt['tema_cat_date'], '</a></th>';
					}

					if (!empty($modSettings['tema_set_t_username']))
					{
						echo '<th  class="lefttext"><a href="' . $scripturl . '?action=tema;cat=' . $cat . ';start=' . $context['start'] . ';sortby=membername;orderby=' . $neworder . '">',$txt['tema_cat_membername'],'</a></th>';
					}

					if (allowedTo('themes_manage') ||  (allowedTo('themes_delete')) || (allowedTo('themes_edit')) )
					{
						echo '<th  class="lefttext">',$txt['tema_cat_options'],'</th>';
					}

				echo '</tr>
				</thead>
				<tbody>';
		foreach ($context['downloads_files'] as $i => $file)
		{

			echo '<tr  class="windowbg">';

			if (!empty($modSettings['tema_set_t_title']))
				echo  '<td><a href="' . $scripturl . '?action=tema;sa=view;down=', $file['ID_FILE'], '">', $file['title'], '</a></td>';


			if (!empty($modSettings['tema_set_t_rating']) && $context['downloads_cat_norate'] != 1)
				echo '<td>';
					if ($file['totalratings'] == 0)
					{
						echo $txt['tema_text_catnone'];
					}
					else
					{
						$stars = 5;
						$derece =($file['rating'] / ($file['totalratings']* $stars) * 100);
						if ($derece == 0)
							echo $txt['tema_form_rating'];
						else if ($derece <= 20)
							echo str_repeat('<span class="generic_star"></span>', 1);
						else if ($derece <= 40)
							echo str_repeat('<span class="generic_star"></span>', 2);
						else if ($derece <= 60)
							echo str_repeat('<span class="generic_star"></span>', 3);
						else if ($derece <= 80)
							echo str_repeat('<span class="generic_star"></span>', 4);
						else if ($derece <= 100)
							echo str_repeat('<span class="generic_star"></span>', 5);
					}
				echo '</td>';

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
			if (allowedTo('themes_manage') ||  (allowedTo('themes_delete') && $file['id_member'] == $user_info['id']) || (allowedTo('themes_edit') && $file['id_member'] == $user_info['id']) )
			{
				echo '<td>';
				if (allowedTo('themes_manage'))
					echo '<a href="' . $scripturl . '?action=tema;sa=unapprove&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_unapprove'] . '</a>';
				if (allowedTo('themes_manage') || allowedTo('themes_edit') && $file['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=edit&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_edit'] . '</a>';
				if (allowedTo('themes_manage') || allowedTo('themes_delete') && $file['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=delete&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_delete'] . '</a>';

				echo '</td>';
			}
			echo '</tr>';

		}
		echo '</tbody>
			</table>';
			
            	echo '<div class="pagesection">
						<div class="buttonlist floatright">';
						
							if (allowedTo('themes_manage'))
								echo '<a class="button" href="', $scripturl, '?action=tema;sa=addcat;cat=', $cat, '">', $txt['tema_text_addsubcat'], '</a>&nbsp;&nbsp;';

							if (allowedTo('themes_add'))
								echo '<a class="button" href="', $scripturl, '?action=tema;sa=add;cat=', $cat, '">', $txt['tema_text_adddownload'], '</a> ';
						echo '
								<a class="button" href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a>
								<a class="button" href="', $scripturl, '?action=tema;sa=search">', $txt['tema_text_search2'], '</a>';	
								if (allowedTo('themes_add') && !($user_info['is_guest']))
								echo '<a class="button" href="', $scripturl , '?action=tema;sa=myfiles;u=' , $user_info['id'],'">', $txt['tema_text_myfiles2'], '</a>';
							echo '
						</div>
						<div class="pagelinks floatleft">'.$context['page_index'].'</div>
					</div>';

	}
	else
	{
		echo '
			<div class="cat_bar">
				<h3 class="catbg centertext">
					', $txt['tema_text_title'], '
				</h3>
			</div>';

		echo '
		<div id="messageindex">';
		echo '<div class="title_bar" id="topic_header">
					<div class="board_icon">&nbsp;</div>
					<div class="info">', $txt['tema_text_categoryname'], '</div>
					<div class="board_stats centertext">', $txt['tema_text_totalfiles'], '</div>
						';
				if	(allowedTo('themes_manage'))
					echo '
					<div class="lastpost">', $txt['tema_text_reorder'], '/', $txt['tema_text_options'], '</div>';
			echo '
			</div>';

		echo '
			<div id="topic_container">';
		foreach ($context['downloads_cats'] as $i => $cat_info)
		{
			$cat_url = '';
			if ($cat_info['view'] == '0')
				continue;
			$totalfiles  = Downloads_GetFileTotals($cat_info['ID_CAT']);
			$cat_url = $scripturl . '?action=tema;cat=' . $cat_info['ID_CAT'];
			echo '<div class="windowbg">';

				if ($cat_info['image'] == '' && $cat_info['filename'] == '')
					echo '<div class="board_icon"><a href="' . $cat_url . '"></a></div>
							<div class="info">
								<a class="subject" href="' . $cat_url . '">' . parse_bbc($cat_info['title']) . '</a>
								<p class="board_description">' . parse_bbc($cat_info['description']) . '</p>
								',($subcats_linktree != '' ? '<p><strong>' . $txt['tema_sub_cats'] . '</strong>: ' . $subcats_linktree.'</p>' : ''),'
							</div>';
				else
				{
					if ($cat_info['filename'] == '')
						echo '<div class="iconla"><a href="' . $cat_url . '"><img src="' . $cat_info['image'] . '" /></a></div>';
					else
						echo '<div class="iconla"><a href="' . $cat_url . '"><img src="' . $modSettings['tema_url'] . 'catimgs/' . $cat_info['filename'] . '" /></a></div>';
					echo '
						<div class="info">
							<a class="subject" href="' . $cat_url . '">' . parse_bbc($cat_info['title']) . '</a>
							<p class="board_description">' . parse_bbc($cat_info['description']) . '</p>
							',($subcats_linktree != '' ? '<p><strong>' . $txt['tema_sub_cats'] . '</strong>: ' . $subcats_linktree.'</p>' : ''),'
						</div>';
				}

			echo '<div class="board_stats centertext">', $totalfiles, '</div>';
			if (allowedTo('themes_manage'))
			{
				echo '<div class="lastpost">
					<div class="buttonlist floatright">
						<a class="button" href="' . $scripturl . '?action=tema;sa=catup;cat=' . $cat_info['ID_CAT'] . '">' . $txt['tema_text_up'] . '</a>
						<a class="button" href="' . $scripturl . '?action=tema;sa=catdown;cat=' . $cat_info['ID_CAT'] . '">' . $txt['tema_text_down'] . '</a>
						<a class="button" href="' . $scripturl . '?action=tema;sa=editcat;cat=' . $cat_info['ID_CAT'] . '">' . $txt['tema_text_edit'] . '</a>
						<a class="button" href="' . $scripturl . '?action=tema;sa=deletecat;cat=' . $cat_info['ID_CAT'] . '">' . $txt['tema_text_delete'] . '</a>
						<a class="button" href="' . $scripturl . '?action=tema;sa=catperm;cat=' . $cat_info['ID_CAT'] . '">' . $txt['tema_text_permissions'] . '</a>
					</div>
				</div>';
			}
			echo '</div>';
		}
		echo '</div>
		</div>';
			if (!empty($modSettings['tema_index_recent'])){
				MainPageBlock($txt['tema_main_recent'], 'recent');
				Enlist();
			}
			if (!empty($modSettings['tema_index_toprated'])){
				MainPageBlock($txt['tema_main_toprated'], 'toprated');
				Enlist();
			}
			if (!empty($modSettings['tema_index_mostviewed'])){
				MainPageBlock($txt['tema_main_viewed'], 'viewed');
				Enlist();
			}	
			if (!empty($modSettings['tema_index_mostdownloaded'])){
				MainPageBlock($txt['tema_main_mostdownloads'], 'mostdownloaded');
				Enlist();
			}	
			
		if (allowedTo('themes_manage'))
		{
			echo '
            <div class="cat_bar">
				<h3 class="catbg centertext">
				', $txt['tema_text_adminpanel'], '
				</h3>
			</div>
			<div class="information centertext">
				<a class="button" href="' . $scripturl . '?action=tema;sa=addcat">' . $txt['tema_text_addcategory'] . '</a>
				<a class="button" href="' . $scripturl . '?action=admin;area=tema;sa=adminset">' . $txt['tema_text_settings'] . '</a>&nbsp;';
				if (allowedTo('manage_permissions'))
				echo '<a class="button" href="', $scripturl, '?action=admin;area=permissions">', $txt['tema_text_permissions'], '</a>';
			echo '<br />' . $txt['tema_text_fileswaitapproval'] . '<strong>',$context['downloads_waitapproval'],'</strong>&nbsp;&nbsp;<a href="' . $scripturl . '?action=admin;area=tema;sa=approvelist">' . $txt['tema_text_filecheckapproval'] . '</a>';
			echo '<br />' . $txt['tema_text_filereported'] . '<strong>',$context['downloads_totalreport'],'</strong>&nbsp;&nbsp;<a href="' . $scripturl . '?action=admin;area=tema;sa=reportlist">' . $txt['tema_text_filecheckreported'] . '</a>';
			echo '</div>';
		}
			echo '
		<div class="pagesection">
			<div class="buttonlist floatright">
				<a class="button" href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a>
				<a class="button" href="' . $scripturl . '?action=tema;sa=stats">', $txt['tema_stats_viewstats'] ,'</a>
				<a class="button" href="', $scripturl, '?action=tema;sa=search">', $txt['tema_text_search2'], '</a>';	
				if (allowedTo('themes_add') && !($user_info['is_guest']))
				echo '<a class="button" href="', $scripturl , '?action=tema;sa=myfiles;u=' , $user_info['id'],'">', $txt['tema_text_myfiles2'], '</a>';
			echo'</div>
		</div>';
	}

}

function template_add_category()
{
	global $scripturl, $txt, $context, $settings, $modSettings;
	if ($context['show_spellchecking'])
		echo '<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';
	echo '
		<div class="cat_bar">
			<h3 class="catbg centertext">
			', $txt['tema_text_addcategory'], '
			</h3>
		</div>
		<form method="post" enctype="multipart/form-data" name="catform" id="catform" action="' . $scripturl . '?action=tema;sa=addcat2" onsubmit="submitonce(this);">
		<div id="post_area">
				<div class="roundframe noup">
		<dl id="post_header">
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_form_title'] .'</span>
			</dt>
			<dd class="pf_subject">
				<input type="text" name="title" size="80" maxlength="80" class="input_text"/>
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_text_parentcategory'] .'</span>
			</dt>
			<dd class="pf_subject">
				<select name="parent">
					<option value="0">',$txt['tema_text_catnone'],'</option>
					';

					foreach ($context['downloads_cat'] as $i => $category)
						echo '<option value="' . $category['ID_CAT']  . '" ' . (($context['cat_parent'] == $category['ID_CAT']) ? ' selected="selected"' : '') .'>' . $category['title'] . '</option>';

			echo '</select>
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_form_description'] . '&nbsp;</span><br />'. $txt['tema_text_bbcsupport'] .'
			</dt>
			<dd class="pf_subject">
				', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'),'
			</dd>';
		  
		if ($context['show_spellchecking'])
		echo '<br /><input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'catform\', \'description\');" />';
		
		echo '<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_form_icon'] . '</span>
			</dt>
			<dd class="pf_subject">	
				<input type="text" name="image" size="64" maxlength="300" />
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_form_uploadicon'] . '</span>';


		if (!is_writable($modSettings['tema_path'] . 'catimgs'))
			echo '<font color="#FF0000"><b>' . $txt['tema_write_catpatherror']  . $modSettings['tema_path'] . 'catimgs' . '</b></font>';


		echo '</dt>
			<dd class="pf_subject">	
				<input type="file" size="48" name="picture" />
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' .   $txt['tema_text_cat_disableratings'] . '</span>
			</dt>
			<dd class="pf_subject">	
				<input type="checkbox" name="disablerating" />
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' .   $txt['tema_txt_sortby']  . '</span>
			</dt>
			<dd class="pf_subject">		
				<select name="sortby">
					<option value="date">',$txt['tema_txt_sort_date'],'</option>
					<option value="title">',$txt['tema_txt_sort_title'],'</option>
					<option value="mostview">',$txt['tema_txt_sort_mostviewed'],'</option>
					<option value="mostrated">',$txt['tema_txt_sort_mostrated'],'</option>
					<option value="mostdowns">',$txt['tema_txt_sort_mostdowns'],'</option>
					<option value="filesize">',$txt['tema_txt_sort_filesize'],'</option>
					<option value="membername">',$txt['tema_txt_sort_membername'],'</option>
				</select>
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' .   $txt['tema_txt_orderby'] . '</span>
			</dt>
			<dd class="pf_subject">	
				<select name="orderby">
					<option value="desc">',$txt['tema_txt_sort_desc'],'</option>
					<option value="asc">',$txt['tema_txt_sort_asc'],'</option>
				</select>
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_text_postingoptions'] . '</span>
			</dt>
			<dd class="pf_subject">
				' . $txt['tema_postingoptions_info'] . '
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_text_boardname'] . '</span>
			</dt>
			<dd class="pf_subject">	
				<select name="boardselect" id="boardselect">';

				foreach ($context['downloads_boards'] as $key => $option)
					 echo '<option value="' . $key . '">' . $option . '</option>';

			echo '</select>
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_posting_locktopic'] . '</span>
			</dt>
			<dd class="pf_subject">	
				<input type="checkbox" name="locktopic" />
			</dd>
		</dl>
		<div class="centertext"><input type="submit" value="', $txt['tema_text_addcategory'], '" name="submit" /></div>
			</div>
		</div>
		</form>';
			if ($context['show_spellchecking'])
					echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';
}

function template_edit_category()
{
	global $scripturl, $txt, $context, $settings, $context, $modSettings;
	if ($context['show_spellchecking'])
		echo '<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';

		echo '
			<div class="cat_bar">
				<h3 class="catbg centertext">
				', $txt['tema_text_editcategory'], '
				</h3>
			</div>
			<form method="post" enctype="multipart/form-data" name="catform" id="catform" action="', $scripturl, '?action=tema;sa=editcat2" onsubmit="submitonce(this);">
			<div id="post_area">
				<div class="roundframe noup">
			<dl id="post_header">
			<dt class="clear pf_subject">
				<span id="caption_subject">', $txt['tema_form_title'], '</span>
			</dt>
			<dd class="pf_subject">	
					<input type="text" name="title" size="64" maxlength="100" value="', $context['tema_catinfo']['title'], '" />
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">', $txt['tema_text_parentcategory'], '</span>
			</dt>
			<dd class="pf_subject">
				<select name="parent">
				<option value="0">', $txt['tema_text_catnone'], '</option>
				';

					foreach ($context['downloads_cat'] as $i => $category)
					{
						echo '<option value="' . $category['ID_CAT']  . '" ' . (($context['tema_catinfo']['ID_PARENT'] == $category['ID_CAT']) ? ' selected="selected"' : '') .'>' . $category['title'] . '</option>';
					}

				echo '</select>
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_form_description'] . '</span><br />' . $txt['tema_text_bbcsupport'] . '
			</dt>
			<dd class="pf_subject">	';
				template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

				if ($context['show_spellchecking'])
					echo '<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'catform\', \'description\');" />';
		echo'</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_form_icon'] . '</span>
			</dt>
			<dd class="pf_subject">
				<input type="text" name="image" size="64" maxlength="100" value="' . $context['tema_catinfo']['image'] . '" /></td>
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_form_uploadicon'] . '</span>
			</dt>
			<dd class="pf_subject">';
			if (!is_writable($modSettings['tema_path'] . 'catimgs'))
				echo '<div class="noticebox"><span class="alert">!</span> ' . $txt['tema_write_catpatherror']  . $modSettings['tema_path'] . 'catimgs' . '</div>';
			echo '<input type="file" size="48" name="picture" />
			</dd>';

			if ($context['tema_catinfo']['filename'] != '')
			echo '
			 <dt class="clear pf_subject">
				<span id="caption_subject">' .   $txt['tema_form_filenameicon'] . '</span>
			</dt>
			<dd class="pf_subject">
				' . $context['tema_catinfo']['filename'] .  '&nbsp;<a href="' . $scripturl . '?action=tema;sa=catimgdel&id=' . $context['tema_catinfo']['ID_CAT'] . '">' . $txt['tema_rep_deletefile'] . '</a>
			</dd>';
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
	echo '  <dt class="clear pf_subject">
				<span id="caption_subject">' .   $txt['tema_text_cat_disableratings'] . '</span>
			</dt>
			<dd class="pf_subject">
				<input type="checkbox" name="disablerating" ' . ($context['tema_catinfo']['disablerating'] ? ' checked="checked"' : '') . ' />
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' .   $txt['tema_txt_sortby']  . '</span>
			</dt>
			<dd class="pf_subject">
				<select name="sortby">
					',$sortselect,'
					<option value="date">',$txt['tema_txt_sort_date'],'</option>
					<option value="title">',$txt['tema_txt_sort_title'],'</option>
					<option value="mostview">',$txt['tema_txt_sort_mostviewed'],'</option>
					<option value="mostrated">',$txt['tema_txt_sort_mostrated'],'</option>
					<option value="mostdowns">',$txt['tema_cat_downloads'],'</option>
					<option value="filesize">',$txt['tema_cat_filesize'],'</option>
					<option value="membername">',$txt['tema_cat_membername'],'</option>
				</select>
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' .   $txt['tema_txt_orderby'] . '</span>
			</dt>
			<dd class="pf_subject">
				<select name="orderby">
					',$orderselect,'
					<option value="desc">',$txt['tema_txt_sort_desc'],'</option>
					<option value="asc">',$txt['tema_txt_sort_asc'],'</option>
				</select>
			</dd>
			<p class="centertext"><strong>' . $txt['tema_text_postingoptions'] . '</strong></p>
			<hr />
			<p class="centertext">' . $txt['tema_postingoptions_info'] . '</p>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_text_boardname'] . '</span>
			</dt>
			<dd class="pf_subject">
				<select name="boardselect" id="boardselect">';
				foreach ($context['downloads_boards'] as $key => $option)
				echo '<option value="' . $key . '"' . (($context['tema_catinfo']['ID_BOARD']==$key) ? ' selected="selected"' : '') . '>' . $option . '</option>';

			echo '</select>
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_posting_locktopic'] . '</span>
			</dt>
			<dd class="pf_subject">
				 <input type="checkbox" name="locktopic" ' . ($context['tema_catinfo']['locktopic'] ? ' checked="checked"' : '') . ' />
			</dd>
		</dl>
			<hr />
			<div class="centertext">
			<input type="hidden" value="' . $context['tema_catinfo']['ID_CAT'] . '" name="catid" />
			<input type="submit" value="' . $txt['tema_text_editcategory'] . '" name="submit" />
			</div>
			</div>
		</div>
		</form>';
		echo'
		<hr />
		<div class="centertext">
			<p class="centertext"><strong>',  $txt['tema_custom_fields'],'</strong></p>
			<form method="post" action="', $scripturl, '?action=tema;sa=cusadd">
			', $txt['tema_custom_title'], '<input type="text" name="title" />
			', $txt['tema_custom_default_value'], '<input type="text" name="defaultvalue" />
			<input type="hidden" name="id" value="',$context['tema_catinfo']['ID_CAT'],'" />
			<input type="checkbox" name="required" />', $txt['tema_custom_required'], '
			<input type="submit" name="addfield" value="',$txt['tema_custom_addfield'],'" />
			</form>
		</div><br />

		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th>', $txt['tema_custom_title'], '</th>
					<th>', $txt['tema_custom_default_value'], '</th>
					<th>', $txt['tema_custom_required'], '</th>
					<th>', $txt['tema_text_options'], '</th>
				</tr>
			</thead>	
			<tbody>';
				foreach ($context['tema_custom'] as $i => $custom)
				{
					echo '
					<tr  class="windowbg">
						<td>', $custom['title'], '</td>
						<td>', $custom['defaultvalue'], '</td>
						<td>', ($custom['is_required'] ? 'TRUE' : 'FALSE'), '</td>
						<td><a href="' . $scripturl . '?action=tema;sa=cusup&id=' . $custom['ID_CUSTOM'] . '">' . $txt['tema_text_up'] . '</a>&nbsp;<a href="' . $scripturl . '?action=tema;sa=cusdown&id=' . $custom['ID_CUSTOM'] . '">' . $txt['tema_text_down'] . '</a>
						&nbsp;&nbsp;<a href="' . $scripturl . '?action=tema;sa=cusdelete&id=' . $custom['ID_CUSTOM'] . '">' . $txt['tema_text_delete'] . '</a>
						</td>
					</tr>';
				}
		echo '</tbody>
		</table>
		<div align="center">
		<a class="button" href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a>
		</div>';
	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';
}

function template_delete_category()
{
	global $context, $scripturl, $txt;
	echo '
		<form method="post" action="' . $scripturl . '?action=tema&sa=deletecat2">
			<div class="cat_bar">
				<h3 class="catbg centertext">
				' . $txt['tema_text_delcategory'] . '
				</h3>
			</div>
			<div class="information centertext">
			<div class="noticebox lefttext"><span class="alert">!</span> ' . $txt['tema_warn_category'] . ' </div>
				<p>' . $txt['tema_text_categoryname'] . ' : ' . $context['cat_title'] . '</p>
				<p>' . $txt['tema_text_totalfiles'] . ' : ' . $context['totalfiles'] . '</p>
				 <br />
				<input type="hidden" value="' . $context['catid'] . '" name="catid" />
				<input type="submit" value="' . $txt['tema_text_delcategory'] . '" name="submit" />
			</div>
		</form>';
}

function template_add_download()
{
	global $scripturl, $modSettings, $txt, $context, $settings;
	$cat = $context['tema_cat'];
	if ($context['show_spellchecking'])
		echo '<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';
	echo '
    <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['tema_form_adddownload'], '
        </h3>
	</div>
	<form method="post" enctype="multipart/form-data" name="picform" id="picform" action="' . $scripturl . '?action=tema&sa=add2" onsubmit="submitonce(this);">
			<div id="post_area">
				<div class="roundframe noup">
			<dl id="post_header">
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_form_title'] . '</span>
			</dt>
			<dd class="pf_subject">	
					<input type="text" name="title" size="50" />
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_form_category'] . '</span>
			</dt>
			<dd class="pf_subject">
				<select name="cat">';

				foreach ($context['downloads_cat'] as $i => $category)
				{
					echo '<option value="' . $category['ID_CAT']  . '" ' . (($cat == $category['ID_CAT']) ? ' selected="selected"' : '') .'>' . $category['title'] . '</option>';
				}

			 echo '</select>
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_form_description'] . '</span>
			</dt>
			<dd class="pf_subject">';
				template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
				if ($context['show_spellchecking'])
					echo '<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'picform\', \'description\');" />';
			echo '
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">', $txt['tema_form_keywords'], '</span>
			</dt>
			<dd class="pf_subject">
				<input type="text" name="keywords" maxlength="100" size="50" />
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">', $txt['tema_form_demourl'], '</span>
			</dt>
			<dd class="pf_subject">
				<input type="text" name="demourl" size="50" />
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">', $txt['tema_form_uploadfile'], '</span>
			</dt>
			<dd class="pf_subject">
				<input type="file" size="48" name="download" />
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">', $txt['tema_form_uploadurl'], '</span>
			</dt>
			<dd class="pf_subject">
				<input type="text" name="fileurl" size="50" />
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">', $txt['tema_form_uploadresimfile'], '</span>
			</dt>
			<dd class="pf_subject">
				<input type="file" size="48" name="picture" />
			</dd>
			<dt class="clear pf_subject">
				<span id="caption_subject">', $txt['tema_form_uploadresimurl'], '</span>
			</dt>
			<dd class="pf_subject">
				<input type="text" name="pictureurl" size="50" />
			</dd>';
			foreach ($context['downloads_custom'] as $i => $custom)
			{
				echo '
					<dt class="clear pf_subject">
						<span id="caption_subject">', $custom['title'], ($custom['is_required'] ? '<font color="#FF0000">*</font>' : ''), '</span>
					</dt>
					<dd class="pf_subject">
						<input type="text" name="cus_', $custom['ID_CUSTOM'],'" value="' , $custom['defaultvalue'], '" />
					</dd>';
			}
		  if ($context['quotalimit'] != 0)
		  {
			echo '
				<dt class="clear pf_subject">
					<span id="caption_subject">',$txt['tema_quotagrouplimit'],'</span>
				</dt>
				<dd class="pf_subject">
					',Downloads_format_size($context['quotalimit'], 2),'
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">',$txt['tema_quotagspaceused'],'</span>
				</dt>
				<dd class="pf_subject">
					',Downloads_format_size($context['userspace'], 2),'
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">',$txt['tema_quotaspaceleft'],'</span>
				</dt>
				<dd class="pf_subject">
					' . Downloads_format_size(($context['quotalimit']-$context['userspace']), 2) . '
				</dd>';
		  }

		echo '</dl>
			<div class="centertext">
				<input type="submit" value="', $txt['tema_form_adddownload'], '" name="submit" />';
			if (!allowedTo('themes_autoapprove'))
				echo '<br /><p>'.$txt['tema_form_notapproved'].'</p>';
		echo '
			</div>
		  </div>
		</div>
	</form>';
	if ($context['show_spellchecking'])
		echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';
}

function template_edit_download()
{
	global $scripturl, $modSettings, $txt, $context, $settings;
	if ($context['show_spellchecking'])
		echo '<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';
	echo '
		<div class="cat_bar">
			<h3 class="catbg centertext">
			', $txt['tema_form_editdownload'], '
			</h3>
		</div>
		<form method="post" enctype="multipart/form-data" name="picform" id="picform" action="' . $scripturl . '?action=tema&sa=edit2" onsubmit="submitonce(this);">
			<div id="post_area">
				<div class="roundframe noup">
			<dl id="post_header">
			<dt class="clear pf_subject">
				<span id="caption_subject">' . $txt['tema_form_title'] . '</span>
				</dt>
				<dd class="pf_subject">
					<input type="text" name="title" size="50" value="' . $context['downloads_file']['title'] . '" />
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">' . $txt['tema_form_category'] . '</span>
				</dt>
				<dd class="pf_subject">
					<select name="cat">';
					foreach ($context['downloads_cat'] as $i => $category)
					{
						echo '<option value="' . $category['ID_CAT']  . '" ' . (($context['downloads_file']['ID_CAT'] == $category['ID_CAT']) ? ' selected="selected"' : '') .'>' . $category['title'] . '</option>';
					}
			 echo '</select>
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">' . $txt['tema_form_description'] . '</span>
				</dt>
				<dd class="pf_subject">';
					echo '', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
					if ($context['show_spellchecking'])
						echo '<br /><input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'picform\', \'description\');" />';
			echo '
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">' . $txt['tema_form_keywords'] . '</span>
				</dt>
				<dd class="pf_subject">
					<input type="text" name="keywords" size="50" maxlength="100" value="' . $context['downloads_file']['keywords'] . '" />
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">' . $txt['tema_form_demourl'] . '</span>
				</dt>
				<dd class="pf_subject">
					<input type="text" name="demourl" size="50" value="' . $context['downloads_file']['demourl'] . '" />
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">', $txt['tema_form_uploadfile'], '</span>
				</dt>
				<dd class="pf_subject">
					<input type="file" size="48" name="download" />
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">', $txt['tema_form_uploadurl'], '</span>
				</dt>
				<dd class="pf_subject">
					<input type="text" name="fileurl" size="50" value="' . $context['downloads_file']['fileurl'] . '" />
				</dd>';

				if ($context['downloads_file']['picture'] != ''){
				echo '
				<dt class="clear pf_subject">
					<span id="caption_subject">' .   $txt['tema_form_filenameicon'] . '</span>
				</dt>
				<dd class="pf_subject">
					' . $context['downloads_file']['picture'] .  '&nbsp;<a href="' . $scripturl . '?action=tema;sa=fileimgdel&id=' . $context['downloads_file']['ID_FILE'] . '">' . $txt['tema_rep_deletefile'] . '</a>
				</dd>';	
				}
			echo '
				<dt class="clear pf_subject">
					<span id="caption_subject">', $txt['tema_form_uploadresimfile'], '</span>
				</dt>
				<dd class="pf_subject">
					<input type="file" size="48" name="picture" />
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">', $txt['tema_form_uploadresimurl'], '</span>
				</dt>
				<dd class="pf_subject">
					<input type="text" name="pictureurl" size="50" value="' . $context['downloads_file']['pictureurl'] . '" />
				</dd>';

				foreach ($context['downloads_custom'] as $i => $custom)
				{
				echo '
				<dt class="clear pf_subject">
					<span id="caption_subject">', $custom['title'], ($custom['is_required'] ? '<font color="#FF0000">*</font>' : ''), '</span>
				</dt>
				<dd class="pf_subject">
					<input type="text" name="cus_', $custom['ID_CUSTOM'],'" value="' , $custom['value'], '" />
				</dd>';
				}

			  if (allowedTo('themes_manage') == true)
			  {
				  echo '
				<dt class="clear pf_subject">
					<span id="caption_subject">', $txt['tema_text_changeowner'], '</span>
				</dt>
				<dd class="pf_subject">
					<input type="text" name="pic_postername" id="pic_postername" value="" />
				  <a href="', $scripturl, '?action=findmember;input=pic_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/members.png" alt="', $txt['find_members'], '" /></a>
				  <a href="', $scripturl, '?action=findmember;input=pic_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a>
				</dd>';
			  }

			  if ($context['quotalimit'] != 0)
			  {
				echo '
				<dt class="clear pf_subject">
					<span id="caption_subject">',$txt['tema_quotagrouplimit'],'</span>
				</dt>
				<dd class="pf_subject">
					',Downloads_format_size($context['quotalimit'], 2),'
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">',$txt['tema_quotagspaceused'],'</span>
				</dt>
				<dd class="pf_subject">
					',Downloads_format_size($context['userspace'], 2),'
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">',$txt['tema_quotaspaceleft'],'</span>
				</dt>
				<dd class="pf_subject">
					', Downloads_format_size(($context['quotalimit']-$context['userspace']), 2), '
				</dd> ';
			  }

			echo '</dl>
			<div class="centertext">
				<input type="hidden" name="id" value="' . $context['downloads_file']['ID_FILE'] . '" />
				<input type="submit" value="' . $txt['tema_form_editdownload'] . '" name="submit" />';
			if (!allowedTo('themes_autoapprove'))
				echo '<br /><p>'.$txt['tema_form_notapproved'].'</p>';
			echo'<br /><b>' . $txt['tema_text_olddownload'] . '</b><br />
					' . $context['downloads_file']['orginalfilename'] . '<br />
					<span class="smalltext">' . $txt['tema_text_views']  . $context['downloads_file']['views'] . '<br />
					' . $txt['tema_text_filesize']  . Downloads_format_size($context['downloads_file']['filesize'],2) . '<br />
					' . $txt['tema_text_date'] . $context['downloads_file']['date'] . '<br />	
			</div>
		  </div>
		</div>
		</form>';
	if ($context['show_spellchecking'])
		echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';

}

function template_view_download()
{
	global $scripturl, $context, $txt, $modSettings, $settings, $memberContext, $user_info, $sourcedir, $boardurl;

	$keywords = explode(' ',$context['downloads_file']['keywords']);
 	$keywordscount = count($keywords);
        if ($modSettings['tema_set_file_title'])
        echo '
        <div class="cat_bar">
        		<h3 class="catbg centertext">
                ', $context['downloads_file']['title'], '
                </h3>
        </div>';
		echo '
		<div class="information centertext">';
			if (!empty($modSettings['tema_who_viewing']))
			{
				echo '<span class="smalltext floatleft">';
				echo empty($context['view_members_list']) ? '0 ' . $txt['tema_who_members'] : implode(', ', $context['view_members_list']) . (empty($context['view_num_hidden']) || $context['can_moderate_forum'] ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['tema_who_hidden'] . ')');

				echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['tema_who_viewfile'], '</span>';
			}
			if ($modSettings['tema_set_file_prevnext'])
				echo '<p class="floatright">
					<a href="', $scripturl, '?action=tema;sa=prev&id=', $context['downloads_file']['ID_FILE'], '">', $txt['tema_text_prev'], '</a> |
					<a href="', $scripturl, '?action=tema;sa=next&id=', $context['downloads_file']['ID_FILE'], '">', $txt['tema_text_next'], '</a>
					</p>';
		echo '
		</div>';
	echo '<div class="roundframe">
			<div class="centertext">';
					echo '<a class="butoon indirbutoon" href="' . $scripturl . '?action=tema;sa=downfile&id=', $context['downloads_file']['ID_FILE'], '">', ($context['downloads_file']['fileurl'] == '' ? $context['downloads_file']['orginalfilename'] : $txt['tema_app_download']), '</a>';

					if($context['downloads_file']['demourl'] != ''){
						echo '<a class="butoon demobutoon" href="'.$boardurl.'/demo/index.php?tema='.$context['downloads_file']['title'].'">Demo</a>';
					}
			echo '
			</div>
			<div class="description centertext">';					

				if($modSettings['tema_set_file_thumb'] != 0){
					echo '<img src="',$context['downloads_file']['picture'] == '' ? $context['downloads_file']['pictureurl'] : $modSettings['tema_url'].'temaresim/'.$context['downloads_file']['picture'],'" alt="'.$context['downloads_file']['title'].'">';
				}
			echo '
				</div>
			<div class="windowbg">';
			if ($modSettings['tema_set_file_desc'])
				echo '' . $txt['tema_form_description'] . ' ' . parse_bbc($context['downloads_file']['description']);

			echo '
			</div>
			<div class="windowbg">
				<div class="poster centertext">';
			
			if ($modSettings['tema_set_file_poster'])
			{

				if ($context['downloads_file']['real_name'] != ''){
					loadMemberData($context['downloads_file']['id_member']);
					loadMemberContext($context['downloads_file']['id_member']);
					$poster = $memberContext[$context['downloads_file']['id_member']];
					echo'<h4>
							'.$poster['link_color'].'
						</h4>
						<ul class="user_info">
							<li class="avatar">
								'.$poster['avatar']['image'].'
							</li>
							<li class="icons">
								'.$poster['group_icons'].'
							</li>
						</ul>';		
				}	
				else
					echo $txt['tema_text_postedby'] . ' ' . $txt['tema_guest'] . '&nbsp;';

			}
			
			if ($modSettings['tema_set_file_date'])
				echo'<p class="smalltext">'. $context['downloads_file']['date'] . '</p>';

			echo'</div>
			<div class="postarea"><dl class="stats"><dt>';	
			
				foreach ($context['downloads_custom'] as $i => $custom)
				{
					if ($custom['value'] != '')
						echo '<p class="smalltext">', $custom['title'], ': ',$custom['value'], '</p>';

				}
			if ($modSettings['tema_set_file_showfilesize'] && $context['downloads_file']['fileurl'] == '')
				echo $txt['tema_text_filesize']  . Downloads_format_size($context['downloads_file']['filesize'],2) . '<br />';
				
			if ($modSettings['tema_set_file_views'])
				echo $txt['tema_text_views'] . ' (' . $context['downloads_file']['views'] . ')<br />';

			if ($modSettings['tema_set_file_downloads'])
				echo $txt['tema_cat_downloads'] . ' (' . $context['downloads_file']['totaldownloads'] . ')<br />';

			if ($modSettings['tema_set_file_lastdownload'])
				echo $txt['tema_text_lastdownload'] . ' ' . ($context['downloads_file']['lastdownload'] != 0 ? timeformat($context['downloads_file']['lastdownload']) : $txt['tema_text_lastdownload2'] ) . '<br />';
		 	
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

			echo '</dt><dd>';

				// Show rating information
			if ($modSettings['tema_set_file_showrating'])
				if ($modSettings['tema_show_ratings'] == true && $context['downloads_file']['disablerating'] == 0)
				{
				echo'<div class="derece">';
					if ($context['downloads_file']['totalratings'] == 0)
					{
						echo'<p>'.$txt['tema_form_rating'] . $txt['tema_form_norating'].'</p><br class="clear"/>';
					}
					else
					{	
						$stars = 5;
						$derece =($context['downloads_file']['rating'] / ($context['downloads_file']['totalratings']* $stars) * 100);
						if ($derece == 0)
							echo $txt['tema_form_rating'];
						else if ($derece <= 20)
							echo $txt['tema_form_rating'].' : '.str_repeat('<span class="generic_star"></span>', 1);
						else if ($derece <= 40)
							echo $txt['tema_form_rating'].' : '.str_repeat('<span class="generic_star"></span>', 2);
						else if ($derece <= 60)
							echo $txt['tema_form_rating'].' : '.str_repeat('<span class="generic_star"></span>', 3);
						else if ($derece <= 80)
							echo $txt['tema_form_rating'].' : '.str_repeat('<span class="generic_star"></span>', 4);
						else if ($derece <= 100)
							echo $txt['tema_form_rating'].' : '.str_repeat('<span class="generic_star"></span>', 5);
						echo '<br/>'.$txt['tema_form_ratingby'].' : '.$context['downloads_file']['totalratings'] . $txt['tema_form_ratingmembers'] . '
						<br class="clear"/>';
					}
					
					if (allowedTo('themes_ratefile'))
					{
						$stars =1;
						echo '<div class="stars"><form method="post" action="' . $scripturl . '?action=tema;sa=rate">
								<input type="hidden" name="id" value="' . $context['downloads_file']['ID_FILE'] . '" />
								<input type="submit" name="submit"  value="' . $txt['tema_form_ratedownload'] . '" />';
							for($i = 5; $i >= $stars;$i--)
								echo '<input class="star star-' . $i .'" id="star-' . $i .'" type="radio" name="rating" value="' . $i .'" />
									<label class="star star-' . $i .'" for="star-' . $i .'"></label>';
						echo '</form></div>';
					}
			echo '</div>';
				
				}

				if (!empty($modSettings['tema_set_showcode_directlink']) || !empty($modSettings['tema_set_showcode_htmllink']))
				{
					echo '<div class="derece" style="line-height: 24px;"><p>',$txt['tema_txt_download_linking'],'</p>';
					if ($modSettings['tema_set_showcode_directlink'])
					{
						echo '<strong class="smalltext floatleft">', $txt['tema_txt_directlink'], '</strong><input class="deinput floatright" type="text" value="' . $scripturl . '?action=tema;sa=downfile&id=' . $context['downloads_file']['ID_FILE']  . '">';
					}
					if ($modSettings['tema_set_showcode_htmllink'])
					{
						echo '<strong class="smalltext floatleft">', $txt['tema_txt_htmllink'], '</strong><input class="deinput floatright" type="text" value="<a href=&#34;' . $scripturl . '?action=tema;sa=downfile&id=' . $context['downloads_file']['ID_FILE']  . '&#34;>', ($context['downloads_file']['fileurl'] == '' ? $context['downloads_file']['orginalfilename'] : $txt['tema_app_download']), '</a>" >';
					}
					echo '<br class="clear"/></div>';
				}
				
				echo '</dd></dl>
					<div class="buttonlist">';
				if (allowedTo('themes_manage'))
					echo '<a class="button" href="' . $scripturl . '?action=tema;sa=unapprove&id=' . $context['downloads_file']['ID_FILE'] . '">' . $txt['tema_text_unapprove'] . '</a>';
				if (allowedTo('themes_manage') || allowedTo('themes_edit') && $context['downloads_file']['id_member'] == $user_info['id'])
					echo '<a class="button" href="' . $scripturl . '?action=tema;sa=edit&id=' . $context['downloads_file']['ID_FILE']. '">' . $txt['tema_text_edit'] . '</a>';
				if (allowedTo('themes_manage') || allowedTo('themes_delete') && $context['downloads_file']['id_member'] == $user_info['id'])
					echo '<a class="button" href="' . $scripturl . '?action=tema;sa=delete&id=' . $context['downloads_file']['ID_FILE'] . '">' . $txt['tema_text_delete'] . '</a>';

				if (allowedTo('themes_report'))
				{
					echo '<a class="button" href="' . $scripturl . '?action=tema;sa=report&id=' . $context['downloads_file']['ID_FILE'] . '">' . $txt['tema_text_reportdownload'] . '</a>';
				}
				if (allowedTo('themes_ratefile'))
				{
					if (allowedTo('themes_manage'))
						echo '<a class="button" href="' . $scripturl . '?action=tema;sa=viewrating&id=' . $context['downloads_file']['ID_FILE'] . '">' . $txt['tema_form_viewratings'] . '</a>';	
				}			
				echo '
					</div>';
				
		echo '</div>
		</div>
	</div>';

	echo '
		<div class="pagesection">
			<div class="buttonlist floatright">
				<a class="button" href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a>
				<a class="button" href="', $scripturl, '?action=tema;sa=search">', $txt['tema_text_search2'], '</a>';	
				if (allowedTo('themes_add') && !($user_info['is_guest']))
				echo '<a class="button" href="', $scripturl , '?action=tema;sa=myfiles;u=' , $user_info['id'],'">', $txt['tema_text_myfiles2'], '</a>';
			echo'</div>
		</div>';

}

function template_delete_download()
{
	global $scripturl, $modSettings, $txt, $context, $settings;
	echo '    
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['tema_form_deldownload'], '
        </h3>
	</div>
	<form method="post" action="', $scripturl, '?action=tema;sa=delete2">
		<div class="information centertext">
			<div class="noticebox lefttext"><span class="alert">!</span> ' . $txt['tema_warn_deletedownload'] . ' </div>
			' . $txt['tema_text_deldownload'] . '<br />
			<a href="' . $scripturl . '?action=tema;sa=view;down=' . $context['downloads_file']['ID_FILE'] . '" target="blank">',$context['downloads_file']['title'],'</a>
				<p>Views: ' . $context['downloads_file']['views'] . '</p>
				<p>' . $txt['tema_text_filesize']  . Downloads_format_size($context['downloads_file']['filesize'],2) . '</p>
				<p>' . $txt['tema_text_date'] . $context['downloads_file']['date'] . '</p>
				 <br />
				<input type="hidden" name="id" value="' . $context['downloads_file']['ID_FILE'] . '" />
				<input type="submit" value="' . $txt['tema_form_deldownload'] . '" name="submit" />
		</div>
	</form>';
}

function template_report_download()
{
	global $scripturl, $context, $txt, $user_info;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['tema_form_reportdownload'], '
		</h3>
	</div>	
	<form method="post" name="cprofile" id="cprofile" action="' . $scripturl . '?action=tema;sa=report2">
		<div class="information centertext">
			<strong>' . $txt['tema_form_comment'] . '</strong><br />
			<textarea rows="6" name="comment" cols="54"></textarea><br />
			<input type="hidden" name="id" value="' . $context['downloads_file_id'] . '" />
			<input type="submit" value="' . $txt['tema_form_reportdownload'] . '" name="submit" />
		</div>	
	</form>';
	echo '
		<div class="pagesection">
			<div class="buttonlist floatright">
				<a class="button" href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a>
				<a class="button" href="', $scripturl, '?action=tema;sa=search">', $txt['tema_text_search2'], '</a>';	
				if (allowedTo('themes_add') && !($user_info['is_guest']))
				echo '<a class="button" href="', $scripturl , '?action=tema;sa=myfiles;u=' , $user_info['id'],'">', $txt['tema_text_myfiles2'], '</a>';
			echo'</div>
		</div>';
}


function template_approvelist()
{
	global $scripturl, $context, $modSettings, $txt;
	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				' . $txt['tema_form_approvedownloads']. '
			</h3>
        </div>
			<form method="post" action="', $scripturl, '?action=tema;sa=bulkactions">
			<table class="table_grid">
				<thead>
					<tr class="title_bar">
						<th>&nbsp;</th>
						<th  class="lefttext first_th">', $txt['tema_app_download'], '</th>
						<th  class="lefttext">', $txt['tema_text_category'], '</th>
						<th  class="lefttext">', $txt['tema_app_title'], '</th>
						<th  class="lefttext">', $txt['tema_app_description'], '</th>
						<th  class="lefttext">', $txt['tema_app_date'], '</th>
						<th  class="lefttext">', $txt['tema_app_membername'], '</th>
						<th  class="lefttext last_th">', $txt['tema_text_options'], '</th>
					</tr>
				</thead>
				<tbody>';
			foreach ($context['downloads_file'] as $i => $file)
			{
				echo '<tr class="windowbg">';
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
			}
		echo '
			</table>
			<div class="pagesection">';
				echo $context['page_index'];
			echo '<div class="floatright">',$txt['tema_text_withselected'],'
					<select name="doaction">
					<option value="approve">',$txt['tema_form_approvedownloads'],'</option>
					<option value="delete">',$txt['tema_form_deldownload'],'</option>
					</select>			
					<input type="submit" value="',$txt['tema_text_performaction'],'" />
				</div>
			</div>	
			</form>';
}

function template_reportlist()
{
	global $scripturl, $txt, $context;
	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				' . $txt['tema_form_reportdownloads'] . '
			</h3>
        </div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th  class="lefttext first_th">', $txt['tema_rep_filelink'], '</th>
					<th  class="lefttext">', $txt['tema_rep_comment'], '</th>
					<th  class="lefttext">', $txt['tema_app_date'], '</th>
					<th  class="lefttext">', $txt['tema_rep_reportby'], '</th>
					<th  class="lefttext last_th">', $txt['tema_text_options'], '</th>
				</tr>
			</thead>
			<tbody>';
			foreach ($context['downloads_reports'] as $i => $report)
			{

				echo '<tr class="windowbg">';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=view;down=' . $report['ID_FILE'] . '">' . $txt['tema_rep_viewdownload'] .'</a></td>';
				echo '<td>', $report['comment'], '</td>';
				echo '<td>', timeformat($report['date']), '</td>';

				if ($report['real_name'] != '')
					echo '<td><a href="' . $scripturl . '?action=profile;u=' . $report['id_member'] . '">'  . $report['real_name'] . '</a></td>';
				else
					echo '<td>',$txt['tema_guest'],'</td>';

				echo '<td><a href="' . $scripturl . '?action=tema;sa=delete&id=' . $report['ID_FILE'] . '">' . $txt['tema_form_deldownload2']  . '</a>';
				echo '<br /><a href="' . $scripturl . '?action=tema;sa=deletereport&id=' . $report['ID'] . '">' . $txt['tema_rep_delete'] . '</a></td>';
				echo '</tr>';
			}
		echo '
			</tbody>
		</table>';

}

function template_search()
{
	global $scripturl, $txt, $context, $settings,$user_info;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['tema_search_download'], '
        </h3>
	</div>
		<form method="post" action="', $scripturl, '?action=tema;sa=search2">
			<div id="post_area">
				<div class="roundframe noup">
			<dl id="post_header">
				<dt class="clear pf_subject">
					<span id="caption_subject">' . $txt['tema_search_for'] . '</span>
				</dt>
				<dd class="pf_subject">
					<input type="text" name="searchfor" size= "50" />
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">' . $txt['tema_search_title'] . '</span>
				</dt>
				<dd class="pf_subject">
					<input type="checkbox" name="searchtitle" checked="checked" />
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">' . $txt['tema_search_description'] . '</span>
				</dt>
				<dd class="pf_subject">
					<input type="checkbox" name="searchdescription" checked="checked" />
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">' . $txt['tema_search_keyword'] . '</span>
				</dt>
				<dd class="pf_subject">	
					<input type="checkbox" name="searchkeywords" />
				</dd>
				<hr />
				<p>',$txt['tema_search_advsearch'],'</p>
				<dt class="clear pf_subject">
					<span id="caption_subject">' . $txt['tema_text_category'] . '</span>
				</dt>
				<dd class="pf_subject">	
					<select name="cat">
						<option value="0">' . $txt['tema_text_catnone'] . '</option>';
						foreach ($context['downloads_cat'] as $i => $category)
						{
							echo '<option value="' . $category['ID_CAT']  . '" >' . $category['title'] . '</option>';
						}
						echo '
					</select>
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">' . $txt['tema_search_daterange']. '</span>
				</dt>
				<dd class="pf_subject">	
					<select name="daterange">
					<option value="0">' . $txt['tema_search_alltime']  . '</option>
					<option value="30">' . $txt['tema_search_days30']  . '</option>
					<option value="60">' . $txt['tema_search_days60']  . '</option>
					<option value="90">' . $txt['tema_search_days90']  . '</option>
					<option value="180">' . $txt['tema_search_days180']  . '</option>
					<option value="365">' . $txt['tema_search_days365']  . '</option>
					</select>
				</dd>
				<dt class="clear pf_subject">
					<span id="caption_subject">' . $txt['tema_search_membername']. '</span>
				</dt>
				<dd class="pf_subject">	
					<input type="text" name="pic_postername" id="pic_postername" value="" />
				  <a href="', $scripturl, '?action=findmember;input=pic_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/members.png" alt="', $txt['find_members'], '" /></a>
				  <a href="', $scripturl, '?action=findmember;input=pic_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a>
				</dd>
			</dl>
			<div class="centertext">
				<input type="submit" value="' . $txt['tema_search'] . '" name="submit" />
			</div>

				</div>
			</div>
		</form>';
	echo '
		<div class="pagesection">
			<div class="buttonlist floatright">
				<a class="button" href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a>
				<a class="button" href="', $scripturl, '?action=tema;sa=search">', $txt['tema_text_search2'], '</a>';	
				if (allowedTo('themes_add') && !($user_info['is_guest']))
				echo '<a class="button" href="', $scripturl , '?action=tema;sa=myfiles;u=' , $user_info['id'],'">', $txt['tema_text_myfiles2'], '</a>';
			echo'</div>
		</div>';
}

function template_search_results()
{
	global $context, $modSettings, $scripturl, $txt, $user_info;
		echo '
			<div class="cat_bar">
				<h3 class="catbg centertext">
					', $txt['tema_searchresults'], '
				</h3>
			</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">';
			if (!empty($modSettings['tema_set_t_title']))
			{
				echo  '<th  class="lefttext">', $txt['tema_cat_title'], '</th>';
			}

			if (!empty($modSettings['tema_set_t_rating']))
			{
				echo '<th  class="lefttext">', $txt['tema_cat_rating'], '</th>';
			}

			if (!empty($modSettings['tema_set_t_views']))
			{
				echo '<th  class="lefttext">', $txt['tema_cat_views'], '</th>';
			}
			
			if (!empty($modSettings['tema_set_t_downloads']))
			{
				echo '<th  class="lefttext">', $txt['tema_cat_downloads'] , '</th>';
			}

			if (!empty($modSettings['tema_set_t_filesize']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_filesize'], '</th>';
			}

			if (!empty($modSettings['tema_set_t_date']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_date'], '</th>';
			}

			if (!empty($modSettings['tema_set_t_comment']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_comments'],'</th>';
			}

			if (!empty($modSettings['tema_set_t_username']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_membername'],'</th>';
			}
			if (allowedTo('themes_manage') ||  (allowedTo('themes_delete') ) || (allowedTo('themes_edit')) )
			{
				echo '<th>',$txt['tema_cat_options'],'</th>';
			}

		echo '</tr>
		</thead>
		<tbody>';
		foreach ($context['downloads_files'] as $i => $file)
		{
			echo '<tr class="windowbg">';

			if (!empty($modSettings['tema_set_t_title']))
				echo  '<td><a href="' . $scripturl . '?action=tema;sa=view;down=', $file['ID_FILE'], '">', $file['title'], '</a></td>';

			if (!empty($modSettings['tema_set_t_rating']))
				echo '<td>';
					if ($file['totalratings'] == 0)
					{
						echo $txt['tema_text_catnone'];
					}
					else
					{
						$stars = 5;
						$derece =($file['rating'] / ($file['totalratings']* $stars) * 100);
							if ($derece == 0)
								echo $txt['tema_form_rating'];
							else if ($derece <= 20)
								echo str_repeat('<span class="generic_star"></span>', 1);
							else if ($derece <= 40)
								echo str_repeat('<span class="generic_star"></span>', 2);
							else if ($derece <= 60)
								echo str_repeat('<span class="generic_star"></span>', 3);
							else if ($derece <= 80)
								echo str_repeat('<span class="generic_star"></span>', 4);
							else if ($derece <= 100)
								echo str_repeat('<span class="generic_star"></span>', 5);
					}
				echo '</td>';

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

			if (allowedTo('themes_manage') ||  (allowedTo('themes_delete') && $file['id_member'] == $user_info['id']) || (allowedTo('themes_edit') && $file['id_member'] == $user_info['id']) )
			{
				echo '<td>';
				if (allowedTo('themes_manage'))
					echo '<a href="' . $scripturl . '?action=tema;sa=unapprove&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_unapprove'] . '</a>';
				if (allowedTo('themes_manage') || allowedTo('themes_edit') && $file['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=edit&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_edit'] . '</a>';
				if (allowedTo('themes_manage') || allowedTo('themes_delete') && $file['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=delete&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_delete'] . '</a>';

				echo '</td>';
			}
			echo '</tr>';

		}
	echo '</tbody>
	</table>';
	echo '
		<div class="pagesection">';
		if ($context['downloads_total'] > 0)
		{
			$q =  $context['downloads_search_query_encoded'];
			$context['page_index'] = constructPageIndex($scripturl . '?action=tema;sa=search2;q=' .$q, $_REQUEST['start'], $context['downloads_total'], 10);
			echo $context['page_index'];
		}
		echo '
			<div class="buttonlist floatright">
				<a class="button" href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a>
				<a class="button" href="', $scripturl, '?action=tema;sa=search">', $txt['tema_text_search2'], '</a>';	
				if (allowedTo('themes_add') && !($user_info['is_guest']))
				echo '<a class="button" href="', $scripturl , '?action=tema;sa=myfiles;u=' , $user_info['id'],'">', $txt['tema_text_myfiles2'], '</a>';
			echo'</div>
		</div>';
}

function template_myfiles()
{
	global $context, $modSettings, $scripturl, $txt, $user_info;
		echo '
		<div class="cat_bar">
			<h3 class="catbg centertext">
				', $context['downloads_userdownloads_name'], '
			</h3>
		</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">';
			if (!empty($modSettings['tema_set_t_title']))
			{
				echo  '<th  class="lefttext">', $txt['tema_cat_title'], '</th>';
			}

			if (!empty($modSettings['tema_set_t_rating']) )
			{
				echo '<th  class="lefttext">', $txt['tema_cat_rating'], '</th>';
			}

			if (!empty($modSettings['tema_set_t_views']))
			{
				echo '<th  class="lefttext">', $txt['tema_cat_views'], '</th>';
			}

			if (!empty($modSettings['tema_set_t_downloads']))
			{
				echo '<th  class="lefttext">', $txt['tema_cat_downloads'] , '</th>';
			}

			if (!empty($modSettings['tema_set_t_filesize']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_filesize'], '</th>';
			}

			if (!empty($modSettings['tema_set_t_date']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_date'], '</th>';
			}

			if (!empty($modSettings['tema_set_t_username']))
			{
				echo '<th  class="lefttext">',$txt['tema_cat_membername'],'</th>';
			}

			if (allowedTo('themes_manage') ||  (allowedTo('themes_delete') ) || (allowedTo('themes_edit')) )
			{
				echo '<th class="lefttext">',$txt['tema_cat_options'],'</th>';
			}

		echo '</tr>
		</thead>
		<tbody>';
		foreach ($context['downloads_files'] as $i => $file)
		{

			echo '<tr class="windowbg">';

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
				echo '<td>';
					if ($file['totalratings'] == 0)
					{
						echo $txt['tema_text_catnone'];
					}
					else
					{
						$stars = 5;
						$derece =($file['rating'] / ($file['totalratings']* $stars) * 100);
						if ($derece == 0)
							echo $txt['tema_form_rating'];
						else if ($derece <= 20)
							echo str_repeat('<span class="generic_star"></span>', 1);
						else if ($derece <= 40)
							echo str_repeat('<span class="generic_star"></span>', 2);
						else if ($derece <= 60)
							echo str_repeat('<span class="generic_star"></span>', 3);
						else if ($derece <= 80)
							echo str_repeat('<span class="generic_star"></span>', 4);
						else if ($derece <= 100)
							echo str_repeat('<span class="generic_star"></span>', 5);
					}
				echo '</td>';

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

			if (allowedTo('themes_manage') ||  (allowedTo('themes_delete') && $file['id_member'] == $user_info['id']) || (allowedTo('themes_edit') && $file['id_member'] == $user_info['id']) )
			{
				echo '<td>';
				if (allowedTo('themes_manage'))
					echo '<a href="' . $scripturl . '?action=tema;sa=unapprove&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_unapprove'] . '</a>';
				if (allowedTo('themes_manage') || allowedTo('themes_edit') && $file['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=edit&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_edit'] . '</a>';
				if (allowedTo('themes_manage') || allowedTo('themes_delete') && $file['id_member'] == $user_info['id'])
					echo '&nbsp;<a href="' . $scripturl . '?action=tema;sa=delete&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_delete'] . '</a>';

				echo '</td>';
			}

			echo '</tr>';
		}
		echo '
			</tbody>
		</table>';
	echo '
		<div class="pagesection">';
			if ($context['downloads_total'] > 0)
			{
			echo $context['page_index'];
			}
		echo'
			<div class="buttonlist floatright">
				<a class="button" href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a>
				<a class="button" href="', $scripturl, '?action=tema;sa=search">', $txt['tema_text_search2'], '</a>';	
				if (allowedTo('themes_add') && !($user_info['is_guest']))
				echo '<a class="button" href="', $scripturl , '?action=tema;sa=myfiles;u=' , $user_info['id'],'">', $txt['tema_text_myfiles2'], '</a>';
			echo'</div>
		</div>';
}

function template_view_rating()
{
	global  $context, $settings, $scripturl, $txt,$user_info;
	echo '
		<div class="cat_bar">
			<h3 class="catbg centertext">
				' . $txt['tema_form_viewratings'] . '
			</h3>
		</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th>' . $txt['tema_app_membername'] . '</th>
					<th>' . $txt['tema_text_rating'] . '</th>
					<th>' . $txt['tema_text_options'] . '</th>
				</tr>
			</thead>
			<tbody>';
				foreach ($context['downloads_rating'] as $i => $rating)
				{
					echo '<tr class="windowbg">
							<td><a href="' . $scripturl . '?action=profile;u=' . $rating['id_member'] . '">'  . $rating['real_name'] . '</a></td>
							<td>';
							for($i=0; $i < $rating['value']; $i++)
								echo '<img src="', $settings['images_url'], '/membericons/icon.png" alt="*" border="0" />';
					echo '</td>
						  <td><a href="' . $scripturl . '?action=tema;sa=delrating&id=' . $rating['ID'] . '">'  . $txt['tema_text_delete'] . '</a></td>
						  </tr>';
				}
			echo '  
			</tbody>
		</table>';
	echo '
		<div class="pagesection">
			<div class="buttonlist floatright">
				<a class="button" href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a>
				<a class="button" href="', $scripturl, '?action=tema;sa=search">', $txt['tema_text_search2'], '</a>';	
				if (allowedTo('themes_add') && !($user_info['is_guest']))
				echo '<a class="button" href="', $scripturl , '?action=tema;sa=myfiles;u=' , $user_info['id'],'">', $txt['tema_text_myfiles2'], '</a>';
			echo'</div>
		</div>';
}

function template_stats()
{
	global $settings, $context, $txt, $scripturl, $user_info;

	echo '
	<div id="statistics" class="main_section">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['tema_stats_title'], '</h3>
		</div>
		<div class="roundframe">
			<div class="title_bar">
				<h4 class="titlebg">
					<span class="generic_icons general"></span> ', $txt['tema_stats_title'], '
				</h4>
			</div>
			<dl class="stats half_content nobb">
				<dt>', $txt['tema_stats_totalfiles'] ,  '</dt>
				<dd>', comma_format($context['total_files']) , '</dd>
				<dt>', $txt['tema_stats_totalviews'] ,  '</dt>
				<dd>', comma_format($context['total_views']) , '</dd>
			</dl>
			<dl class="stats half_content nobb">
				<dt>', $txt['tema_stats_totaldownloads'] , '</dt>
				<dd>', comma_format($context['total_downloads']), '</dd>
				<dt>', $txt['tema_stats_totalfize'] ,  '</dt>
				<dd>', $context['total_filesize'] , '</dd>
			</dl>
			<div class="half_content">
				<div class="title_bar">
					<h4 class="titlebg">
						', $txt['tema_stats_viewed'], '
					</h4>
				</div>
				<dl class="stats">';
					foreach ($context['top_viewed'] as $file)
						{
							echo '
							<dt>', $file['link'], '</dt>
							<dd class="statsbar">';
							if (!empty($file['percent']))
							echo '
							<div class="bar" style="width: ', $file['percent'], '%;">
								<span class="righttext">', $file['views'], '</span>
							</div>';
							else
							echo '
							<div class="bar empty"><span class="righttext">', $file['views'], '</span></div>';
							echo '
							</dd>';
						}
					
					
				echo'	
				</dl>
			</div>
			<div class="half_content">
				<div class="title_bar">
					<h4 class="titlebg">
						', $txt['tema_stats_toprated'], '
					</h4>
				</div>
				<dl class="stats">';
					foreach ($context['top_rating'] as $file)
						{
							echo '<dt>', $file['link'], '</dt>
							<dd class="statsbar">';
							if (!empty($file['percent']))
							echo '
							<div class="bar" style="width: ', $file['percent'], '%;">
								<span class="righttext">', $file['rating'], '</span>
							</div>';
							else
							echo '
							<div class="bar empty"><span class="righttext">', $file['rating'], '</span></div>';
							echo '
							</dd>';
						}
				echo'	
				</dl>
			</div>
			<div class="half_content">
				<div class="title_bar">
					<h4 class="titlebg">
						', $txt['tema_stats_topfile'], '
					</h4>
				</div>
				<dl class="stats">';
					foreach ($context['totaldownloads'] as $file)
						{
							echo '<dt>', $file['link'], '</dt>
							<dd class="statsbar">';
							if (!empty($file['percent']))
							echo '
							<div class="bar" style="width: ', $file['percent'], '%;">
								<span class="righttext">', $file['totaldownloads'], '</span>
							</div>';
							else
							echo '
							<div class="bar empty"><span class="righttext">', $file['totaldownloads'], '</span></div>';
							echo '
							</dd>';
						}
				echo'
				</dl>
			</div>
			<div class="half_content">
				<div class="title_bar">
					<h4 class="titlebg">
						',$txt['tema_stats_last'], '
					</h4>
				</div>
				<dl class="stats half_content nobb">';
					foreach ($context['last_upload'] as $file)
						{
							echo '<dt>', $file['link'], '</dt><dd></dd>';
						}
				echo'
				</dl>
			</div>
		</div>
		<br class="clear">	
	</div>';
		echo '
		<div class="pagesection">
			<div class="buttonlist floatright">
				<a class="button" href="', $scripturl, '?action=tema">', $txt['tema_text_returndownload'], '</a>
				<a class="button" href="', $scripturl, '?action=tema;sa=search">', $txt['tema_text_search2'], '</a>';	
				if (allowedTo('themes_add') && !($user_info['is_guest']))
				echo '<a class="button" href="', $scripturl , '?action=tema;sa=myfiles;u=' , $user_info['id'],'">', $txt['tema_text_myfiles2'], '</a>';
			echo'</div>
		</div>';
        
}
function template_settings()
{
	global $scripturl, $modSettings, $txt;

echo '
			<div class="cat_bar">
								<h3 class="catbg">' . $txt['tema_text_settings'] . '</h3>
            </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg">
			<td>
				
			<form method="post" action="' . $scripturl . '?action=tema;sa=adminset2">
				<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4">
					<tr><td width="30%">' . $txt['tema_set_filesize'] . '</td><td><input type="text" name="tema_max_filesize" value="' .  $modSettings['tema_max_filesize'] . '" /> (bytes)</td></tr>
				<tr><td width="30%">' . $txt['tema_set_path'] . '</td><td><input type="text" name="tema_path" value="' .  $modSettings['tema_path'] . '" size="50" /></td></tr>
				<tr><td width="30%">' . $txt['tema_set_url'] . '</td><td><input type="text" name="tema_url" value="' .  $modSettings['tema_url'] . '" size="50" /></td></tr>

				<tr><td width="30%">' . $txt['tema_upload_max_filesize'] . '</td><td><a href="http://www.php.net/manual/en/ini.core.php#ini.upload-max-filesize" target="_blank">' . @ini_get("upload_max_filesize") . '</a></td></tr>
				<tr><td width="30%">' . $txt['tema_post_max_size'] . '</td><td><a href="http://www.php.net/manual/en/ini.core.php#ini.post-max-size" target="_blank">' . @ini_get("post_max_size") . '</a></td></tr>
				<tr><td colspan="2">',$txt['tema_upload_limits_notes'] ,'</td></tr>



				<tr><td width="30%">' . $txt['tema_set_files_per_page'] . '</td><td><input type="text" name="tema_set_files_per_page" value="' .  $modSettings['tema_set_files_per_page'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['tema_set_cat_width'] . '</td><td><input type="text" name="tema_set_cat_width" value="' .  $modSettings['tema_set_cat_width'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['tema_set_cat_height'] . '</td><td><input type="text" name="tema_set_cat_height" value="' .  $modSettings['tema_set_cat_height'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['tema_set_file_image_width'] . '</td><td><input type="text" name="tema_set_file_image_width" value="' .  $modSettings['tema_set_file_image_width'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['tema_set_file_image_height'] . '</td><td><input type="text" name="tema_set_file_image_height" value="' .  $modSettings['tema_set_file_image_height'] . '" /></td></tr>
				</table>
				<input type="checkbox" name="tema_set_file_thumb" ' . ($modSettings['tema_set_file_thumb'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_thumb'] . '<br />
				<input type="checkbox" name="tema_who_viewing" ' . ($modSettings['tema_who_viewing'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_whoonline'] . '<br />
				<input type="checkbox" name="tema_set_count_child" ' . ($modSettings['tema_set_count_child'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_count_child'] . '<br />
				<input type="checkbox" name="tema_set_show_quickreply" ' . ($modSettings['tema_set_show_quickreply'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_show_quickreply'] . '<br />
				<input type="checkbox" name="tema_show_ratings" ' . ($modSettings['tema_show_ratings'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_showratings'] . '<br />
				<input type="checkbox" name="tema_set_enable_multifolder" ' . ($modSettings['tema_set_enable_multifolder'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_enable_multifolder'] . '<br />
				<input type="checkbox" name="tema_index_toprated" ' . ($modSettings['tema_index_toprated'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_index_toprated'] . '<br />
				<input type="checkbox" name="tema_index_recent" ' . ($modSettings['tema_index_recent'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_index_recent'] . '<br />
				<input type="checkbox" name="tema_index_mostviewed" ' . ($modSettings['tema_index_mostviewed'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_index_mostviewed'] . '<br />
				<input type="checkbox" name="tema_index_mostdownloaded" ' . ($modSettings['tema_index_mostdownloaded'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_index_mostdownloaded'] . '<br />


				<b>' . $txt['tema_catthumb_settings'] . '</b><br />
				<input type="checkbox" name="tema_set_t_title" ' . ($modSettings['tema_set_t_title'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_title'] . '<br />
				<input type="checkbox" name="tema_set_t_downloads" ' . ($modSettings['tema_set_t_downloads'] ? ' checked="checked" ' : '') . ' />' .$txt['tema_set_t_downloads'] . '<br />
				<input type="checkbox" name="tema_set_t_views" ' . ($modSettings['tema_set_t_views'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_t_views'] . '<br />
				<input type="checkbox" name="tema_set_t_filesize" ' . ($modSettings['tema_set_t_filesize'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_t_filesize'] . '<br />
				<input type="checkbox" name="tema_set_t_date" ' . ($modSettings['tema_set_t_date'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_t_date'] . '<br />
				<input type="checkbox" name="tema_set_t_username" ' . ($modSettings['tema_set_t_username'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_t_username'] . '<br />
				<input type="checkbox" name="tema_set_t_rating" ' . ($modSettings['tema_set_t_rating'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_t_rating'] . '<br />

				<b>' . $txt['tema_files_settings'] . '</b><br />

				<input type="checkbox" name="tema_set_file_prevnext" ' . ($modSettings['tema_set_file_prevnext'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_prevnext'] . '<br />
				<input type="checkbox" name="tema_set_file_desc" ' . ($modSettings['tema_set_file_desc'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_desc'] . '<br />
				<input type="checkbox" name="tema_set_file_title" ' . ($modSettings['tema_set_file_title'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_title'] . '<br />
				<input type="checkbox" name="tema_set_file_views" ' . ($modSettings['tema_set_file_views'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_views'] . '<br />
				<input type="checkbox" name="tema_set_file_downloads" ' . ($modSettings['tema_set_file_downloads'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_downloads'] . '<br />
				<input type="checkbox" name="tema_set_file_lastdownload" ' . ($modSettings['tema_set_file_lastdownload'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_lastdownload'] . '<br />
				<input type="checkbox" name="tema_set_file_poster" ' . ($modSettings['tema_set_file_poster'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_poster'] . '<br />
				<input type="checkbox" name="tema_set_file_date" ' . ($modSettings['tema_set_file_date'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_date'] . '<br />
				<input type="checkbox" name="tema_set_file_showfilesize" ' . ($modSettings['tema_set_file_showfilesize'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_showfilesize'] . '<br />
				<input type="checkbox" name="tema_set_file_showrating" ' . ($modSettings['tema_set_file_showrating'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_showrating'] . '<br />
				<input type="checkbox" name="tema_set_file_keywords" ' . ($modSettings['tema_set_file_keywords'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_file_keywords'] . '<br />

				<br /><b>' . $txt['tema_txt_download_linking'] . '</b><br />
				<input type="checkbox" name="tema_set_showcode_directlink" ' . ($modSettings['tema_set_showcode_directlink'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_showcode_directlink'] . '<br />
				<input type="checkbox" name="tema_set_showcode_htmllink" ' . ($modSettings['tema_set_showcode_htmllink'] ? ' checked="checked" ' : '') . ' />' . $txt['tema_set_showcode_htmllink'] . '<br />

				';

				if (!is_writable($modSettings['tema_path']))
					echo '<font color="#FF0000"><b>' . $txt['tema_write_error']  . $modSettings['tema_path'] . '</b></font>';

				echo '

				<input type="submit" name="savesettings" value="' . $txt['tema_save_settings'] . '" />
			</form>
			<br />
			<b>' . $txt['tema_text_permissions'] . '</b><br/><span class="smalltext">' . $txt['tema_set_permissionnotice'] . '</span>
			<br /><a href="' . $scripturl . '?action=admin;area=modsettings;sa=tema_izinler">' . $txt['tema_set_editpermissions']  . '</a>

			</td>
		</tr>
</table>';

}


function template_filespace()
{
	global $scripturl, $txt, $context;
		echo '
			<div class="cat_bar">
				<h3 class="catbg">
				' . $txt['tema_filespace']. '
				</h3>
            </div>

            <b>' .$txt['tema_filespace_groupquota_title'] . '</b>
			<table class="table_grid">
                <thead>
					<tr class="title_bar">
						<th>' . $txt['tema_filespace_groupname'] . '</th>
						<th>' .$txt['tema_filespace_limit']  . '</th>
						<th>' .  $txt['tema_text_options']  . '</th>
					</tr>
                </thead>';

			foreach ($context['downloads_membergroups'] as $i => $group)
			{

				echo '<tr class="windowbg">';
				echo '<td>'  . $group['group_name'] . '</td>';
				echo '<td>' . Downloads_format_size($group['totalfilesize'], 2) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=deletequota&id=' . $group['ID_GROUP'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';

			}
			foreach ($context['downloads_reggroup'] as $i => $group)
			{
				echo '<tr class="windowbg">';
				echo '<td>', $txt['membergroups_members'], '</td>';
				echo '<td>' . Downloads_format_size($group['totalfilesize'], 2) . '</td>';
				echo '<td><a href="',$scripturl, '?action=tema;sa=deletequota&id=' . $group['ID_GROUP'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';

			}

		echo '
			</table>
				<div class="windowbg centertext">
					<form class="login" method="post" action="' . $scripturl . '?action=tema;sa=addquota">
						<dl>
						<dt>
							' . $txt['tema_filespace_groupname']  . '
						</dt>
						<dd>	
							<select name="groupname">
								<option value="0">', $txt['membergroups_members'], '</option>';
								foreach ($context['groups'] as $group)
									echo '<option value="', $group['ID_GROUP'], '">', $group['group_name'], '</option>';

							echo '</select>
						</dd>
						<dt>
							' . $txt['tema_filespace_limit'] . '
						</dt>
						<dd>	
							<input type="text" name="filelimit" /> (bytes)
						</dd>
						</dl>
						<p>	
							<input type="submit" value="' . $txt['tema_filespace_addquota'] . '" />
						</p>
					</form>
				</div>
			<table class="table_grid">
				<thead>
					<tr class="title_bar">
						<th>' . $txt['tema_app_membername'] . '</th>
						<th>' . $txt['tema_text_options'] . '</th>
						<th>' . $txt['tema_filespace_filesize']  . '</th>
					</tr>
                </thead>';

				foreach ($context['downloads_members'] as $i => $member)
				{

					echo '<tr class="windowbg">';
					echo '<td><a href="' . $scripturl . '?action=profile;u=' . $member['id_member'] . '">'  . $member['real_name'] . '</a></td>';
					echo '<td><a href="' . $scripturl . '?action=tema;sa=filelist&id=' . $member['id_member'] . '">'  . $txt['tema_filespace_list'] . '</a></td>';
					echo '<td>' . Downloads_format_size($member['totalfilesize'], 2) . '</td>';
					echo '</tr>';
				}
			echo'
			</table>';
			

			echo '
			<div class="pagesection">
				<form method="post" action="' . $scripturl . '?action=tema;sa=recountquota">
					<input type="submit" value="' . $txt['tema_filespace_recount'] . '" />
				</form>
				',$context['page_index'],'
			</div>';

}

function template_filelist()
{
	global $scripturl, $txt, $context, $modSettings;

		echo '
			<div class="cat_bar">
				<h3 class="catbg">
					' . $txt['tema_filespace_list_title'] . ' - ' . $context['downloads_filelist_real_name'] . '
				</h3>
            </div>

			<table class="table_grid">
				<thead>
					<tr class="title_bar">
						<th>' . $txt['tema_app_title'] . '</th>
						<th>' . $txt['tema_filespace_filesize']  . '</th>
						<th>' . $txt['tema_text_options'] . '</th>
					</tr>
				</thead>';
		  	foreach ($context['downloads_files'] as $i => $file)
			{

				echo '<tr class="windowbg">';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=view;down=' . $file['ID_FILE'] . '">', $file['title'],'</a></td>';
				echo '<td>' . Downloads_format_size($file['filesize'], 2) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=delete&id=' . $file['ID_FILE'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';
			}
			echo'
			</table>
			<div class="pagesection">';
				if ($context['downloads_total'] > 0)
				{
				echo $context['page_index'];
				}
			echo'<a class="button" href="' . $scripturl . '?action=admin;area=tema;sa=filespace">' . $txt['tema_filespace'] . '</a>	
			</div>';
}

function template_catpermlist()
{
	global $scripturl, $txt, $context;
		echo '
			<div class="cat_bar">
				<h3 class="catbg">
					' . $txt['tema_text_catpermlist'] . '
				</h3>
            </div>
			<table class="table_grid">
				<thead>
					<tr class="title_bar">
						<th>' . $txt['tema_filespace_groupname'] . '</th>
						<th>' . $txt['tema_text_category']  . '</th>
						<th>' .  $txt['tema_perm_view']  . '</th>
						<th>' .  $txt['tema_perm_add']  . '</th>
						<th>' .  $txt['tema_perm_edit']  . '</th>
						<th>' .  $txt['tema_perm_delete']  . '</th>
						<th>' .  $txt['tema_text_options']  . '</th>
					</tr>
				</thead>';
			foreach ($context['downloads_membergroups'] as $i => $row)
			{

				echo '<tr class="windowbg">';
				echo '<td>'  . $row['group_name'] . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catperm;cat=' . $row['ID_CAT'] . '">'  . $row['catname'] . '</a></td>';
				echo '<td>' . ($row['view'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['addfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['editfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['delfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';

			}
			foreach ($context['downloads_regmem'] as $i => $row)
			{

				echo '<tr class="windowbg">';
				echo '<td>'  . $txt['membergroups_members'] . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catperm;cat=' . $row['ID_CAT'] . '">'  . $row['catname'] . '</a></td>';
				echo '<td>' . ($row['view'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['addfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['editfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['delfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';
			}

			foreach ($context['downloads_guestmem'] as $i => $row)
			{

				echo '<tr class="windowbg">';
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
		<div class="windowbg centertext">
			<form class="login" method="post" action="' . $scripturl . '?action=tema;sa=catperm2">
				<p>'  . $txt['tema_text_addperm'] . '</p>
				<dl>
					<dt>
						' . $txt['tema_filespace_groupname'] . '
					</dt>
					<dd>
						<select name="groupname">
							<option value="-1">' . $txt['membergroups_guests'] . '</option>
							<option value="0">' . $txt['membergroups_members'] . '</option>';
								foreach ($context['groups'] as $group)
								echo '<option value="', $group['ID_GROUP'], '">', $group['group_name'], '</option>';
					echo '</select>
					</dd>
					<dt>
						' . $txt['tema_perm_view'] .'
					</dt>
					<dd>
						<input type="checkbox" name="view" checked="checked" />
					</dd>
					<dt>
						'.$txt['tema_perm_viewdownload'].'
					</dt>
					<dd>
						<input type="checkbox" name="viewdownload" checked="checked" />
					</dd>
					<dt>
						' . $txt['tema_perm_add'] .'
					</dt>
					<dd>
						<input type="checkbox" name="add" checked="checked" />
					</dd>
					<dt>
						' . $txt['tema_perm_edit'] .'
					</dt>
					<dd>
						<input type="checkbox" name="edit" checked="checked" />
					</dd>
					<dt>
						' . $txt['tema_perm_delete'] .'
					</dt>
					<dd>
						<input type="checkbox" name="delete" checked="checked" />
					</dd>
				</dl>	
				<p><input type="hidden" name="cat" value="' . $context['downloads_cat'] . '" />
				<input type="submit" value="' . $txt['tema_text_addperm'] . '" /></p>	
			</form>
		</div>
		<table class="table_grid">
				<thead>
					<tr class="title_bar">
						<th>' . $txt['tema_filespace_groupname'] . '</th>
						<th>' .  $txt['tema_perm_view']  . '</th>
						<th>' .  $txt['tema_perm_viewdownload']  . '</th>
						<th>' .  $txt['tema_perm_add']  . '</th>
						<th>' .  $txt['tema_perm_edit']  . '</th>
						<th>' .  $txt['tema_perm_delete']  . '</th>
						<th>' .  $txt['tema_text_options']  . '</th>
					</tr>
				</thead>';
			foreach ($context['downloads_membergroups'] as $i => $row)
			{

				echo '<tr class="windowbg">';
				echo '<td>', $row['group_name'], '</td>';
				echo '<td>' . ($row['view'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['viewdownload'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['addfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['editfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['delfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';

			}
			foreach ($context['downloads_reggroup'] as $i => $row)
			{

				echo '<tr class="windowbg">';
				echo '<td>', $txt['membergroups_members'], '</td>';
				echo '<td>' . ($row['view'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['viewdownload'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['addfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['editfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td>' . ($row['delfile'] ? $txt['tema_perm_allowed'] : $txt['tema_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=tema;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['tema_text_delete'] . '</a></td>';
				echo '</tr>';
			}
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
		</table>';
}
function Enlist()
{

	global $context,$txt,$modSettings,$scripturl;
				echo '<br class="clear"/>
					<div class="cat_bar">
						<h3 class="catbg centertext">
							', $context['MainPagebaslik'], '
						</h3>
					</div>';
					echo '<div class="themesicerik">'; 
				foreach($context['MainPageicerik'] as $icerik)
				{

					echo '<div class="themesbox">
						<a href="' . $scripturl . '?action=tema;sa=view;down=' . $icerik['ID_FILE'] . '"><img src="',$icerik['picture'] == '' ? $modSettings['tema_url'].'temaresim/default.png' : $modSettings['tema_url'].'temaresim/'.$icerik['picture'],'" alt="">
						</a><br />
						<a class="subject" href="' . $scripturl . '?action=tema;sa=view;down=' . $icerik['ID_FILE'] . '">',$icerik['title'],'</a>';
						echo'<dl>';
					if (!empty($modSettings['tema_set_t_rating']))
						if ($icerik['totalratings'] == 0)
						{
							echo' <dt>'. $txt['tema_form_rating'].'</dt><dd> '. $txt['tema_text_catnone'] . '</dd>';
						}
						else
						{
							$stars = 5;
							$derece =($icerik['rating'] / ($icerik['totalratings']* $stars) * 100);
							if ($derece == 0)
								echo $txt['tema_form_rating'];
							else if ($derece <= 20)
								echo' <dt>'.$txt['tema_form_rating'].'</dt><dd> '.str_repeat('<span class="generic_star"></span>', 1) . '</dd>';
							else if ($derece <= 40)
								echo' <dt>'.$txt['tema_form_rating'].'</dt><dd> '.str_repeat('<span class="generic_star"></span>', 2) . '</dd>';
							else if ($derece <= 60)
								echo' <dt>'.$txt['tema_form_rating'].'</dt><dd> '.str_repeat('<span class="generic_star"></span>', 3) . '</dd>';
							else if ($derece <= 80)
								echo' <dt>'.$txt['tema_form_rating'].'</dt><dd> '.str_repeat('<span class="generic_star"></span>', 4) . '</dd>';
							else if ($derece <= 100)
								echo' <dt>'.$txt['tema_form_rating'].'</dt><dd> '.str_repeat('<span class="generic_star"></span>', 5) . '</dd>';
						}
					if (!empty($modSettings['tema_set_t_downloads']))
						echo' <dt>'.$txt['tema_text_downloads'].'</dt><dd> '. $icerik['totaldownloads'] . '</dd>';
					if (!empty($modSettings['tema_set_t_views']))
						echo' <dt>'. $txt['tema_text_views'].'</dt><dd>'.$icerik['views'] . '</dd>';
					if (!empty($modSettings['tema_set_t_filesize']))
						echo' <dt>'. $txt['tema_text_filesize'].'</dt><dd>  '. Downloads_format_size($icerik['filesize'], 2) . '<br />';
					if (!empty($modSettings['tema_set_t_date']))
						echo' <dt>'. $txt['tema_text_date'].'</dt><dd>  '.timeformat($icerik['date'], '%d %b %Y ') . '</dd>';
					if (!empty($modSettings['tema_set_t_username']))
					{
						if ($icerik['real_name'] != '')
							echo' <dt>'. $txt['tema_text_by'] . '</dt><dd> <a href="' . $scripturl . '?action=profile;u=' . $icerik['id_member'] . '">'  . $icerik['real_name'] . '</a></dd>';
						else
							echo' <dt>'.  $txt['tema_text_by'] . '</dt><dd> ' . $txt['tema_guest'] . '</dd>';
					}
					echo '</dl></div>';
				
				}
				echo '
				  </div><br class="clear"/>';
	

}
?>