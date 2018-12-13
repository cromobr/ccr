<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=<?php echo $charset;?>" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<title>Sistema - Galeria</title>
<meta name="title" content="<?php echo $pagetitle;?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="googlebot" content="noarchive,nofollow" />
<link href="<?php echo ADMIN_FOLDER;?>css/admin.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_FOLDER;?>css/tabmenu.css" rel="stylesheet" type="text/css" />
<script language="JavaScript" type="text/javascript">
<!--
function checkAll(num,type) {
	var item = 'document.';
	switch (type) {
		case 'ctrl': item += 'fileform.selectfile';  break;
		case 'comm': item += 'commentform.comment';	break;
		case 'upld': item += 'uploadform.overwrite'; break;
		default: return;
	}
	for (var i = 0; i < num; i++) {
		var box = eval(item + i);
		if (box.checked == false) box.checked = true;
	}
}
function uncheckAll(num,type) {
	var item = 'document.';
	switch (type) {
		case 'ctrl': item += 'fileform.selectfile';  break;
		case 'comm': item += 'commentform.comment';	break;
		case 'upld': item += 'uploadform.overwrite'; break;
		default: return;
	}
	for (var i = 0; i < num; i++) {
		var box = eval(item + i);
		if (box.checked == true) box.checked = false;
	}
}
function confirmSubmit(num,type,action) {
	if (type == 'comm') {
		var item = "commentform['comment";
		if (action=='delete')
			var message = "<?php echo $mg2->lang['commentconfirm'];?>";
		else if (action=='lock')
			var message = "<?php echo $mg2->lang['lockcomments'];?>";
		else if (action=='unlock')
			var message = "<?php echo $mg2->lang['unlockcomments'];?>";
	}
	else {
		var item = "fileform['selectfile";
		if (action=='delete')
			var message = "<?php echo $mg2->lang['deleteconfirm'];?>";
		else if (action=='move')
			var message = "<?php echo $mg2->lang['moveconfirm'];?>";
	}

	for (i=0;i<num;i++) {
		if (eval("document." + item + i +"'].checked")) return confirm(message);
	}

	var error = (type == 'comm')?
		"<?php echo $mg2->lang['notice'] .' '. $mg2->lang['commentnotselected'];?>"
		:
		"<?php echo $mg2->lang['notice'] .' '. $mg2->lang['filenotselected'];?>";

	alert(error);
	return false;
}
function confirmRebuilt(folder) {
	var message	 = "<?php echo $mg2->lang['rebuildimages'];?>";
	if (folder)
		 message += " <?php echo strtolower($mg2->lang['from']);?> '" + folder + "'";

	return confirm(message);
}
-->
</script>
<?php
// INCLUDE FCKEditor (WYSIWYG)
if ((int)$_REQUEST['editID']			> 0	||		// edit image
	 (int)$_REQUEST['nextID']			> 0	||		// update image
	 (int)$_REQUEST['rotate']			> 0	||		// rotate image
	 (int)$_REQUEST['newfolder']		> 0	||		// newfolder
	 (int)$_REQUEST['editfolder']		> 0	||		// editfolder
	 (int)$_REQUEST['editComment']	> 0	||		// editcomment
	 $_REQUEST['action']==='updatefolder')			// updatefolder
{
	// INITIALIZE FCKEditor (WYSIWYG)
	$fckeditor_path = ADMIN_FOLDER .'wysiwyg/fckeditor.inc.php';
	if (($mg2->extendedset & 4) && is_readable($fckeditor_path)) {
		include($fckeditor_path);
	}

	// INITIALIZE DYNARCH CALENDAR
	$calendar_path = ADMIN_FOLDER .'calendar/calendar.inc.php';
	if (($mg2->extendedset & 16) && is_readable($calendar_path)) {
		include($calendar_path);
		$mg2->Calendar = new MG2Calendar(ADMIN_FOLDER .'calendar/',		// CALENDAR PATH
													substr($mg2->activelang,0,2),	// CALENDAR LANGUAGE
													'calendar_mg2',					// CALENDAR THEME
													$mg2->lang['calendar']);		// CALENDER TITLE

		// LOAD JAVASCRIPT AND THEME FILES
		$mg2->Calendar->load_files();
	}
}
?>
</head>

<body>
<?php
// DISLPAY TOOLTIPS, MANLY FOR MINI THUMBS
if ($mg2->extendedset & 8) {
	printf('<script type="text/javascript" src="%stooltip/wz_tooltip.js"></script>',
		ADMIN_FOLDER
	);
}
?>
