<?php
// SET HEADERS TO PREVENT BROWSER CACHING OF PAGES
@header('Content-Type: text/html; charset=utf-8');
@header('Expires: Mon, 20 Jul 2000 05:00:00 GMT');
@header('Last-Modified: '. gmdate('D, d M Y H:i:s') .' GMT');
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Cache-Control: post-check=0, pre-check=0', false);
@header('Pragma: no-cache');

// DISPLAY ADMIN HEADER
define('INC_FOLDER',   dirname(__FILE__) .'/includes/');
define('DATA_FOLDER',  dirname(__FILE__) .'/data/');
define('ADMIN_FOLDER', 'admin/');
define('ADMIN_IMAGES', './admin/images/');

// MOD VERSION (SCRIPT)

// INCLUDE INSTALL HEAD AND PAGE TITLE
$charset   = 'UTF-8';
include(ADMIN_FOLDER .'install_header.inc.php');

$step = (is_file(DATA_FOLDER .'mg2db_settings.php'))? -1:(int)$_REQUEST['step'];

//
// STEP -1 (MG2/kh_mod already installed!)
//
if($step === -1) {
echo '
	<table class="table_menu" cellpadding="0" cellspacing="0">
	<tr valign="top">
		<td align="center" colspan="2">
			<p><strong>Galeria já instalada!</strong></p>
			<p>A instalação não pode ser feita, o sistema já está configurado.</p>
			<a href="index.php">Ver galeria</a>
		</td>
	</tr>
	';
}

//
// STEP 1
//
elseif($step === 0) {
	$failure = '<span class="install_failure">Erro</span>';
	$success = '<span class="install_success">Successo</span>';
	$todo = '';

	// TEST 1
	@rmdir(DATA_FOLDER .'x');
	if (@mkdir(DATA_FOLDER .'x')) {
		$test1 = $success .'<br />';
		@rmdir(DATA_FOLDER .'x');
	} else {
		$test1 = $failure;
		$todo.= '- Grant the script write access to the gallery folder \''. DATA_FOLDER .'\' (CHMOD 777) - <a href="http://www.google.com/search?hl=en&q=chmod+tutorial&meta=" target="_blank">help!</a><br />';
	}

	// TEST 2
	@rmdir('pictures/x');
	if (@mkdir('pictures/x')) {
		$test2 = $success;
		@rmdir('pictures/x');
	} else {
		$test2 = $failure;
		$todo.= '- Create folder called \'pictures\' in gallery root using FTP and chmod to 777<br />';
	}

	// TEST 3
	$test3 = $success;
	$main_file1 = 'index.php';
	$main_file2 = 'admin.php';
	$main_file3 = 'lang/en_US/en_US.gallery.php';
	$main_file4 = INC_FOLDER .'mg2_viewimage.php';
	$main_file5 = INC_FOLDER .'mg2_viewfolder.php';
	$main_file6 = INC_FOLDER .'mg2_slideshow.php';
	$main_file7 = INC_FOLDER .'mg2admin_sortview.php';
	$main_file8 = INC_FOLDER .'mg2admin_tableview.php';
	if (!is_readable($main_file1)) $test3 = $failure;
	if (!is_readable($main_file2)) $test3 = $failure;
	if (!glob($main_file3))        $test3 = $failure;
	if (!is_readable($main_file4)) $test3 = $failure;
	if (!is_readable($main_file5)) $test3 = $failure;
	if (!is_readable($main_file6)) $test3 = $failure;
	if (!is_readable($main_file7)) $test3 = $failure;
	if (!is_readable($main_file8)) $test3 = $failure;

	// TEST 4
	$test4 = $success;
	$class_file1 = INC_FOLDER .'mg2_functions.php';
	$class_file2 = INC_FOLDER .'mg2admin_functions.php';
	if (!is_readable($class_file1)) $test4 = $failure;
	if (!is_readable($class_file2)) $test4 = $failure;

	if ($test3 != $success) {
		$todo.= '- Upload all gallery files<br />';
	}
	elseif ($test4 != $success) {
		$todo.= '- Upload files \''.$class_file1.'\' and  \''.$class_file2.'\'<br />';
	}

	// TEST 5
	$test5 = '<span class="install_failure">?</span>';
	if ($test4 == $success) {
		include_once($class_file1);
		$mg2 = new mg2db;
		// NEEDED GD VERSION 2.0.1
		if ($mg2->gd_version() < 2) {
			$test5 = $failure ." ($mg2->gd_version_number)";
			$todo.= '- Install GD image library version 2.0.1 or newer ';
			$todo.= '(<a href="http://www.boutell.com/gd/" target="_blank">';
			$todo.= 'http://www.boutell.com/gd/</a>)<br />';
		}
		else {
			$test5 = $success ." ($mg2->gd_version_number)";
		}
	}

	// TEST 6
	$phpvers	= phpversion();
	$order	= '- Install PHP version 4.3.0 or newer ';
	$order  .= '(<a href="http://www.php.net/downloads.php" target="_blank">';
	$order  .= 'http://www.php.net/</a>)<br />';
	if (!function_exists('version_compare')) {
		$test6 = $failure ." ($phpvers)";
		$todo	.= $order;
	}
	elseif (version_compare($phpvers,'4.3.0','<')) {
		$test6 = $failure ." ($phpvers)";
		$todo	.= $order;
	}
	else $test6 = $success ." ($phpvers)";

	include(ADMIN_FOLDER .'install_main.inc.php');
}

//
// STEP 2
//
elseif($step === 2) {
	include_once(INC_FOLDER .'mg2_functions.php');
	$mg2 = new mg2db;

	// GET LANGUAGES
	$defaultlang = 'en_US';
	$exists		 = false;
	$lang			 = array();
	$regexp		 = '/^[a-z]{2}.[A-Z]{2}$/';
	$workdir		 = opendir('lang');
	while (false !== ($pointer = readdir($workdir))) {
		if ($pointer{0} === '.')				continue;
		if (!is_dir('lang/'. $pointer))		continue;
		if (!preg_match($regexp, $pointer))	continue;

		$value = 'value="'.$pointer.'"';
		if ($defaultlang === $pointer) {
			$value .= ' selected="selected"';
			$exists = true;
		}
		$lang[] = array($value, $pointer);
	}
	closedir($workdir);
	if (!$exists) $lang[] = array('value="" selected="selected"','--');
	sort($lang);

	if ($lang_path = $mg2->checkLanguage($defaultlang)) {
		include sprintf($lang_path, 'admin');
	}
	else {
		$mg2->lang['gallerytitle']	= 'Gallery title';
		$mg2->lang['adminemail']	= 'Admin email';
		$mg2->lang['language']		= 'Language';
		$mg2->lang['skin']			= 'Skin';
		$mg2->lang['password']		= 'Password';
	}

	// SKIN
	$skins	= array();
	$workdir	= opendir('skins');
	while (false !== ($pointer = readdir($workdir))) {
		if ($pointer{0} === '.')			continue;
		if ($pointer 	 === '_global_')	continue;
		$skins[] = $pointer;
	}
	closedir($workdir);
	sort($skins);

	include(ADMIN_FOLDER .'install_main.inc.php');
}

//
// STEP 3
//
elseif($step === 3) {
	include_once(INC_FOLDER .'mg2_functions.php');
	$mg2 = new mg2db;

	$_POST['gallerytitle'] = $mg2->charfix($_POST['gallerytitle']);
	$_POST['password']	  = trim($_POST['password']);
	$bufferpwd = md5(strrev(md5($_POST["password"])));

	$filebuffer = "<?php\n";
	$filebuffer.= '$mg2->gallerytitle = '.chr(34).$_POST['gallerytitle'].chr(34).";\n";
	$filebuffer.= '$mg2->adminemail = '.chr(34).$_POST['adminemail'].chr(34).";\n";
	$filebuffer.= '$mg2->metasetting = 81'.";\n";													// kh_mod 0.2.0, add
	$filebuffer.= '$mg2->defaultlang = '.chr(34).$_POST['defaultlang'].chr(34).";\n";
	$filebuffer.= '$mg2->activeskin = '.chr(34).$_POST['activeskin'].chr(34).";\n";		// kh_mod 0.1.0, changed
	$filebuffer.= '$mg2->dateformat = '.chr(34)."%d.%m.%Y".chr(34).";\n";					// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->timeformat = '.chr(34)."%H:%M".chr(34).";\n";						// kh_mod 0.3.0, add
	$filebuffer.= '$mg2->navtype = 1'.";\n";															// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->showexif = 510'.";\n";														// kh_mod 0.2.0, changed
	$filebuffer.= '$mg2->commentsets = 159'.";\n";													// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->marknew = 7'.";\n";															// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->copyright = '.chr(34)."Copyright &#169; 2009".chr(34).";\n";	// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->adminpwd = '.chr(34).$bufferpwd.chr(34).";\n";
	$filebuffer.= '$mg2->extensions = '.chr(34)."jpeg,jpg,gif,png,flv,mov,mp4,mp3".chr(34).";\n";
	$filebuffer.= '$mg2->introwidth = '.chr(34)."0".chr(34).";\n";								// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->mediumimage = '.chr(34)."700".chr(34).";\n";							// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->indexfile = '.chr(34)."index.php".chr(34).";\n";
	$filebuffer.= '$mg2->imagefolder = '.chr(34)."pictures".chr(34).";\n";					// kh_mod 0.1.0, add
	$filebuffer.= '$mg2->foldersetting = 544'.";\n";												// kh_mod 0.2.0, add
	$filebuffer.= '$mg2->layoutsetting = 19'.";\n";													// kh_mod 0.3.0, add
	$filebuffer.= '$mg2->thumbquality = 85'.";\n";													// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->thumbMaxWidth = 150'.";\n";												// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->thumbMaxHeight = 150'.";\n";												// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->imagecols = 4'.";\n";														// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->imagerows = 6'.";\n";														// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->slideshowdelay = 8'.";\n";													// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->websitelink = '.chr(34).chr(34).";\n";
	$filebuffer.= '$mg2->websitetext = '.chr(34)."Home".chr(34).";\n";						// kh_mod 0.1.0, add
	$filebuffer.= '$mg2->inactivetime = 15'.";\n";													// kh_mod 0.3.0, changed
	$filebuffer.= '$mg2->extendedset = 220'.";\n";													// kh_mod 0.3.2, changed
	$filebuffer.= '$mg2->modversion = '.chr(34). $install_version .chr(34).";\n";			// kh_mod 0.3.0, add
	$filebuffer.= '$mg2->installdate = '.chr(34). time() .chr(34).";\n";
	$filebuffer.= '?>';
	$fp = fopen(DATA_FOLDER .'mg2db_settings.php','w');
	fwrite($fp,$filebuffer);
	fclose($fp);

	include(ADMIN_FOLDER .'install_main.inc.php');
}

include(ADMIN_FOLDER .'admin_footer.php');
?>
