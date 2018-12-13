<?php
	// DISPLAY ERRORS BUT HIDE NOTICES
	error_reporting(E_ALL ^ E_NOTICE);

	session_start();

	// DISPLAY ADMIN HEADER
	define('INC_FOLDER',   dirname(__FILE__) .'/includes/');
	define('DATA_FOLDER',  dirname(__FILE__) .'/data/');
	define('ADMIN_FOLDER', 'admin/');
	define('ADMIN_IMAGES', 'admin/images/');

	// SET HEADERS TO PREVENT BROWSER CACHING OF PAGES
	@header('Expires: Mon, 20 Jul 2000 05:00:00 GMT');
	@header('Last-Modified: '. gmdate('D, d M Y H:i:s') .' GMT');
	@header('Cache-Control: no-store, no-cache, must-revalidate');
	@header('Cache-Control: post-check=0, pre-check=0', false);
	@header('Pragma: no-cache');

	// MAKE NEW DATABASE OBJECT
	include(INC_FOLDER .'mg2_functions.php');
	include(INC_FOLDER .'mg2admin_functions.php');
	include(INC_FOLDER .'mg2admin_sqlmain.php');
	include(INC_FOLDER .'mg2_counter.php');
	$mg2 = new SQLadmin();

	// NEW MOD VERSION (SCRIPT)
	$update_version = '0.4.0 b3';

	// INCLUDE UPDATE/INSTALL HEAD AND PAGE TITLE
	$charset   = 'ISO-8859-1';
	$pagetitle = 'Update to kh_mod '. $update_version;
	@include(ADMIN_FOLDER .'install_header.inc.php');
?>

	<table class="table_menu" cellpadding="0" cellspacing="0">
	<tr valign="top">
		<td align="center" colspan="4">
			<p><strong>Update from MG2 0.5.0/0.5.1 or kh_mod &lt; <?php echo $update_version;?></strong></p>
<?php if (!isset($_REQUEST['step']) || $_REQUEST['step'] < 3) { ?>
			<p>This script will update your gallery to kh_mod <?php echo $update_version;?> in 2 easy steps.</p>
<?php }

	// SEARCH SETTING FILE
	$mg2db_settings = DATA_FOLDER .'mg2db_settings.php';
	if (is_file($mg2db_settings)) {
		if (!isset($_REQUEST['step'])) {
			$_SESSION['version'] = (method_exists($mg2, 'read_sDBSQL'))?
										  5	// kh_mod >= 0.3.0 b6
										  :
										  4;	// kh_mod == 0.3.0 b5
		}
	} else {
		$mg2db_settings = DATA_FOLDER .'mg2_settings.php';	// kh_mod >= 0.2.0 b2 (with new data structure)
		if (!isset($_REQUEST['step'])) {
			$_SESSION['version'] = (isset($mg2->foldersetting)		&&
											isset($mg2->metasetting)		&&
											isset($mg2->layoutsetting))?
											3	// kh_mod >= 0.3.0 b2
											:
											2;	// kh_mod >= 0.2.0 b2
		}
		if  (!is_file($mg2db_settings)) {
			if (!isset($_REQUEST['step'])) $_SESSION['version'] = 1;	// kh_mod <= 0.2.0 b1 or MG2 0.5.1/0.5.0
			$mg2db_settings = 'mg2_settings.php';
		}
	}
	if (is_readable($mg2db_settings) && include($mg2db_settings)) {
		// READ SETTINGS FROM MYSQL, kh_mod 0.3.0, add
		if ((($_SESSION['version'] < 4) && $mg2->extendedset & 32) || $mg2->sqldatabase) {
			($_SESSION['version'] > 4)?	// kh_mod >= 0.3.0 b6
				$mg2->read_sDBSQL()
				:
				$mg2->read_sDBMySQL();
		}
		if (empty($_REQUEST['step']) || empty($_SESSION['updatepwd'])) {
			$step = '1';
			// PASSWORD RENAMED IN, kh_mod 0.3.0 rc1
			if (!isset($mg2->adminpwd)) $mg2->adminpwd = $mg2->password;
			if (empty($_POST['password']) ||
				 md5(strrev(md5($_POST['password']))) !== $mg2->adminpwd)
			{
				// ASK FOR PASSWORD
				inputpassword($mg2);
				exit();
			}
			$_SESSION['updatepwd'] = md5(strrev(md5($_POST['password'])));
			echo '<p><strong>Step '. $step .' / 2</strong></p>';

			// SET DEFAULT VALUES
			if (!isset($mg2->thumbwidth))		$mg2->thumbwidth    = 150;				// thumb max. width
			if (!isset($mg2->thumbheight))	$mg2->thumbheight   = 150;				// thumb max. height
			if (!isset($mg2->imagecolumns))	$mg2->imagecolumns  = 4;				// images per row 
			if (!isset($mg2->imagerows))		$mg2->imagerows  	  = 6;				// images per column
			if (!isset($mg2->imagefolder))	$mg2->imagefolder   = 'pictures';	// image folder
			if (!isset($mg2->navtype))			$mg2->navtype		  = 1;				// navigationstyp (Text, Icons, Thumbs)
			if (!isset($mg2->accesstime))		$mg2->accesstime	  = 15;				// auto logout after 15 min.
			if (!isset($mg2->introwidth))		$mg2->introwidth	  = 0;				// width of folder intro text
			if (!isset($mg2->layoutsetting))	$mg2->layoutsetting = 19;				// layout settings
			if (!isset($mg2->timeformat))		$mg2->timeformat	  = '%H:%M';		// time format, kh_mod 0.3.0 rc1

			// RENAMES IN, kh_mod 0.3.0 b6
			if (!isset($mg2->thumbMaxWidth))	$mg2->thumbMaxWidth	= $mg2->thumbwidth;
			if (!isset($mg2->thumbMaxHeight))$mg2->thumbMaxHeight	= $mg2->thumbheight;
			if (!isset($mg2->inactivetime))	$mg2->inactivetime	= $mg2->accesstime;
			if (!isset($mg2->imagecols))		$mg2->imagecols		= $mg2->imagecolumns;

			// ADD UNITS TO 'introwidth' AND 'mediumimage', kh_mod 0.3.0 b6
			if (($introwidth = (int)$mg2->introwidth) > 0) {
				$mg2->introwidth  = (strpos($mg2->introwidth, '%') > 0)?
										  $introwidth . '%'
										  :
										  $introwidth . 'px';
			}
			/*
			if (($mediumimage = (int)$mg2->mediumimage) > 0) {
				$mg2->mediumimage = (strpos($mg2->mediumimage, '%') > 0)?
										  $mediumimage . '%'
										  :
										  $mediumimage . 'px';
			}
			*/

			// FOLDER SETTINGS, kh_mod 0.2.0 b2, add, 0.3.0 b2 changed
			if (!isset($mg2->foldersetting))
			if (!isset($mg2->folderseting)) {
				$mg2->foldersetting  = (isset($mg2->foldersort))?
											  $mg2->foldersort & 15			// use old settings for folder sorting 
											  :
											  0;									// sort folder content by folder setup
				$mg2->foldersetting |= ($mg2->foldericons)? 16:0;	// default icon for all folders
				$mg2->foldersetting |= 544;								// display file title under thumbnails and thumbtooltip
				$mg2->foldersetting |= ($mg2->displayfile)? 64:0;	// display file name under all thumbs
			}
			else {
				$mg2->foldersetting  = $mg2->folderseting;
				$mg2->foldersetting ^= 32;
				$mg2->foldersetting |= ($mg2->folderseting & 32)<<6;
			}
			if (!$mg2->modversion) $mg2->foldersetting &= 1023;	// without categories, kh_mod 0.3.0 < b6

			// METASETTING, kh_mod, 0.3.0, add
			if (!isset($mg2->metasetting))
			$mg2->metasetting = (isset($mg2->metaseting))?
									  $mg2->metaseting
									  :
									  17;	// kh_mod > 0.3.0 b5 -> 81

			// COMMENT SETTINGS, kh_mod 0.3.0 b2, add
			if (!isset($mg2->commentsets))$mg2->commentsets = 79;	// kh_mod > 0.3.0 b5 -> default: 159
			elseif ($_SESSION['version'] < 3) {							// kh_mod < 0.3.0 b2
				if ($mg2->commentsets & 64) {	// logip
					$mg2->commentsets ^= 64;
					$mg2->commentsets |= 256;
				}
				if ($mg2->commentsets & 4) {	// jsvalidate
					$mg2->commentsets ^= 4;
					$mg2->commentsets |= 64;
				}
				if ($mg2->commentsets & 8) {	// sendmail
					$mg2->commentsets ^= 8;
					$mg2->commentsets |= 32;
				}
				$mg2->commentsets |=   4;		// show email in commets, yes
				$mg2->commentsets |=   8;		// allow new comments, yes
				$mg2->commentsets &= ~16;		// lock new comment entries, no
			}

			// CHANGES IN kh_mod 0.3.0 b6
			if (!isset($mg2->modversion)) { // kh_mod < 0.3.0 b6

				// META SETTINGS
				$new_metasetting = ($mg2->metasetting & 15);	// take the first four flags
				if ($mg2->metasetting &  16) $new_metasetting |=  64;
				if ($mg2->metasetting &  32) $new_metasetting |=  64;
				if ($mg2->metasetting &  64) $new_metasetting |= 144;
				if ($mg2->metasetting & 128) $new_metasetting |=  32;
				$mg2->metasetting = $new_metasetting;

				// COMMENT SETTINGS
				$new_commentsets  = ($mg2->commentsets & 15);				// take the first four flags
				$new_commentsets |= 16;												// hide comment form
				if ($mg2->commentsets &   16) $new_commentsets |=   32;	// lockcomments
				if ($mg2->commentsets &   32) $new_commentsets |=   64;	// sendmail
				if ($mg2->commentsets &   64) $new_commentsets |=  128;	// jsvalidate
				if ($mg2->commentsets &  128) $new_commentsets |=  256;	// cpvalidate
				if ($mg2->commentsets &  256) $new_commentsets |= 2048;	// logip
				$mg2->commentsets = $new_commentsets;
			}

			// PERMISSION TEST, kh_mod 0.2.0, changed
			$error_message = '';
			@rmdir(DATA_FOLDER .'x');
			if (@mkdir(DATA_FOLDER .'x')) {
				@rmdir(DATA_FOLDER .'x');
			} else {
				$error_message.= 'ERROR: Cannot write to \''.DATA_FOLDER.'\' folder.
										Chmod the folder to 777 and try it again!<br />';
			}
			@rmdir($mg2->imagefolder.'/x');
			if (@mkdir($mg2->imagefolder.'/x')) {
				@rmdir($mg2->imagefolder.'/x');
			} else {
				$error_message.= 'ERROR: Cannot write to \''.$mg2->imagefolder.'\' folder.
										Chmod the folder to 777 and try it again!<br />';
			}
			if ($error_message != '') {
				echo $error_message .'
					<br />
					<form action="'. $_SERVER['PHP_SELF'] .'" method="post">
					<input type="hidden" name="step" value="" />
					<input type="image" src="'. ADMIN_IMAGES .'rebuild.gif" class="adminpicbutton" alt="Try it again" title="Try it again" />
					</form>
					<br />&nbsp;
					</td></tr></table></body></html>
				';
				exit();
			}

			// DISPLAY EXIF INFOS, kh_mod 0.2.0 b2, add
			if ($mg2->showexif === '1') $mg2->showexif = 510;

			// EXTENDED SETTINGS, kh_mod 0.2.0 b2, add
			if (!isset($mg2->extendedset))$mg2->extendedset	= 156;	// admin mode, calendar, tooltips, htmlarea
			elseif ($_SESSION['version'] < 2) {
				$mg2->extendedset = (int)$mg2->extendedset | 16;		// update calendar
				$mg2->extendedset^= 1;											// change bit 1 (password recursiv)
			}

			// ADMIN MODE OPTION, ONLY FOR kh_mod < 0.3.0 rc2
			if (!isset($mg2->modversion) || preg_match('/0\.3\.0\s*(rc1|b6)/i', $mg2->modversion))
				$mg2->extendedset	|= 128;

			// UPDATE FOR DATE FORMAT, BUILT IN kh_mod 0.1.0 final
			// kh_mod, 0.3.0 b5, changed
			if (empty($mg2->dateformat)) $mg2->dateformat = '%d.%m.%Y';
			if ($_SESSION['version'] < 2)
			if (substr_count($mg2->dateformat, '%') < 2) {
				$search  = array('%','M','j','i','n','a','A','D','m','d','H','y','Y');					// 13 items
				$replace = array('%%','%b','%e','%M','%n','%p','%p','%a','%m','%d','%H','%y','%Y');	// 13 items
				$mg2->dateformat = str_replace($search,$replace,$mg2->dateformat);
			}

			// CHANGES IN kh_mod 0.3.1
			if (!isset($mg2->modversion) || version_compare($mg2->modversion, '0.3.0', '<=')) {
				if ((int)$mg2->foldersetting & (1<<14)) {	// categories_subs
					$mg2->foldersetting |=  (1<<15);
					$mg2->foldersetting &= ~(1<<14);
				}
			}

			// CHANGES IN kh_mod 0.4.0 b1
			if (!isset($mg2->modversion) || version_compare($mg2->modversion, '0.4.0 b1', '<')) {
				$mg2->extensions = strtolower($mg2->extensions);
				if (strpos($mg2->extensions,'flv') === false) $mg2->extensions.= ',flv';
				if (strpos($mg2->extensions,'mov') === false) $mg2->extensions.= ',mov';
				if (strpos($mg2->extensions,'mp4') === false) $mg2->extensions.= ',mp4';
			}

			// CHANGES IN kh_mod 0.4.0 b2
			if (!isset($mg2->modversion) || version_compare($mg2->modversion, '0.4.0 b2', '<')) {
				$mg2->extensions = strtolower($mg2->extensions);
				if (strpos($mg2->extensions,'mp3') === false) $mg2->extensions.= ',mp3';
			}

			// CHANGES IN kh_mod 0.4.0 b3
			if (!isset($mg2->modversion) || version_compare($mg2->modversion, '0.4.0 b3', '<')) {
				// SET CONTENT TYPE IN THE ITEM DATABASE IN STEP 3
				$_SESSION['setContentType'] = true;
			}

			// SET NEW MOD VERSION
			if (is_readable(INC_FOLDER .'mg2_version.php')) {
				@include(INC_FOLDER .'mg2_version.php');
				if (preg_match('/^\d{1,2}\.\d{1,2}\.\d{1,2}.{0,5}$/', $modversion))
					$mg2->modversion = $modversion;
			}

			// WRITE SETTINGS
			if (defined('DB_NAME') 		&&
				 defined('DB_USERNAME') &&
				 defined('DB_PASSWORD')	&&
				 defined('DB_SERVER')) {
				 if ((int)$mg2->sqldatabase < 1) {
					$mg2->sqldatabase = 1;		// MySQL
					$mg2->extendedset&= ~32;	// delete old MySQL setting (only 0.3.0 beta), now 'seolink'
				 }
				 $flat_ok = ($mg2->write_sDBFlatfile('sql') && $mg2->createSQLTable('settings'))?
								$mg2->write2SQLTable('settings')
								:
								-1;
			}
			else {
				$flat_ok = ($mg2->write_sDBFlatfile('flat'))? 1:-1;
			}

			// DELETE OLD SETTINGS < kh_mod 0.3.0 b5
			if (($flat_ok > 0) && is_file(DATA_FOLDER .'mg2_settings.php')) unlink(DATA_FOLDER .'mg2_settings.php');

			echo ($flat_ok > 0)?
				'<p>Your gallery settings now are upgraded to kh_mod '. $update_version .' (MG2 0.5.1)</p>
				<p><form action="'. $_SERVER['PHP_SELF'] .'" method="post">
					<input type="hidden" name="password" value="'.$_POST['password'].'" />
					<input type="hidden" name="step" value="2" />
					<input type="image" src="'. ADMIN_IMAGES .'ok.gif" class="adminpicbutton" alt="Next" title="Next" />
				</form></p>'
				:
				'Couldn\'t upgrade your MG2 settings to kh_mod '. $update_version .' (MG2 0.5.1)!
				<br /><br />
				<form action="'. $_SERVER['PHP_SELF'] .'" method="post">
					<input type="hidden" name="step" value="" />
					<input type="image" src="'. ADMIN_IMAGES .'rebuild.gif" class="adminpicbutton" alt="Try it again" title="Try it again" />
				</form><br />';
		} // END OF STEP 1
	} else {
		echo '
			<br />
			ERROR: Cannot read \''.$mg2db_settings.'\' to update your MG2 installation!
			<br /><br />
			<form action="'. $_SERVER['PHP_SELF'] .'" method="post">
			<input type="hidden" name="step" value="" />
			<input type="image" src="'. ADMIN_IMAGES .'rebuild.gif" class="adminpicbutton" alt="Try it again" title="Try it again" />
			</form>
			<br />
		';
	}

	if ($_REQUEST['step'] === '2' && $_SESSION['updatepwd'] === $mg2->adminpwd)
	if ($_SESSION['version'] >= 2) { // kh_mod >= 0.2.0 b2
		$checked = (is_file('database.txt'))? 'checked="checked"':'';
		$step = '2';
		echo '
			<p><strong>Step '. $step .'/2 - Import the counter plug-in.</strong></p>
			<form action="'. $_SERVER['PHP_SELF'] .'" method="post">
			<input type="hidden" name="step" value="3" />
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td>
						<input type="checkbox" name="ctr" id="ctr" value="on" style="vertical-align:middle;" '.$checked.' />
						<label for="ctr"> Import plug-in counter data (from gallery root)</label>
					</td>
				</tr>
			</table>
			<br />
			<input type="image" src="'. ADMIN_IMAGES .'ok.gif" class="adminpicbutton" alt="Next" title="Next" />&nbsp;
			<a href="'. $_SERVER['PHP_SELF'] .'?step=3"><img src="'. ADMIN_IMAGES .'cancel.gif" class="adminpicbutton" alt="Cancel" title="Cancel"/></a>
			</form>
			<br />
		';				
	}
	else {
		$checked = (is_file('database.txt'))? 'checked="checked"':'';
		$step = '2';
		echo '
			<p><strong>Step '. $step .'/2 for MG2 0.5.1/kh_mod 0.1.0 or 0.2.0 b1 <span style="color:red">ONLY!</span></strong></p>
			<form action="'. $_SERVER['PHP_SELF'] .'" method="post">
			<input type="hidden" name="step" value="3" />
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td>
						<input type="checkbox" name="fDB" id="fDB" value="on" style="vertical-align:middle;" checked="checked" />
						<label for="fDB"> Import folder database (from gallery root)</label>
					</td>
				</tr><tr>
					<td>
						<input type="checkbox" name="iDB" id="iDB" value="on" style="vertical-align:middle;" checked="checked" />
						<label for="iDB"> Import image database (from gallery root)</label>
					</td>
				</tr><tr>
					<td>
						<input type="checkbox" name="ctr" id="ctr" value="on" style="vertical-align:middle;" '.$checked.' />
						<label for="ctr"> Import plug-in counter database (from gallery root)</label>
					</td>
				</tr><tr>
					<td>
						<input type="checkbox" name="cDB" id="cDB" value="on" style="vertical-align:middle;" checked="checked" />
						<label for="cDB"> Convert comment entries (in \''.$mg2->imagefolder.'\')</label>
					</td>
				</tr><tr>
					<td>
						<input type="checkbox" name="log" id="log" value="on" style="vertical-align:middle;" checked="checked" />
						<label for="log"> Import gallery log file (from gallery root)</label>
					</td>
				</tr>
			</table>
			<br />
			<input type="image" src="'. ADMIN_IMAGES .'ok.gif" class="adminpicbutton" alt="Next" title="Next" />&nbsp;
			<a href="'. $_SERVER['PHP_SELF'] .'?step=3"><img src="'. ADMIN_IMAGES .'cancel.gif" class="adminpicbutton" alt="Cancel" title="Cancel"/></a>
			</form>
			<br />
		';
	} // END OF STEP 2

	if ($_REQUEST['step'] === '3' && $_SESSION['updatepwd'] === $mg2->adminpwd) {
		$step = '3';
		if (is_readable(INC_FOLDER .'mg2admin_convert.php')) {
			include(INC_FOLDER .'mg2admin_convert.php');
			// CONVERT DATABASE CONTENT
			if ($_SESSION['version'] < 2) {	// kh_mod < 0.2.0 b2
				if (isset($_REQUEST['fDB'])&&$_REQUEST['fDB']==='on') convert_fDB();			// folder database
				if (isset($_REQUEST['iDB'])&&$_REQUEST['iDB']==='on') convert_iDB();			// item database
				if (isset($_REQUEST['ctr'])&&$_REQUEST['ctr']==='on') convert_clickDB();	// counter database
				if (isset($_REQUEST['cDB'])&&$_REQUEST['cDB']==='on') convert_cDB();			// comment files
				if (isset($_REQUEST['log'])&&$_REQUEST['log']==='on') {							// logfile
					$message = (@copy('mg2_log.txt', DATA_FOLDER .'mg2_log.txt'))?
								  'Imoprted log file'
								  :
								  'Couldn\'t imoprt log file';
					$mg2->displaystatus($message);
				}
			}
			// SET CONTENT TYPE
			elseif (isset($_SESSION['setContentType'])) { 
				setContentType();
			}
			echo '
				<p>
				Your gallery should now be upgraded to kh_mod '. $update_version .' (MG2 0.5.1)
				</p><p>
				<b>IMPORTANT: DELETE \'mg2_update.php\' USING FTP NOW!!!</b>
				</p><p>
				<a href="admin.php">Go to admin panel</a>
				</p>
			';
			$mg2->log('Gallery updated to kh_mod '. $update_version .' (MG2 0.5.1)');
		}
		else {
			echo '
				<p>
				<b>Couldn\'t upgrade your MG2 installation to kh_mod '. $update_version .' (MG2 0.5.1)!</b>
				</p><p>
				-- The convert file \''. INC_FOLDER .'mg2admin_convert.php\' isn\'t readable! --
				</p><p>
				<form action="'. $_SERVER['PHP_SELF'] .'" method="post">
					<input type="hidden" name="step" value="2" />
					<input type="image" src="'. ADMIN_IMAGES .'rebuild.gif" class="adminpicbutton" alt="Try it again" title="Try it again" />
				</form>
				</p>
			';
		}
		unset($_SESSION['updatepwd']);
		unset($_SESSION['version']);
	} // END OF STEP 3
?>
		</td>
	</tr>
</table>
</body>
</html>

<?php
	function inputpassword($mg2) {
		if (!is_file('lang/'.$mg2->defaultlang))	$mg2->defaultlang = 'english.php';
		if (is_file('lang/'.$mg2->defaultlang)) {
			include('lang/'.$mg2->defaultlang);
			@header('Content-Type: text/html; charset='.$mg2->charset);
		}

		echo'
		<script language="JavaScript" type="text/javascript">
		<!--
			window.onload = function() {document.login.password.focus();}
		-->
		</script>
		<table class="table_menu" cellpadding="0" cellspacing="0">
			<tr>
			<td class="td_div">
				<br />
				<form name="login" method="post" action="'. $_SERVER['PHP_SELF'] .'">
				<p><b>'. $mg2->lang['enterpassword'] .'</b></p>
				<p>'. $mg2->lang['thissection'] .'</p>
				<p><input type="password" name="password" class="admintext" /></p>
				<p><input type="image" src="'. ADMIN_IMAGES .'ok.gif" class="adminpicbutton" alt="'. $mg2->lang['ok'] .'" title="'. $mg2->lang['ok'] .'" /></p>
				</form>
			</td>
		</tr>
		</table>
		</body>
		</html>
		';
	}
?>