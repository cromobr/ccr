<?php

// DISPLAY ERRORS BUT HIDE NOTICES
// error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);

//SET PHP ARGUMENT SEPERATOR ON '&AMP'
@ini_set('arg_separator.output','&amp;');

session_start();

//
// DEFINE SCRIPT CONSTANS
// kh_mod 0.2.0, add
define('GALLERY_ID',	  'mg2'. (string)crc32(dirname(__FILE__)));
define('INC_FOLDER',   dirname(__FILE__) .'/includes/');
define('DATA_FOLDER',  dirname(__FILE__) .'/data/');
define('LANG_FOLDER',  dirname(__FILE__) .'/lang/');
define('ADDON_FOLDER', './skins/_global_/');

//
// LOGOUT ALL FOLDERS (GALLERY)
// kh_mod 0.1.0, add
$logoutmsg = '';
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'logout') {
	unset($_SESSION[GALLERY_ID]['folderpwd']);
	$logoutmsg = true;
}

// TRIGGER INSTALLATION
if (!is_file(DATA_FOLDER .'mg2db_settings.php')) {
	$message = 'The MG2/kh_mod installation is invalid!';
	@include (is_file('mg2_install.php'))?
		'mg2_install.php'
		:
		'admin/fatal_error.php';
	exit();
}

// MAKE NEW DATABASE OBJECT
include(INC_FOLDER .'mg2_functions.php');
include(INC_FOLDER .'mg2_comments.php');
include(INC_FOLDER .'mg2_counter.php');
include(INC_FOLDER .'mg2_splashes.php');
$mg2 = new mg2db;

// BASIC SETTINGS
$mg2->imagefolder	  = 'pictures';
$mg2->layoutsetting =  19;		 // 0, 0, 0, 0, 1, 0, 0, 1, 1
$mg2->extendedset	  =  28;		 // 0, 0, 0, 0, 1, 1, 1, 0, 0
$mg2->commentsets	  = 159;		 // 0, 1, 0, 0, 1, 1, 1, 1, 1
$mg2->showexif		  =   0;		 // displayed exif values
$mg2->sqldatabase   =   0;		 // use flat file, 1 use MySQL
$mg2->sqlprefix	  = 'mg2_';	 // prefix for database tables (at present unused!)
$mg2->charset		  = 'utf-8'; // nessesary for mb_ functions

// READ SETTINGS
@include(DATA_FOLDER .'mg2db_settings.php');
if ($mg2->sqldatabase) $mg2->read_sDBSQL(); // kh_mod 0.3.0, add

//
// CHECK LANGUAGE
// kh_mod 0.1.0 rc1, 0.3.0 changed
$gallerylang = $_SESSION[GALLERY_ID]['gallerylang'];
if (isset($_GET['ln']) && strlen($_GET['ln']) === 2) {
	switch ($_GET['ln']) {
		// ISO 639-1, ISO 3166-1 ALPHA-2
		case 'cs': $gallerylang = 'cs_CZ';	break;
		case 'da': $gallerylang = 'da_DK';	break;
		case 'de': $gallerylang = 'de_DE';	break;
		case 'el': $gallerylang = 'el_GR';	break;
		case 'en': $gallerylang = 'en_US';	break;
		case 'fr': $gallerylang = 'fr_FR';	break;
		case 'ja': $gallerylang = 'ja_JP';	break;
		case 'lt': $gallerylang = 'lt_LT';	break;
		case 'pl': $gallerylang = 'pl_PL';	break;
		case 'ro': $gallerylang = 'ro_RO';	break;
		case 'es': $gallerylang = 'es_ES';	break;
		case 'sr': $gallerylang = 'sr_RS';	break;
		case 'sv': $gallerylang = 'sv_SE';	break;
	}
}

// INCLUDE LANGUAGE, , kh_mod 0.3.0 changed
do {
	if (!$lang_path = $mg2->checkLanguage($gallerylang)) {
		$gallerylang = $_SESSION[GALLERY_ID]['gallerylang'];
		if (!$lang_path = $mg2->checkLanguage($gallerylang)) {
			$gallerylang = $mg2->defaultlang;
			if (!$lang_path = $mg2->checkLanguage($gallerylang))	{
				$gallerylang = 'en_US';
				if (!$lang_path = $mg2->checkLanguage($gallerylang)) {
					$mg2->status = sprintf('The language "%s" is not available!',
											substr($mg2->defaultlang,0,5)
										);
					break;
				}
			}
		}
	}
	@include sprintf($lang_path, 'gallery');
	$_SESSION[GALLERY_ID]['gallerylang'] = $gallerylang;

	// SET THE WEB SERVER TO LANG FILE CHARSET
	if ((int)$mg2->extendedset & 64) {
		$regexp = '/^([a-z]{2}).?([A-Z]{2})$/';
		if (preg_match($regexp, $gallerylang, $item)) {
			@setlocale(LC_CTYPE, sprintf('%s_%s.%s', $item[1], $item[2], $mg2->charset));
		}
	}
}
while(0);

// X-ROBOTS-TAG
$x_robots = array();
if ($mg2->metasetting & 1024) $x_robots[]	= 'noindex';
if ($mg2->metasetting & 2048) $x_robots[]	= 'nofollow';
if ($mg2->metasetting & 4096) $x_robots[]	= 'noarchive';
if ($mg2->metasetting & 8192) $x_robots[]	= 'nosnippet';
if ($mg2->metasetting & 15360) @header('X-Robots-Tag: '. implode(', ', $x_robots));

// SET ANOTHER HTTP HEADERS
@header('Content-Type: text/html; charset='.$mg2->charset);
@header('Content-Script-Type: text/javascript');
@header('Content-Style-Type: text/css');

//
// SKIN SETTINGS
//  kh_mod 0.2.0, changed
$skinpath = 'skins/'. $mg2->activeskin .'/';
if (!is_readable($skinpath .'templates')) {
	$message = (empty($mg2->activeskin))?
				  'There isn\'t any installed skin!'
				  :
				  'The skin "'. ucfirst($mg2->activeskin) .'" is not available!';
	include('admin/fatal_error.php');
	exit();
}
if (is_readable($skinpath .'settings.php')) {
	include($skinpath .'settings.php');
}

//
// READ DATABASE FOR GALLERY
//  kh_mod 0.2.0, changed
$mg2->readDB();

//
// FOR THE LINK COMPATIBILITY WITH MG2 AND kh_mod 0.1.0
// kh_mod 0.2.1, add
if (!isset($_REQUEST['iID']) && !isset($_REQUEST['fID'])) {
	$_REQUEST['iID'] = $_REQUEST['id'];
	$_REQUEST['fID'] = $_REQUEST['list'];
}

//
// GET IMAGE ID AND RELATED FOLDER ID
// kh_mod 0.1.0, add, 0.3.1 changed
$imageRC	 = false;
$imageID	 = false;
$folderID = 1;
do {
	// SLIDESHOW
	if (!empty($_REQUEST['slideshow'])) {
		$imageID = (int)$_REQUEST['slideshow'];
		$imageRC = $mg2->all_images[$imageID];
		if (isset($imageRC[1])) {
			$folderID = (int)$imageRC[1];
			define('SLIDESHOW', true);
			break;
		}	
	}

	// IMAGEVIEW
	if (!empty($_REQUEST['iID'])) {
		$imageID = (int)$_REQUEST['iID'];
		$imageRC = $mg2->all_images[$imageID];
		if (isset($imageRC[1])) {
			$folderID = (int)$imageRC[1];
			define('IMAGEVIEW', true);
			break;
		}
	}

	// REMOTEVIEW
	if (!empty($_GET['rID'])) {
		$remoteID = (int)$_GET['rID'];
		$imageID  = (int)$_SESSION[GALLERY_ID]['remoteID'][$remoteID];
		$imageRC  = $mg2->all_images[$imageID];
		if (isset($imageRC[1])) {
			$folderID = (int)$imageRC[1];
			define('IMAGEVIEW', true);
		}
	}
}
while(0);

//
// CURRENT PAGE OF THUMBNAILS
// kh_mod 0.3.0, changed
$currentPage = ($_GET['page'] === 'all')?
					'all'
					:
					max((int)$_GET['page'], 1);

//
// CHECK IMAGE ID
// kh_mod 0.3.2, changed
if ($imageID)
{
	if (!defined('SLIDESHOW')	&&
		 !defined('IMAGEVIEW'))
	{
		notexists($imageID);					// image id not exists
	}
}
elseif (isset($_REQUEST['fID']))
{
	$folderID = (int)$_REQUEST['fID'];	// get requested folder
}

//
// SET METATAGS TITLE AND ROBOTS
// kh_mod 0.2.0, add, 0.3.0 changed
$mg2->pagetitle = $mg2->getPagetitle($imageID, $folderID, ' - ');
$mg2->robots	 = array();
$mg2->googlebot = '';
if ($mg2->metasetting &  16) $mg2->robots[]	= 'noindex';
if ($mg2->metasetting &  32) $mg2->robots[]	= 'index';
if ($mg2->metasetting &  64) $mg2->robots[]	= 'nofollow';
if ($mg2->metasetting & 128) $mg2->robots[]	= 'follow';
if ($mg2->metasetting & 256) $mg2->robots[]	= 'noarchive';
if ($mg2->metasetting & 512) $mg2->googlebot	= 'nosnippet';

//
// ADMIN MODE
// kh_mod 0.3.0 rc1, add
if (isset($_GET['user'])) {
	if (($mg2->extendedset & 128)								&&
		 !empty($_SESSION[$_GET['user']]['adminpwd'])	&&
		 $_SESSION[$_GET['user']]['adminpwd'] === $mg2->adminpwd
		)
	// SET ADMIN MODE
	{
		$_SESSION[GALLERY_ID]['adminmode'] = $mg2->adminpwd;
	}
	// DELETE ADMIN MODE
	elseif (isset($_SESSION[GALLERY_ID]['adminmode']))
	{
		unset($_SESSION[GALLERY_ID]['adminmode']);
		$logoutmsg = true;
	}
}

//
// GALLERY SECURITY
// kh_mod 0.2.0, changed
$mg2->gallerysecurity($folderID, $imageID);

//
// GET FOLDER SETTINGS AND CHECK IF IT EXISTS
//
$mg2->getFolderSettings($folderID);

/////////////////////////////
//	DISPLAY IMAGES	//
////////////////////////////
if (defined('SLIDESHOW')) {
	// SLIDESHOW
	include (INC_FOLDER .'mg2_slideshow.php');
}
elseif (defined('IMAGEVIEW')) {
	// DISPLAY IMAGE
	include (INC_FOLDER .'mg2_viewimage.php');
}
else {
	// INDEX (THUMBS)
	include (INC_FOLDER .'mg2_viewfolder.php');
}
?>
