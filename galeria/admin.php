<?php

// DISPLAY ERRORS BUT HIDE NOTICES
error_reporting(E_ALL ^ E_NOTICE);

// SET PHP ARGUMENT SEPERATOR ON '&AMP'
@ini_set('arg_separator.output','&amp;');

// SESSION START, kh_mod 0.2.0, changed
session_start();

// DEFINE SCRIPT CONSTANS
define('GALLERY_ID',   'mg2'. (string)crc32(__FILE__));
define('INC_FOLDER',   dirname(__FILE__) .'/includes/');
define('DATA_FOLDER',  dirname(__FILE__) .'/data/');
define('LANG_FOLDER',  dirname(__FILE__) .'/lang/');
define('ADMIN_INDEX',  $_SERVER['PHP_SELF']);
define('ADMIN_FOLDER', './admin/');
define('ADDON_FOLDER', './skins/_global_/');
define('ADMIN_IMAGES', './admin/images/');
define('IMG_FOLDER', './admin/images/');			// at present unused!

// TRIGGER INSTALLATION
if (!is_file(DATA_FOLDER .'mg2db_settings.php')) {
	$message = 'Missed file \'mg2db_settings.php\'!';
	include (ADMIN_FOLDER.'fatal_error.php');
	exit();
}

// INSTANTIATE NEEDED MODULES
include(INC_FOLDER .'mg2_functions.php');
include(INC_FOLDER .'mg2admin_functions.php');
include(INC_FOLDER .'mg2admin_sqlmain.php');
include(INC_FOLDER .'mg2_comments.php');
include(INC_FOLDER .'mg2_counter.php');
include(INC_FOLDER .'mg2_splashes.php');
include(INC_FOLDER .'mg2_keywords.php');
$mg2 = new SQLadmin();

////////////////////////////////////////////////////////////////////////////////////////////////////
// ***************************************** SETTINGS *****************************************  //

// DEFAULT SETTINGS
$mg2->imagefolder	  = 'pictures';
$mg2->layoutsetting = 19;		 // 0, 0, 0, 0, 1, 0, 0, 1, 1
$mg2->extendedset	  = 28;		 // 0, 0, 0, 0, 1, 1, 1, 0, 0
$mg2->inactivetime  = 15;		 // max user inactivity in min
$mg2->sqldatabase   =  0;		 // use flat file, 1 use MySQL
$mg2->sqlprefix	  = 'mg2_';	 // prefix for database tables (at present unused!)
$mg2->charset		  = 'utf-8'; // nessesary for http header and mb_ functions

// LOAD FLAT FILE SETTINGS
include(DATA_FOLDER .'mg2db_settings.php');

// LOAD SETTINGS FROM SQL DATABASE
if ($mg2->sqldatabase) $mg2->read_sDBSQL(); // kh_mod 0.3.0, add

// RESTORE SETTINGS, kh_mod 0.3.0, add
if ($_POST['action'] === 'useBackup') {
	if (isset($_POST['use_settings'])) {
		$time_settings = substr($_POST['settings'],0,10);
		$path_settings = DATA_FOLDER . $time_settings. '_mg2db_settings.php';
		if (preg_match('/^[0-9]{10}$/', $time_settings) && is_file($path_settings))
			$mg2->settingsOK = include($path_settings);
		else
			$mg2->settingsOK = false;
	}
}

// NEW SETTINGS, kh_mod 0.3.0, changed
elseif ($_POST['action'] === 'writesetup') {
	$mg2->gallerytitle = $mg2->charfix($_POST['gallerytitle']);
	$regexp  = '°^[a-z]{2}.?[a-z]{2}$°i';
	$newlang = $_POST['defaultlang'];
	if (@preg_match($regexp, $newlang)) $mg2->defaultlang = $newlang;
}

// ************************************** END OF SETTINGS *************************************  //
//////////////////////////////////////////////////////////////////////////////////////////////////////

// INCLUDE LANGUAGE FILE, kh_mod 0.3.0, changed
if ($lang_path = $mg2->checkLanguage($mg2->defaultlang)) {
	include sprintf($lang_path, 'gallery');
	include sprintf($lang_path, 'admin');
}
elseif ($lang_path = $mg2->checkLanguage('en_US')) {
	$mg2->defaultlang = 'en_US';
	include sprintf($lang_path, 'gallery');
	include sprintf($lang_path, 'admin');
}

// SET USED LOCAL CHARSET (WEB SERVER), kh_mod 0.3.0 add
if ((int)$mg2->extendedset & 64) {
	$regexp = '/^([a-z]{2}).?([A-Z]{2})$/';
	if (preg_match($regexp, $mg2->defaultlang, $item)) {
		@setlocale(LC_CTYPE, sprintf('%s_%s.%s', $item[1], $item[2], $mg2->charset));
	}
}

// SET HEADERS TO PREVENT BROWSER CACHING OF PAGES
@header('Expires: Mon, 20 Jul 2000 05:00:00 GMT');
@header('Last-Modified: '. gmdate('D, d M Y H:i:s') .' GMT');
@header('Content-Type: text/html; charset='.$mg2->charset);
@header('Content-Script-Type: text/javascript');
@header('Content-Style-Type: text/css');
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Cache-Control: post-check=0, pre-check=0', false);
@header('Pragma: no-cache');

// GET PAGE TITLE, changed kh_mod 0.3.0
$pagetitle = @htmlspecialchars(strip_tags($mg2->gallerytitle));

// LOGIN SECURITY CHECK, kh_mod 0.1.0pl1, changed
$firstlogin = $mg2->security($pagetitle);

// HTML-HEADER, changed kh_mod 0.3.0
include(ADMIN_FOLDER .'admin_header.php');

// READ DATABASE FOR ADMIN, kh_mod 0.2.0, changed
$mg2->readDB();

//////////////////////////////////////////////////////////////////////////////////////////////////////
// ************************************* START MAIN MENU *************************************** //

// GET FOLDER ID FOR MENU
if (!empty($_REQUEST['fID'])) {
	$folderID = (int)$_REQUEST['fID'];			// action
}
elseif (!empty($_REQUEST['editfolder'])) {	// display
	$folderID = (int)$_REQUEST['editfolder'];
}
elseif (!empty($_REQUEST['newfolder'])) {		// display
	$folderID = (int)$_REQUEST['newfolder'];
}
if ($folderID < 1) $folderID = 1;

// GET FOLDER PAGE FOR MENU
$page = ($_REQUEST['page']==='all')?
		  'all'
		  :
		  max((int)$_REQUEST['page'], 1);

// CALCULATE IMAGES SIZE, kh_mod 0.3.0, changed
$total_size = 0;
if (is_array($mg2->all_images)) {
	foreach ($mg2->all_images as $item) { $total_size += (int)$item[12]; }
}
$total_size = $mg2->convertBytes($total_size);

// CALCULATE IMAGES NUMBER
$total_images = count($mg2->all_images);
$total_images.= ' '. (($total_images === 1)? $mg2->lang['image']:$mg2->lang['images']);

// CALCULATE FOLDERS NUMBER
$total_folders = count($mg2->all_folders); if ($total_folders > 0) $total_folders--;
$total_folders.= ' '. (($total_folders === 1)? $mg2->lang['folder']:$mg2->lang['folders']);
$total_folders.= ' + '. $mg2->lang['root'];

// INCLUDE MENU SKIN
include(ADMIN_FOLDER .'admin1_menu.php');

// ************************************* END OF MAIN MENU ************************************** //
///////////////////////////////////////////////////////////////////////////////////////////////////////

// LANGUAGE INSTALLED?
if (empty($mg2->lang)) {
	$mg2->displaystatus('There is no language intsalled!', 3);
}

// FIRST VIEW AFTER LOGIN
if ($firstlogin) {

	// USED SKIN NOT AVAILABLE OR UP TO DATE
	$skinpath = 'skins/'. $mg2->activeskin .'/';
	if (!is_readable($skinpath .'templates')) {
		$_REQUEST['action'] = 'setup';
		$mg2->displaystatus(sprintf('The skin "%s" is not available! Please select another one.',
									  ucfirst($mg2->activeskin)
								 ), 3);
	}
	elseif (is_readable($skinpath .'settings.php')) {
		include ($skinpath .'settings.php');
		$found = preg_match('/kh_mod\D*([0-9]\.[0-9]\.[0-9])/',$skin_version,$treffer);
		if (!$found || version_compare($treffer[1], '0.4.0', '<')) {
			$display = ($skin_version)? '('. htmlspecialchars(trim($skin_version)) .')':'';
			$mg2->displaystatus(sprintf($mg2->lang['notuptodate_skin'],
										  $display,
										  ucfirst($mg2->activeskin)
									 ), 2);
		}
	}

	// MEMORY LIMIT
	$mg2->getMemoryStatus();
	if ($mg2->currentMemory['allocate'] > 0 && $mg2->currentMemory['limit'] > 0) {
		$allocate = $mg2->currentMemory['allocate']/$mg2->currentMemory['limit'] * 100;
		$message  = sprintf($mg2->lang['memoryallocated'],
							sprintf('<span title="%s">%s</span>',
								$mg2->convertBytes($mg2->currentMemory['allocate']),
								number_format($allocate, 1, $mg2->lang['decimalsign'], '')
							),
							'<a href="'.ADMIN_INDEX.'?display=setup&amp;tab=3">...</a>'
						);
		$mg2->displaystatus($message, ($allocate < 90) ? 1:2);
	}
}

// UPLOAD FILES ERROR?
if (isset($_REQUEST['loading']) && $_REQUEST['action'] !== 'upload') {
	$max_upload = get_cfg_var('upload_max_filesize');
	$mg2->displaystatus(sprintf($mg2->lang['uploadfiles_gt'],
								  preg_replace('/(\d+)M$/','${1} MByte', $max_upload)
							 ), 2);
}

// 'ACTION' CONTROLLER
if (isset($_REQUEST['action']))
switch ($_REQUEST['action']) {
	case 'savesorting':
		$folderID = (int)$_POST['editfolder'];
		$mg2->setSortingPositions($folderID);
		break;
	case 'updateID':
		$imageID = (int)$_POST['iID'];
		if ($_REQUEST['fID'] = $mg2->updateID($imageID)) {
			$nextID = (int)$_POST['nextID'];
			$_REQUEST['editID'] = ($nextID > 0)? $nextID:NULL;
		} else
			$_REQUEST['editID'] = $imageID;
		break;
	case 'deleteID':
		$_REQUEST['fID'] = $mg2->deleteID($_REQUEST['iID']);
		break;
	case 'upload':
		$mg2->getMemoryStatus();
		if ($mg2->upload($folderID)) {
			include(ADMIN_FOLDER ."admin4_credits.php");
			exit();
		}
		break;
	case 'import':
		$mg2->getMemoryStatus();
		if ($imported = $mg2->importstart($folderID)) {
			include(ADMIN_FOLDER ."admin4_credits.php");
			exit();
		}
		elseif ($imported === false)
			$_REQUEST['fID'] = 1;
		break;
	case 'newfolder':
		$mg2->newfolder($folderID);
		break;
	case 'updatefolder':
		$folderID = (int)$_REQUEST['fID'];
		$mg2->updatefolder($folderID);
		$_REQUEST['editfolder'] = $folderID;
		break;
	case 'admincomments':
		$imageID  = (int)$_REQUEST['editID'];
		$Comments = $mg2->getInstance('MG2Comments');
		$mg2->$Comments->adminCommentAction($imageID);
		break;
	case 'writesetup':
		$mg2->preparesetup();
		break;
	case 'switchdb':	// database switch
		$db = substr($_POST['database'],0,11);
		$mg2->switchDatabase($db);
		break;
	case 'makeBackup':
		$mg2->backup_DB();
		break;
	case 'useBackup':
		if (isset($_POST['restBackup']))
			$mg2->restoreBackup();
		elseif (isset($_POST['delBackup']))
			$mg2->deleteBackup();
		break;
	case 'convert':	// database convert
		include(INC_FOLDER .'mg2admin_convert.php');
		if ($_POST['item'] === 'clickDB') convert_clickDB();
		elseif ($_POST['item'] === 'iDB') {
			convert_iDB();
		}
		elseif ($_POST['item'] === 'fDB') {
			convert_fDB();
		}
		elseif ($_POST['item'] === 'cDB') convert_cDB();
		else	 $mg2->displaystatus('No items to convert!');
}
// END OF 'ACTION' CONTROLLER

if (isset($_REQUEST['erasefolder'])) {
	$_REQUEST['fID'] = $mg2->erasefolder((int)$_REQUEST['erasefolder']);
}

// UPDATE COMMENT, ACTION, kh_mod 0.1.0, add; 0.4.0 b3 changed
if (isset($_REQUEST['updateComment'])) {
	$Comments		 = $mg2->getInstance('MG2Comments');
	$updatedComment = $mg2->$Comments->updateComment(
														(int)$_REQUEST['editID'],
														(int)$_REQUEST['updateComment']
												  );
	// BACK TO 'EDIT COMMENT' DIALOG
	if ($updatedComment===false) {
		$_REQUEST['iID']			 = $_REQUEST['editID'];
		$_REQUEST['editComment'] = $_REQUEST['updateComment'];
	}
}

//
// DISPLAY DIALOGS
//

// 'DISPLAY' CONTROLLER
if (isset($_REQUEST['display']))
switch ($_REQUEST['display']) {
	case 'setup':
		$mg2->setup($folderID, $page);
		include(ADMIN_FOLDER .'admin4_credits.php');
		exit();
}
// END OF 'DISPLAY' CONTROLLER

// 'EDIT COMMENT' DIALOG, kh_mod 0.1.0, add; 0.4.0 b3 changed
if (isset($_REQUEST['editComment'])) {
	$Comments	 = $mg2->getInstance('MG2Comments');
	$editComment = $mg2->$Comments->editComment(
													(int)$_REQUEST['iID'],
													(int)$_REQUEST['editComment']
											  );
	// IF 'EDIT COMMENT' DIALOG OK, THEN EXIT
	if ($editComment === true) {
		include(ADMIN_FOLDER .'admin4_credits.php');
		exit();
	}
	// BACK TO 'EDIT IMAGE' DIALOG
	else {
		$_REQUEST['editID'] = $_REQUEST['iID'];
	}
}

// 'DELETE COMMENT' DIALOG, kh_mod 0.1.0, add; 0.4.0 b3 changed
if (isset($_REQUEST['askDelComment'])) {
	$Comments = $mg2->getInstance('MG2Comments');
	$askDelOK = $mg2->$Comments->askDelComment(
												(int)$_REQUEST['iID'],
												(int)$_REQUEST['askDelComment']
										  );
	// IF 'ASK DELETE COMMENT' DIALOG OK, THEN EXIT
	if ($askDelOK === true) {
		include(ADMIN_FOLDER .'admin4_credits.php');
		exit();
	}
}

// DELETE FILES, kh_mod 0.1.0 b3, changed
if (isset($_REQUEST['deletefiles'])) {
	$del_request = html_entity_decode($_REQUEST['deletefiles']);
	$del_value	 = html_entity_decode($mg2->lang['buttondelete']);
	if ($del_request === $del_value) $mg2->deletefiles();
}

// MOVE FILES, ACTION, kh_mod 0.1.0 b3, changed
if (isset($_REQUEST['movefiles'])) {
	$move_request = html_entity_decode($_REQUEST['movefiles']);
	$move_value	  = html_entity_decode($mg2->lang['buttonmove']);
	if ($move_request === $move_value) $mg2->movefiles();
}

// UPLOAD IMAGES, DIALOG, kh_mod 0.3.1, changed
if(!empty($_REQUEST['startupload'])) {
	$mg2->makeFolderlist();
	$folderID  = (int)$_REQUEST['startupload'];
	$subdirs   = $mg2->get_subdirs();
	$explorer  = $mg2->get_tree($subdirs);
	$marker	  = 'checked="checked"';
	$url2setup = ADMIN_INDEX .'?display=setup&amp;fID='. $folderID .'&amp;page=' .$page;
	if (isset($_SESSION[GALLERY_ID]['import_modify'])) {
		$checked['thumb']	 = ($_SESSION[GALLERY_ID]['import_modify'] & 1)? $marker:'';
		$checked['medium'] = ($_SESSION[GALLERY_ID]['import_modify'] & 2)? $marker:'';
		$checked['delete'] = ($_SESSION[GALLERY_ID]['import_modify'] & 4)? $marker:'';
	} else {
		$checked['thumb']  = $marker;
		$checked['medium'] = $marker;
		$checked['delete'] = '';
	}
	include(ADMIN_FOLDER .'admin2_upload.php');
	include(ADMIN_FOLDER .'admin4_credits.php');
	exit();
}

// IMPORT IMAGES, DIALOG, kh_mod 0.3.1, changed
if(!empty($_REQUEST['startimport'])) {
	$mg2->makeFolderlist();
	$_REQUEST['fID'] = $folderID = (int)$_REQUEST['startimport']; // like line 463
	$subdirs   = $mg2->get_subdirs();
	$explorer  = $mg2->get_tree($subdirs);
	$url2setup = ADMIN_INDEX .'?display=setup&amp;fID='. $folderID .'&amp;page=' .$page;
	$marker	  = 'checked="checked"';
	if (isset($_SESSION[GALLERY_ID]['import_modify'])) {
		$checked['thumb']	 = ($_SESSION[GALLERY_ID]['import_modify'] & 1)? $marker:'';
		$checked['medium'] = ($_SESSION[GALLERY_ID]['import_modify'] & 2)? $marker:'';
		$checked['delete'] = ($_SESSION[GALLERY_ID]['import_modify'] & 4)? $marker:'';
	} else {
		$checked['thumb']  = $marker;
		$checked['medium'] = $marker;
		$checked['delete'] = '';
	}
	include(ADMIN_FOLDER .'admin2_import.php');
}

// MAKE A NEW FOLDER, DIALOG, kh_mod 0.1.0, add
if(!empty($_REQUEST['newfolder'])) {
	$_REQUEST['fID'] = (int)$_REQUEST['newfolder'];
	include(ADMIN_FOLDER .'admin2_newfolder.php');
}

// EDIT FOLDER, DIALOG
if (!empty($_REQUEST['editfolder'])) {
	$_REQUEST['fID'] = $mg2->editfolder($folderID, $page);
}

// REBUILD FOLDER, ACTION, kh_mod 0.2.0, changed
if(!empty($_REQUEST['rebuildfolder'])) {
	$mg2->getMemoryStatus();
	$reblist = (int)$_REQUEST['rebuildfolder'];
	if ($mg2->rebuildfolder($reblist) > 0)
		$_REQUEST['fID'] = $reblist;
}

// REBUILD THUMB AND MEDIUM IMAGE, kh_mod 0.2.0, changed
if (!empty($_GET['rebuildID'])) {
	$mg2->getMemoryStatus();
	$imageID = (int)$_REQUEST['rebuildID'];
	if ($update = $mg2->rebuildID($imageID)) {
		$_REQUEST['fID'] = $mg2->all_images[$imageID][1];

		// UPDATE IMAGE DATABASE, kh_mod 0.3.0, changed
		if (!$mg2->write_iDB('upd', array($imageID)))
			$mg2->log('ERROR: Couldn\'t update image database!');
	}
}

// DELETE IMAGE, DIALOG
if (!empty($_REQUEST['deleteID'])) {
	$_REQUEST['fID'] = $mg2->askdeleteID((int)$_REQUEST['deleteID'], $page);
}

// DELETE FOLDER, DIALOG
if (!empty($_REQUEST['deletefolder'])) {
	$delfolder = (int)$_REQUEST['deletefolder'];
	if (!isset($_REQUEST['fID'])) {
		$_REQUEST['fID'] = $folderID = $delfolder; // like line 405
	}
	$mg2->askdelfolder($delfolder, $folderID, $page);
}

if (empty($_GET['isort']) || !include(INC_FOLDER .'mg2admin_sortview.php')) {
	include(INC_FOLDER .'mg2admin_tableview.php');
}
include(ADMIN_FOLDER .'admin4_credits.php');
?>
