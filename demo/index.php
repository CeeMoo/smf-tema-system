<?php

require "../SSI.php";

// Don't do anything if SMF is already loaded.
if (!defined('SMF'))
	return true;

global $context, $mbname, $settings, $txt, $modSettings, $scripturl, $smcFunc, $user_info, $sourcedir;


   // Load the language files
    if (loadlanguage('tema') == false)
        loadLanguage('tema','english');

	if (empty($modSettings['tema_url']))
		$modSettings['tema_url'] = $boardurl . '/tema/';

$demoyolurl =  $boardurl.'/demo/';
$temayol = $boardurl."/index.php?action=tema;sa=view;down=";


$gel = $smcFunc['db_query']('', "SELECT p.id_cat, c.id_cat, p.title, p.ID_FILE, p.date, p.picture, p.pictureurl, p.demourl, c.title catname
	FROM {db_prefix}tema_file as p
		LEFT JOIN {db_prefix}tema_cat as c ON (c.id_cat = p.id_cat)
	WHERE p.approved = 1 AND p.demourl != ''
	ORDER BY p.ID_FILE DESC");
$context['temalar'] = array();
	while($geldi = $smcFunc['db_fetch_assoc']($gel)){
		$context['temalar'][] = array(
			'ID_FILE' => $geldi['ID_FILE'],
			'title' => $geldi['title'],
			'picture' => $geldi['picture'],
			'pictureurl' => $geldi['pictureurl'],
			'demourl' => $geldi['demourl'],
			'catname' => $geldi['catname'],
		);
	}

$smcFunc['db_free_result']($gel);



$dbresult2 = $smcFunc['db_query']('', "SELECT p.title, p.ID_FILE, p.demourl FROM {db_prefix}tema_file as p WHERE p.demourl != '' ORDER BY p.ID_FILE DESC");
$row2 = $smcFunc['db_fetch_assoc']($dbresult2);
$smcFunc['db_free_result']($dbresult2);

?>

<?php
echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', !empty($txt['lang_locale']) ? ' lang="' . str_replace("_", "-", substr($txt['lang_locale'], 0, strcspn($txt['lang_locale'], "."))) . '"' : '', '>
<head>
	<meta charset="', $context['character_set'], '">';
	
	echo '
	<title>', $context['meta_tags'][0]['content'], ' - Theme Live Demo</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">';
?>
    <meta name="keywords" content="" />
    <meta name="description" content="" />

    <!-- CSS Style -->
    <link rel="stylesheet" href="css/bar.css" />
    <link rel="stylesheet" href="css/bar-frame.css" />


    <!-- JavaScript -->
    <script type="text/javascript" src="js/jquery.js"></script>
    <script src="js/bar.js"></script>
    </head>
    <body>

<?php include "demopage.php"; ?>

    </body>
</html>