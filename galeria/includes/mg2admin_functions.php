<?php

class MG2admin extends mg2db {

	var $Calendar;		// child object for javascript calendar

	// DISPLAY STATUS MESSAGE
	// kh_mod 0.3.0, changed
	function displaystatus($message='', $type=0) {
		if (!empty($message)) {
			$style = '<div style="color:%s;">%s %s</div>';
			switch ($type) {
				case 0:	// ok
					$this->status = $message;
					break;
				case 1:	// notice
					$this->status = sprintf($style, 'teal', $this->lang['notice'], $message);
					break;
				case 2:	// warning
					$this->status = sprintf($style, '#C69023', $this->lang['warning'], $message);
					break;
				case 3:	// error
					$this->status = sprintf($style, 'red', $this->lang['error'], $message);
			}
		}
		if (!empty($this->status)) {
			include(ADMIN_FOLDER .'admin2_status.php');
			$this->status = '';
		}
	}

	// CHECK LOGIN
	// kh_mod, 0.3.1 changed
	function security($pagetitle='')
	{
		$firstlogin = false;
		if (isset($_POST['password']) &&
			 md5(strrev(md5($_POST['password']))) === $this->adminpwd &&
			 !isset($_SESSION[GALLERY_ID]['adminpwd'])
			)
		{	// FIRST LOGIN!
			$_SESSION[GALLERY_ID]['sorting']		  = false;							// sorting array
			$_SESSION[GALLERY_ID]['sortstart']	  = false;							// folder id of the sorted folder
			$_SESSION[GALLERY_ID]['adminpwd']	  = $this->adminpwd;				// admin password
			$_SESSION[GALLERY_ID]['accesstime']	  = time();							// last access in seconds
			$_SESSION[GALLERY_ID]['inactivetime'] = ($this->inactivetime < 1)?
																 900								// time in seconds (15 minutes)
																 :
																 $this->inactivetime * 60;	// time in seconds
			$ip   = getenv('REMOTE_ADDR');
			$host = gethostbyaddr($ip);
			$this->log(sprintf('Login from IP: %s, HOST: %s', $ip, $host));
			$firstlogin = true;
		}

		// ALLOW INACTIVE ADMIN TIME IN SECONDS
		$inactivetime = (int)$_SESSION[GALLERY_ID]['inactivetime'];

		// PASSWORD LOGOFF, SHOW LOGIN SCREEN
		if (empty($_SESSION[GALLERY_ID]['adminpwd']) ||
			 $_SESSION[GALLERY_ID]['adminpwd'] !== $this->adminpwd)
		{
			$select = 1;
			unset($_SESSION[GALLERY_ID]);
			$pwdheadline = ($_POST['password'])? $this->lang['wronglogin']:$this->lang['enterpassword'];
			include(ADMIN_FOLDER .'admin_security.php');
			include(ADMIN_FOLDER .'admin_donate_hint.php');
			include(ADMIN_FOLDER .'admin_footer.php');
			exit();
		}
		// TIME LOGOFF
		elseif ((time() > ($_SESSION[GALLERY_ID]['accesstime']) + $inactivetime)) {
			$select = 2;
			unset($_SESSION[GALLERY_ID]);
			include(ADMIN_FOLDER .'admin_security.php');
			include(ADMIN_FOLDER .'admin_footer.php');
			$this->log('Timeout logoff after '. (int)($inactivetime/60) .' minutes');
			exit();
		}
		// ADMIN LOGOUT
		elseif ($_REQUEST['action'] === 'logoff') {
			$select = 3;
			unset($_SESSION[GALLERY_ID]);
			include(ADMIN_FOLDER .'admin_security.php');
			include(ADMIN_FOLDER .'admin_footer.php');
			$this->log('Logoff');
			exit();
		}

		// SET NEW ACCESSTIME
		$_SESSION[GALLERY_ID]['accesstime'] = time();

		// SET ADMIN PERMISSION ENVIRONMENT
		define('USER_ADMIN', true);

		// ADDITIONAL LOGIN PERMISSION CHECKS
		if ($firstlogin === true) {
			@rmdir(DATA_FOLDER .'x');
			if (@mkdir(DATA_FOLDER .'x')) {
				@rmdir(DATA_FOLDER .'x');
			} else {
				$this->permcheck(1);
			}
			@rmdir($this->imagefolder .'/x');
			if (@mkdir($this->imagefolder .'/x')) {
				@rmdir($this->imagefolder .'/x');
			} else {
				$this->permcheck(2);
			}
			// ARE DATABASE FILES WRITEABLE?
			if (is_file(DATA_FOLDER .'mg2db_idatabase.php')			&&
				 !is_writable(DATA_FOLDER .'mg2db_idatabase.php'))
			{
				$this->permcheck(3);
			}
			if (is_file(DATA_FOLDER .'mg2db_idatabase_temp.php')	&&
				 !is_writable(DATA_FOLDER .'mg2db_idatabase_temp.php'))
			{
				$this->permcheck(4);
			}
			if (is_file(DATA_FOLDER .'mg2db_fdatabase.php')			&&
				 !is_writable(DATA_FOLDER .'mg2db_fdatabase.php'))
			{
				$this->permcheck(5);
			}
			if (is_file(DATA_FOLDER .'mg2db_fdatabase_temp.php')	&&
				 !is_writable(DATA_FOLDER .'mg2db_fdatabase_temp.php'))
			{
				$this->permcheck(6);
			}
		}

		// RETURN IF FIRST LOGIN
		return $firstlogin;
	}

	//
	// DISPLAY PERMISSION ERROR
	// kh_mod 0.3.1, changed
	function permcheck($level) {
		$permerror = $this->lang['permerror'.$level];
		$whattodo  = $this->lang['whattodo'.$level];
		include(ADMIN_FOLDER .'admin_header.php');
		include(ADMIN_FOLDER .'admin_permerror.php');
		unset($_SESSION[GALLERY_ID]);
		exit();
	}

	//
	// CONVERT BYTES TO KB, MB OR GB
	// kh_mod 0.3.1, add
	function convertBytes($bytes, $precision=2) {

		// INIT VALUES
		$prec  = 0;
		$units = array('Bytes', 'KBytes', 'MBytes', 'GBytes');

		// CALCULATE
		foreach($units as $unit) {
			if ($bytes < 1024) break;
			$bytes /= 1024;
			$prec   = $precision;
		}

		// FORMAT CALCULATED BYTES
		return sprintf('%s %s',
					 number_format($bytes, $prec, $this->lang['decimalsign'],''),
					 $unit
				 );
	}

	//
	// REBULID THUMB AND MEDIUM IMAGE
	// kh_mod 0.2.0, changed
	function rebuildID($imageID) {
		$rebuild = false;
		$errtype = 3; // error
		do {
			// EXCLUSION CRITERIONS
			if (!isset($this->all_images[$imageID])) {
				$message = sprintf($this->lang['nopictureid'], $imageID); break;
			}
			$imageRC  = &$this->all_images[$imageID];
			$filename = $imageRC[6];
			$subdir	 = $imageRC[7];
			$filePath = $this->get_path($filename, $subdir);
			if (!is_readable($filePath)) {
				$message = $this->lang['rebuilderror'] .' '. $filename; break;
			}

			// DELETE OLD THUMB AND MEDIUM IMAGE
			$thumbPath  = $this->get_path($filename, $subdir, 'thumb');
			$mediumPath = $this->get_path($filename, $subdir, 'medium');
			if (is_file($thumbPath)  && !unlink($thumbPath))	 break;
			if (is_file($mediumPath) && !unlink($mediumPath)) break;

			// CREATE NEW THUMB AND MEDIUM IMAGE
			$finfo = $this->resizeImage($filename, $subdir);

			// UPDATE DATABASE, kh_mod 0.3.1 changed
			if (is_array($finfo)) {
				$imageRC[8]  = $finfo['image']['width'];
				$imageRC[9]  = $finfo['image']['height'];
				$imageRC[10] = $finfo['thumb']['width'];
				$imageRC[11] = $finfo['thumb']['height'];
				$imageRC[12] = filesize($filePath);
				$imageRC[13] = (isset($imageRC[13]))? trim($imageRC[13]):'';
				$imageRC[14] = (isset($imageRC[14]))? trim($imageRC[14]):'';
				$imageRC[15] = (isset($imageRC[15]))? (int)$imageRC[15] :0;
				$imageRC[16] = $finfo['code'];
				$this->log('Rebuild thumbnail for image ID #'. $imageID .' (\''. $filename .'\')');
				// THUMBNAIL ERROR?
				if ($finfo['error'] & 1) {
					$message = sprintf("%s '%s'", $this->lang['rebuilderror'], $filename);
					$errtype = 2;	// warning
				} else {
					$message = sprintf("%s '%s'", $this->lang['rebuildsuccess'], $filename);
					$errtype = 0;	// ok
					$rebuild = true;
				}
			}
		}
		while(0);

		// DISPLAY MESSAGE
		$this->displaystatus($message, $errtype); flush();
		return $rebuild;
	}

	//
	// IMAGE SETTING DIALOG
	// kh_mod 0.3.0, changed
	function editID($editID, $updatedComment) {
		if (!isset($this->all_images[$editID])) {
			$this->log('ERROR: In editID(), image ID #'. $editID .'not founded!');
			$this->displaystatus(sprintf($this->lang['nopictureid'], $editID), 3);
			return false;
		}

		// GET IMAGE VALUES
		$imageRC	 = $this->all_images[$editID];
		$folderID = (int)$imageRC[1];
		$this->makeFolderlist(-1);
		$this->getFolderSettings($folderID);
		$_REQUEST['fID']	= $folderID;
		$title				= $imageRC[2];
		$description		= ($this->extendedset & 4)?	// WYSIWYG HTML-Editor
								  $imageRC[3]
								  :
								  $this->br2nl($imageRC[3]);
		$publish			  	= $this->time2date($imageRC[4], true);
		$position			= $imageRC[5];
		$filename			= $imageRC[6];
		$subdir				= $imageRC[7];
		$photographer		= $imageRC[14];
		$selected			= (empty($imageRC[15]))? '':'checked="checked"';

		// GET KEYWORDS
		$Keywords = $this->getInstance('MG2Keywords');
		$keyArray = $this->$Keywords->getKeywords($editID);

		// GET CONTENT TYPE
		$contentType = $this->getContentType($editID);

		// GET ITEM ICON AND CONTENT TYPE
		list($thumbID,
			  $thumbfile,
			  $thumbWidth,
			  $thumbHeight) = $this->getFileIcon($editID, $contentType);

		// ADD IMAGE SUFFIX
		$thumbfile.= $isuffix = sprintf('?%d', mt_rand(0,10000));

		// THUMB SIZE ATTRIBUTE
		$thumbsize = sprintf('width="%d" height="%d"', $thumbWidth, $thumbHeight);

		// CREATE NAVIGATION ELEMENTS
		list($currentPage, $prevID, $nextID)= $this->imagenavigation($editID);

		// DISPLAY IMAGE SETTING
		include(ADMIN_FOLDER .'admin2_edit.php');

		// DISPLAY COMMENT OVERVIEW
		$Comments	  = $this->getInstance('MG2Comments');
		$commentItems = $this->$Comments->getComments($editID);
		if (($numComments = count($commentItems)) > 0) {
			include(ADMIN_FOLDER .'admin2_comments.php');
		}

		// CURRENT PAGE AND SUFFIX FOR IMAGE UPDATING
		return array($currentPage, $isuffix);
	}

	//
	// UPDATE ITEM DATA RECORD ACTION
	// kh_mod 0.4.0 b3, changed
	function updateID($itemID) {
		// EXISTS REQUEST ID?
		if (!isset($this->all_images[$itemID])) {
			$this->log('ERROR: Fct. updateID(), item ID #'. $itemID .' not founded!');
			$this->displaystatus(sprintf($this->lang['nopictureid'], $itemID), 3);
			return false;
		}

		// CLEAN INPUT VALUES
		$filename_new = $this->charfix($_POST['filename']);
		$position	  = (int)$_POST['position'];										// order position for image
		$publish		  = $this->date2time($_POST['publish']);
		$title		  = $this->charfix($_POST['title']);
		$description  = $this->charfix($_POST['description']);
		$description  = ($this->extendedset & 4)?										// WYSIWYG HTML-Editor?
							 preg_replace('/\r\n|\r|\n/', '', $description)			// remove line breaks
							 :
							 preg_replace('/\r\n|\r|\n/', '<br />', $description);// convert line breaks
		$photographer = $this->charfix($_POST['photographer']);					// artist for exif display
		$selected	  = !empty($_POST['selected']) ? true:false;					// record selected
		$folderID	  = (int)$_POST['setthumb'];										// folder id for thumb icon

		// INITIALIZE STATUS MESSAGE AND REFERENCE TO IMAGE DB
		$this->status = '';
		$imageRC		  = &$this->all_images[$itemID];

		// SET AS THUMBNAIL FOR FOLDER?
		$fDB_ok = true;
		if (isset($this->all_folders[$folderID])) {
			$thumbfile = $this->get_path($imageRC[6],$imageRC[7],'thumb');
			if (is_file($thumbfile)) {
				$this->all_folders[$folderID][6] = $itemID;						// use image as folder icon
				$fDB_ok = ($this->write_fDB('upd', array($folderID)) > 0)?	// write folder to database
							 true
							 :
							 false;
			}
		}

		// SET TITLE, DESCRIPTION, POSITION
		$imageRC[2] = $title;
		$imageRC[3] = $description;
		$imageRC[5] = $position;

		// SET THE NEW DATE
		if ($publish !== false) {
			$diffTime = $imageRC[4] - $publish;
			if ($diffTime < 0 || 59 < $diffTime) $imageRC[4] = $publish;
		}

		// IF SET KEYWORDS
		if (isset($_POST['keywords'])) {
			$Keywords = $this->getInstance('MG2Keywords');
			$keysOK	 = $this->$Keywords->setKeywords($itemID);
		}

		// IF VIDEO/SOUND FILE
		if ($this->getContentType($itemID, 'flash'))
		{
			// SET FLASH CANVAS SIZE
			$imageRC[8] = (int)$_POST['canvas_width'];
			$imageRC[9] = (int)$_POST['canvas_height'];

			// SET SPLASH OPTIONS
			$Splashes = $this->getInstance('MG2Splashes');
			$this->$Splashes->setSplashRecord($itemID);
		}

		// RENAME FILE NAME?
		$ren_ok = ($imageRC[6] !== $filename_new)?
					 $this->renamefiles($itemID, $filename_new)
					 :
					 true;

		// SET PHOTOGRAPHER
		$imageRC[14] = $photographer;

		// SET RECORD UNSELECTED
		if (!$selected) {
			$imageRC[15] = 0;
		}
		// SET RECORD SELECTED
		elseif (empty($imageRC[15])) {
			$imageRC[15] = time();
		}

		// WRITE IMAGE DATABASE AND UPDATE
		$iDB_ok = ($this->write_iDB('upd', array($itemID)) > 0)?
					 true
					 :
					 false;

		// OUTPUT STATUS MESSAGES
		if ($iDB_ok === false) {
			$this->displaystatus($this->lang['iDB_error'], 3);			// item database error
			$this->log('ERROR: In updateID(), concerns image ID #'. $itemID);
		}
		elseif ($ren_ok === false) {
			$this->displaystatus($this->status, 2);						// rename item file warning
			$this->log('Warning: Couldn\'t rename image file, ID #'. $itemID);
		}
		else {
			$this->displaystatus($this->lang['updatesuccess']);		// update item record ok
			$this->log('Success of updating image record, ID #'. $itemID);
		}
		if ($fDB_ok === false) {												// thumbnail setting error
			$this->displaystatus('Couldn\'t set image as folder icon!', 3);
		}

		// COMPLETELY SUCCESSFULL?
		return ($ren_ok && $iDB_ok && $fDB_ok)? $imageRC[1]:false;	// parent id : false
	}

	// kh_mod 0.3.1, changed
	function renamefiles($imageID, $fname_new) {
		// CHECK FILENAME FOR ILLEGAL CHARACTERS
		if (preg_match('/[:;<>\/\\\¤\|~§\?]/', $fname_new)) {
			$this->status = sprintf('%s<div>\'%s\'</div>',
									 $this->lang['renamefailure'],
									 htmlspecialchars($fname_new, ENT_QUOTES)
								 );
			return false;
		}

		// CHECK FILE EXTENSION
		$ext = strtolower(substr(strrchr($fname_new, '.'), 1));
		if (!$ext || strpos($this->extensions, $ext) === false) {
			$this->status = sprintf('%s<div>\'%s\'</div>',
									 $this->lang['forebiddenxtensions'],
									 htmlspecialchars($fname_new, ENT_QUOTES)
								 );
			return false;
		}

		// RENAME IMAGE
		$filename  = $this->all_images[$imageID][6];
		$subdir	  = $this->all_images[$imageID][7];
		$image_old = $this->get_path($filename, $subdir);
		$image_new = $this->get_path($fname_new, $subdir);
		if (
			!$image_new												||		// new file formally incorrect
			!$image_old												||		// orig. file formally incorrect
			!is_file($image_old)									||		// orig. file not found
			!rename($image_old, $image_new)							// renaming missed
		) {
			$this->status = sprintf('%s<div>\'%s\'</div>',
									 $this->lang['renamefailure_image'],
									 htmlspecialchars($fname_new, ENT_QUOTES)
								 );
			return false;
		}

		// SET DATABASE ENTRY
		$ren_ok = true;
		$this->all_images[$imageID][6] = $fname_new;

		// RENAME THUMBNAIL
		$thumb_old = $this->get_path($filename, $subdir, 'thumb');
		$thumb_new = $this->get_path($fname_new, $subdir, 'thumb');
		if (!is_file($thumb_old))
			$ren_ok = $this->rebuildID($imageID);
		elseif (!rename($thumb_old, $thumb_new)) {
			$this->status = $this->lang['renamefailure_thumb'] .'<br />';
			$ren_ok = false;
		}

		// RENAME MEDIUM
		$medium_old = $this->get_path($filename, $subdir, 'medium');
		$medium_new = $this->get_path($fname_new, $subdir, 'medium');
		if (is_file($medium_old) && !rename($medium_old, $medium_new))
			$this->status.= $this->lang['renamefailure_medium'] .'<br />';

		// RENAME COMMENT
		$comment_old = $this->get_path($filename, $subdir, 'comment');
		$comment_new = $this->get_path($fname_new, $subdir, 'comment');
		if (is_file($comment_old) && !rename($comment_old, $comment_new))
			$this->status.= $this->lang['renamefailure_comment'] .'<br />';

		return $ren_ok;
	}

	//
	// ASK DELETE IMAGE DIALOG
	// kh_mod 0.1.0, add, kh_mod 0.4.0 b3 changed
	function askdeleteID($deleteID, $page) {

		// CHECK ITEM ID
		if (!array_key_exists($deleteID, $this->all_images)) {
			$this->displaystatus(sprintf($this->lang['nopictureid'], $deleteID), 3);
			return false;
		}

		// GET ITEM ICON
		list(	$thumbID,
				$thumbFile,
				$thumbWidth,
				$thumbHeight) = $this->getFileIcon($deleteID, null, true);

		// GET DIALOG VALUES
		$display		  = 'image';
		$folderID	  = $this->all_images[$deleteID][1];
		$fileName	  = $this->all_images[$deleteID][6];
		$message		  = sprintf($this->lang['deletefile'], $fileName);
		$cancel_href  = ADMIN_INDEX .'?fID='. $folderID .'&amp;page='. $page;
		$ok_href		  = ADMIN_INDEX .'?action=deleteID&amp;iID='. $deleteID .'&amp;page='. $page;
		$cancel_title = $this->lang['cancel'];
		$ok_title	  = $this->lang['ok'];

		// DISPLAY DIALOG TEMPLATE
		include(ADMIN_FOLDER .'admin2_delete.php');

		return $folderID;
	}

	//
	// DELETE SINGLE IMAGE ACTION
	// kh_mod 0.3.1, change
	function deleteID($imageID) {

		// GET FOLDER ID
		$folderID = (int)$this->all_images[$imageID][1];

		// DELETE IMAGE
		$delStatus = $this->deleteItem($imageID);

		// DELETE IMAGE USED AS FOLDER ICON
		$this->delFolderIcons(array($imageID));

		// DELETE COUNTER ENTRY
		$Counter = new MG2Counter($this->sqldatabase);
		$Counter->deleteRecords(array($imageID));

		// DELETE FLASH ITEM OR IMAGE USED AS SPLASH
		$Splashes = $this->getInstance('MG2Splashes');
		$this->$Splashes->deleteSplashes(array($imageID));

		// DELETE ITEM KEYWORD RECORD
		$Keywords = $this->getInstance('MG2Keywords');
		$this->$Keywords->deleteItemKeywords(array($imageID));

		// DELETED DATA BASE ENTRY?
		if (($delStatus & 4) && ($this->write_iDB('del', array($imageID)) >= 0)) {
			$this->status.= '<div>'. $this->lang['recorddeleted'] .'</div>';
		}
		// PHYSICALLY IMAGE FILES AND/OR TEMPORARLY IMAGE RECORDS DELETED?
		elseif ($delStatus & 6) {
			$this->status.= sprintf('<div style="margin-top:8px;color:red">%s %s</div>',
									 $this->lang['error'],
									 $this->lang['iDB_error']
								 );
		}

		// IMAGE ID EXISTS?
		if ($delStatus & 1)
			$errtype = ($delStatus & 2)?	// image file physically deleted?
						  0	// ok
						  :
						  2;	// warning
		else
			$errtype = 3;	// error

		$this->displaystatus($this->status, $errtype);

		// RETURN FOLDER ID
		return ($delStatus & 1)? $folderID:1;
	}

	// kh_mod 0.3.1, changed
	function deletefiles() {
		$todelete	= 0;
		$delfiles	= 0;
		$delrecords	= array();
		$numfiles	= (int)$_REQUEST['selectsize'];

		// DLETE FILES
		$this->log('Delete file(s) started');

		for ($i = 0; $i < $numfiles; $i++) {
			$imageID = (int)$_REQUEST['selectfile'. $i];
			if ($imageID < 1) continue;

			$todelete++;
			if ($delStatus = $this->deleteItem($imageID)) {
				if ($delStatus & 2) $delfiles++;						// image file deleted from server?
				if ($delStatus & 4) $delrecords[] = $imageID;	// temp database entry deleted?
			}
		}

		// DELETE IMAGES USED AS FOLDER ICONS
		$this->delFolderIcons($delrecords);

		// DELETE COUNTER ENTRIES
		$Counter = new MG2Counter($this->sqldatabase);
		$Counter->deleteRecords($delrecords);

		// DELETE FLASH ITEM OR IMAGE USED AS SPLASH
		$Splashes = $this->getInstance('MG2Splashes');
		$this->$Splashes->deleteSplashes($delrecords);

		// DELETE ITEM KEYWORD RECORD
		$Keywords = $this->getInstance('MG2Keywords');
		$this->$Keywords->deleteItemKeywords($delrecords);

		// BUILD STATUS MESSAGE
		$file_txt = ($todelete > 1)? $this->lang['filesdeleted']:$this->lang['filedeleted'];
		if ($todelete < 1) {
			$this->log('ERROR: No file selected to delete!');
			$message = $this->lang['filenotselected'];
			$errtype = 1;
		} elseif ($delfiles < $todelete) {
			$message = $delfiles .' '. $this->lang['of'] .' '. $todelete .' '. $file_txt;
			$errtype = 2;
		} else {
			$message = $delfiles .' '. $file_txt;
			$errtype = 0;
		}

		//  TEMPORARLY DELETED IMAGE RECORDS FROM ARRAY
		$deleted = count($delrecords);
		// WRITE IMAGE DATA BASE AND UPDATE IT
		if ($deleted && ($this->write_iDB('del', $delrecords) >= 0)) {
			$this->log('Summery: Deleted '. $deleted .' database entry(s) of '. $todelete .' selected');
			$record_txt = ($deleted > 1)? $this->lang['recordsdeleted']:$this->lang['recorddeleted'];
			$message.= '<div>'. $deleted .' '. $record_txt .'</div>';
		// IMAGE FILES DELETED AND/OR IMAGE RECORDS DELETED TEMPORARLY?
		} elseif ($deleted || $delfiles) {
			$message.= sprintf('<div style="margin-top:8px;color:red">%s %s</div>',
								$this->lang['error'],
								$this->lang['iDB_error']
						  );
		}
		$this->displaystatus($message, $errtype);
	}

	// kh_mod 0.4.0 b3, changed
	function deleteItem($itemID) {

		// EXISTS REQUESTED ID?
		if (!isset($this->all_images[$itemID])) { 	// kh_mod 0.2.0, changed
			$this->displaystatus(sprintf($this->lang['nopictureid'], $itemID), 3);
			return false;
		}

		// SET ITEM ID OK AND GET FILE NAME AND SUBDIR
		$delStatus = 1;
		$filename  = $this->all_images[$itemID][6];
		$subdir	  = $this->all_images[$itemID][7];

		// IS EXISTS ITEM, DELETE IT!
		$filepath = $this->get_path($filename, $subdir);
		if (is_file($filepath)) {
			if (!unlink($filepath)) {
				$this->log('ERROR: Couldn\'t delete file \''.$filename.'\'');
				$this->status = $this->lang['filenotdeleted'].' \''.$filename.'\'';
				return $delStatus;
			}
			else {
				$this->log('Deleting file \''. $filename .'\'');
				$this->status = $this->lang['filedeleted'].' \''.$filename.'\'';
				$delStatus |= 2;
			}
		}
		else {
			$this->log('ERROR: Couldn\'t found file \''. $filename .'\' to delete!');
			$this->status = $this->lang['filenotfound'].' \''.$filename.'\'';
		}

		// DELETE THUMBNAIL
		$thumbpath = $this->get_path($filename, $subdir, 'thumb');
		if (is_file($thumbpath)) unlink($thumbpath);

		// DELETE MEDIUM IMAGE
		$mediumpath = $this->get_path($filename, $subdir, 'medium');
		if (is_file($mediumpath)) unlink($mediumpath);

		// DELETE COMMENT FILE
		$commentpath = $this->get_path($filename, $subdir, 'comment');
		if (is_file($commentpath)) unlink($commentpath);

		// DELETE DATABASE ENTRY
		if ($del_ok = $this->arrayDelete($this->all_images, $itemID)) {
			$this->log('Item database entry deleted, ID #'. $itemID);
			$delStatus |= 4;
		} else {
			$this->log('ERROR: Couldn\'t delete item database entry!');
		}
		return $delStatus;
	}

	//
	// DELETED IMAGES DELETE ALSO AS FOLDER ICON
	// kh_mod 0.3.1, add
	function delFolderIcons($delImages=array()) {

		$delImages = array_flip($delImages);
		$records   = array();
		foreach($this->all_folders as $folderID=>$folderRC) {

			// IMAGE NOT USED AS FOLDER ICON?
			if (!isset($delImages[(int)$folderRC[6]])) continue;

			$this->all_folders[$folderID][6] = 0;	// default icon, -1 random image
			$records[] = $folderID;
		}
		if (!empty($records)) $this->write_fDB('upd', $records);
	}

	//
	// UPLOAD IMAGES ACTION
	// kh_mod 0.3.1, changed
	function upload($folderID) {
		// CHECK FOLDER ID
		if (!isset($this->all_folders[$folderID])) {
			$this->displaystatus($this->lang['nofolderid'] .' #'. $folderID, 3);
			return false;	// corrupted folder id
		}

		// GET TARGET SUBDIR FOR IMAGES
		$subdir = trim($_POST['importfrom']);
		if (preg_match('°(\.\./|//|%\d|\\\x\d)°', $subdir)) {					// forbidden path chars
			$this->displaystatus($this->lang['invalidimportpath'], 2);
			$this->log('No upload possible, invalid server path!');
			return 0;		// no import
		}

		// WRITE LOG MESSAGE
		$this->log('Upload and import started');

		// INIT USED VALUES
		$allfiles  = 0;
		$duplicate = 0;
		$renamed   = 0;
		$records   = array();

		for ($x = 0; $x < 10; $x++) {
			// GET FORM VALUES
			$tmp_name  = trim($_FILES['file']['tmp_name'][$x]);
			$filename  = trim($_FILES['file']['name'][$x]);
			$tmp_size  = (int)$_FILES['file']['size'][$x];
			$uploadto  = ($_POST['uploadto'][$x])?
							 (int)$_POST['uploadto'][$x]
							 :
							 (int)$_GET['fID'];	// fall back
			$overwrite = ($_POST['overwrite'.$x])? true:false;

			// EXCLUSION CRITERIONS
			if (!$tmp_name || $tmp_size < 1)							continue;	// temp file don't exists
			if (preg_match('/[:;<>\/\\\¤\|§\?"]/',$filename))	continue;	// forbidden chars
			if (!isset($this->all_folders[$uploadto]))			continue;	// folder don't exists

			// START IMAGE FILE IMPORT
			$allfiles++;
			$filepath = $this->imagefolder . $subdir .'/'. $filename;
			// NEW FILE OR OVERWRITE?
			if (!is_file($filepath) or $overwrite) {
				$imageID = $this->importimage($uploadto,$filename,$subdir,$tmp_name);
				if ($imageID) {
					$records[] = $imageID;
				}
			}
			// DIFFERENT FILE SIZE?
			elseif (filesize($filepath) !== $tmp_size) {
				$extension = strrchr($filename,'.');
				$extenlen  = strlen($extension);
				$filename  = substr($filename,0,-$extenlen); 
				$filename .= '_autorenamed'. rand(1000,9999);
				$filename .= $extension;
				$imageID   = $this->importimage($uploadto,$filename,$subdir,$tmp_name);
				if ($imageID) {
					$records[] = $imageID;
					$renamed++;
				}
			}
			// FILE EXISTS BUT NOT ENTERED IN DATABASE?
			elseif ($imageID = $this->importimage($folderID, $filename, $subdir)) {
				$records[] = $imageID;
			// FILE ALREADY EXISTS!
			} else
				$duplicate++;
		}
		// IMPORTED RECORDS
		$imported = count($records);

		// WRITE IMAGE DATABASE AND UPDATE
		$import_ok = ($imported && ($this->write_iDB('upd', $records) > 0))? true:false;

		// DISPLAY STATUS MESSAGE
		if ($import_ok) {
			$cimages = (string)count($this->all_images);
			$this->log('Summary: Uploaded and imported '.$imported.' file(s). Gallery now contains '. $cimages .' images');
			$this->status = $imported .' '. $this->lang['filesimported'];
		} elseif ($imported) {
			$this->status = $imported .' '. $this->lang['filesuploaded'];
		} elseif ($duplicate < 1) {
			$this->log('No files to import!');
			$message = $this->lang['nofilestoimport'];
			if ($allfiles > 0) {
				$message.= sprintf('<div>(%d %s %s)</div>',
									$allfiles,
									$this->lang['forbidden'],
									($allfiles===1)? $this->lang['file']:$this->lang['files']
							  );
			}
			$this->displaystatus($message, 1);
		}
		if ($renamed > 0) {
			$this->log('Therefrom '. $renamed .' file(s) renamed automatically!');
			$this->status.= sprintf(', %s<div>%d %s</div>',
									 $this->lang['therefrom'],
									 $renamed,
									 $this->lang['filesrenamed']			
								 );
		}
		if ($duplicate > 0) {
			$this->log($duplicate .' already exists file(s) have not been uploaded!');
			$this->status.= sprintf('<div style="color:teal">%s %d %s</div>',
									 $this->lang['notice'],
									 $duplicate,
									 $this->lang['alreadyexists']
								 );
		}
		if ($imported > 0) {
			$iDB_error = ($import_ok===false)?
							 sprintf('<div style="color:red">%s %s</div>', $this->lang['error'], $this->lang['iDB_error'])
							 :
							 '';
			$this->status.= sprintf('%s<div style="margin-top:8px"><a href="%s?fID=%d">%s \'%s\'</a></div>',
									 $iDB_error,
									 ADMIN_INDEX,
									 $folderID,
									 $this->lang['backtofolder'],
									 $this->getFolderName($folderID)
								 );
		}
		$this->displaystatus();
		return $imported;
	}

	//
	// IMPORT START ACTION
	// kh_mod 0.3.1, changed
	function importstart($folderID) {
		// CHECK FOLDER ID
		if (!isset($this->all_folders[$folderID])) {
			$this->displaystatus($this->lang['nofolderid'] .' #'. $folderID, 3);
			return false;	// corrupted folder id
		}

		// GET TARGET SUBDIR FOR IMAGES
		$subdir = trim($_POST['importfrom']);
		if (preg_match('°(\.\./|//|%\d|\\\x\d)°', $subdir)) {				// forbidden path chars
			$this->displaystatus($this->lang['invalidimportpath'], 2);
			$this->log('No import possible, invalid server path!');
			return 0;		// no import
		}

		// IMPORT SERVER DIRECTORY STRUCTURE? kh_mod 0.3.0, add
		if (!empty($_POST['dirstruc'])) $this->importdirs($folderID, $subdir);

		// IMPORT IMAGES
		$this->log('Import started');
		$imported = 0;
		if ($handle = @opendir($this->imagefolder . $subdir)) {
			$records = array();
			while (false !== ($filename = readdir($handle))) {
				$imageID = $this->importimage($folderID, $filename, $subdir);
				if ($imageID) $records[] = $imageID;
			}
			closedir($handle);

			// WRITE IMAGE DATABASE AND UPDATE
			if ($records && ($this->write_iDB('new', $records) > 0))
				$imported = count($records);
		}

		// DISPLAY STATUS MESSAGE
		$foldername	= $this->getFolderName($folderID);
		$cimages		= (string)count($this->all_images);
		$message		= sprintf('Summary: Imported %d file(s) to \'%s\'. Gallery now contains %d images',
								$imported,
								$foldername,
								$cimages
						  );
		$this->log($message);
		if ($imported < 1) {
			$message = $this->lang['nofilestoimport'];
			$errtype = 1;
		} else {
			$message = sprintf('%d %s<div style="margin-top:8px"><a href="%s?fID=%d">%s \'%s\'</a></div>',
								$imported,
								$this->lang['filesimported'],
								ADMIN_INDEX,
								$folderID,
								$this->lang['backtofolder'],
								$foldername
						  );
			$errtype = 0;
		}
		$this->displaystatus($message, $errtype);
		return $imported;
	}

	//
	// IMPORT NEW IMAGE ACTION
	// kh_mod 0.2.0, changed
	function importimage($folderID,$filename,$subdir,$tmp_name='') {
		$extension = substr(strrchr($filename, '.'), 1);
		$extension = strtolower($extension);
		$filepath  = $this->get_path($filename, $subdir);

		// EXCLUSION CRITERIONS
		if (empty($extension))													return false;	// extension empty
		if (strpos($this->extensions,$extension) === false)			return false;	// extension forbidden
		if ($tmp_name && !move_uploaded_file($tmp_name,$filepath))	return false;	// can't import new file
		if (strpos($filename, '_medium')  !== false)						return false;	// medium image
		if (strpos($filename, '_thumb')   !== false)						return false;	// thumb image		
		if ($imageID = $this->search_iDB($filename,6,$subdir,7))							// image alredy imported
			return ($tmp_name && $this->rebuildID($imageID))? $imageID:false;			// rebuild image?

		// LOG ENTRY - IMPORT
		$filesize = filesize($filepath);
		$this->log(sprintf('Importing \'%s\' (%d Bytes)', $filename, $filesize));

		// GENERATE NEW THUMBNAIL AND MEDIUM IMAGE
		@chmod($filepath, 0644);
		$modify  = (isset($_POST['thumb'])  && (int)$_POST['thumb']	 === 1)? 1:0;
		$modify |= (isset($_POST['medium']) && (int)$_POST['medium'] === 1)? 2:0;
		$modify |= (isset($_POST['delete']) && (int)$_POST['delete'] === 1)? 4:0;
		$finfo	= $this->resizeImage($filename, $subdir, $modify);

		// SET UPLOAD/IMPORT OPTIONS
		$_SESSION[GALLERY_ID]['import_modify'] = $modify;

		// NEW DATABASE ENTRY, kh_mod 0.3.1, changed
		if (is_array($finfo)) {
			$log_msg = ($finfo['error'] & 1)?
						  ''
						  :
						  date('Ymd, H:i:s - ') .' * finished creating thumbnail'. "\n";

			if ($modify & 4)  $filesize = filesize($filepath);			// delete original image?
			$imageID = ++$this->autoid;
			$this->all_images[$imageID] = array(
													$imageID,						// image autoid			[0]
													$folderID,						// contained in folder		[1]
													'',								// image headline			[2]
													'',								// image description		[3]
													time(),							// publishing date			[4]
													1,									// sorting position			[5]
													$filename,						// file name				[6]
													$subdir,							// subdir path				[7]
													$finfo['image']['width'],	// image width			[8]
													$finfo['image']['height'],	// image height			[9]
													$finfo['thumb']['width'],	// thumb width			[10]
													$finfo['thumb']['height'],	// thumb height			[11]
													$filesize,						// image file size			[12]
													'',								// exif date				[13]
													'',								// Photographer			[14]
													0,									// Bookmarked				[15]
													$finfo['code']					// file code				[16]
												 );
			$date		= date('Ymd, H:i:s');
			$log_msg.= $date .' -  * finished importing \''. $filename ."'\n";
			$log_msg.= $date .' -  * finished adding file to database'. "\n";
			$this->log($log_msg, true);
			return $imageID;
		}
		else {
			$this->log('ERROR: Can\'t import \''. $filename .'\' ('. $filesize .' Bytes)');
			return false;
		}
	}

	// SEARCH ITEMS IN IMAGE DATABASE
	// kh_mod 0.2.0, add
	function search_iDB($string1, $field1, $string2, $field2) {
		if (is_array($this->all_images))
		foreach ($this->all_images as $record) {
			if ($record[$field1] === $string1 && $record[$field2] === $string2)
				return $record[0];
		}
		return false;
	}

	//
	// GET FOLDER NAME
	// kh_mod 0.2.0, changed
	function getFolderName($folderID) {
		if ($folderRC = $this->all_folders[$folderID])
			return ((int)$folderRC[0] === 1)? $this->lang['root']:$folderRC[2];
		else
			return '';
	}

	//
	// GET FOLDER ICON
	// kh_mod 0.2.0, add, 0.4.0 changed
	function getFolderIcon($folderID, $defaultClass=false) {
		$iconfile = '';
		$selected = '';
		$imageID	 = (int)$this->all_folders[$folderID][6];
		if ($imageID > 0 && $folderID > 1) {
			$filename = $this->all_images[$imageID][6];
			$subdir	 = $this->all_images[$imageID][7];
			if ($iconfile = $this->get_path($filename, $subdir, 'thumb')) {
				$selected	  = $iconfile;
				$thumb_width  = (int)$this->all_images[$imageID][10];
				$thumb_height = (int)$this->all_images[$imageID][11];
				$class		  = 'thumb';
			}
			else $imageID	  = 0;
		}

		// DISPLAY STANDARD FOLDER ICON?
		if (!$iconfile || !is_readable($iconfile) || ($this->foldersetting & 16)) {
			$iconfile	  = ADMIN_IMAGES .'folder.gif';
			$fileInfo	  = getimagesize($iconfile);
			$thumb_width  = $fileInfo[0];
			$thumb_height = $fileInfo[1];
			$class		  = $defaultClass? 'thumb_default':'';
		}

		// BUILD HTML ATTRIBUTES
		$attrb = sprintf('class="%s" width="%d" height="%d"',
						$class,
						$thumb_width,
						$thumb_height
					);

		return array('id'		=> $imageID,	// image ID for folder icon
						 'path'	=> $iconfile,	// icon path for dislaying
						 'attrb'	=> $attrb,		// width, height and class for thumb
						 'thumb'	=> $selected	// selceted thumb for folder icon
				 );
	}

	//
	// IMPORT THE SERVER DIRECTORY STRUCTURE
	// kh_mod 0.3.0, add
	function importdirs($infolder=1, $subdir='') {

		$sublevel = preg_match_all('°/[_\w]+°i', $subdir, $treffer);
		$subdirs	 = $this->get_subdirs($subdir, $sublevel);
		$fid		 = $infolder;
		$records  = array();
		foreach($subdirs as $dir) {

			// DIRECTORY SUB LEVEL
			$newlevel = $dir['level'];
			if ($newlevel > $sublevel) $structure[$newlevel] = $fid;
			$infolder = $structure[$newlevel];
			$sublevel = $newlevel;

			// FOLDER ALREADY EXISTS?
			if ($folders = $this->select($dir['name'], $this->all_folders, 2))
			if (count($this->select($infolder, $folders, 1)) === 1) {
				$fid = $folders[0][0]; continue;
			}

			// CREATE FOLDER RECORD
			$fid = ++$this->folderautoid;
			$this->all_folders[$fid] = array(
										$fid,				// folderID						[0]
										$infolder,		// in folder (#id)			[1]
										$dir['name'],	// folder name					[2]
										'',				// introtext					[3]
										time(),			// timestamp (publish off)	[4]
										1,					// folder position			[5]
									  -1,					// imageID for folder icon	[6]
										6,					// folder sort mode			[7]
										'',				// password						[8]
										0,					// number of cols				[9]
										0,					// number of rows				[10]
										0					// folder type					[11]
									);
			$records[] = $fid;
		}

		// SAVE NEW FOLDERS AND DISPLAY MESSAGE, kh_mod 0.3.1 changed 
		if (count($records)) {
			// IMPORT NEW FOLDERS OK
			if ($this->write_fDB('new', $records) > 0) {
				$message = sprintf('Imported %d of %d folders', count($records), count($subdirs));
				$errtype = 0;
			}
			// IMPORT ERROR
			else {
				$message = sprintf('Couldn\'t import the %d new folders', count($records));
				$errtype = 3;
			}
		}
		else {
			$message = sprintf('There are no new folders to import!');
			$errtype = 1;
		}
		$this->displaystatus($message, $errtype);
	}

	//
	// MAKE A NEW FOLDER ACTION
	// kh_mod 0.2.0, changed
	function newfolder($infolder) {

		// IS NOT EXISTS FOLDER ID?
		if (!isset($this->all_folders[$infolder])) {
			$this->log('ERROR: Folder ID #'. $infolder .' not found! (function \'newfolder\')');
			$this->displaystatus($this->lang['nofolderid'] .' #'. $infolder, 3);
			return false;
		}

		// CLEAN REQUEST VALUES, kh_mod 0.3.0, changed
		list($password, $confirm) = $this->cleaninput($infolder);

		// SAVE NEW FOLDER VALUES
		$fid		= ++$this->folderautoid;
		$sortby	= $_REQUEST['sortby'];
		$sortby |= $_REQUEST['direction'] << 4;
		$this->all_folders[$fid] = array(
											$fid,								// folder autoid			[0]
											$infolder,						// move in folder (#id)		[1]
											$_REQUEST['name'],			// folder name			[2]
											$_REQUEST['introtext'],		// introtext				[3]
											$_REQUEST['publish'],		// timestamp	(publish off)	[4]
											$_REQUEST['position'],		// folder position			[5]
											$_REQUEST['icon'],			// imageID for folder icon	[6]
											$sortby,							// folder sort mode		[7]
											$password,						// password				[8]
											0,									// number of cols			[9]
											0,									// number of rows			[10]
											0									// folder type				[11]
										);

		// SET LOCALTHUMBS, kh_mod 0.3.0 add
		if ((int)$_POST['thumbgrid'] === 2) {
			$this->all_folders[$fid][9]  = (int)$_POST['cols'];
			$this->all_folders[$fid][10] = (int)$_POST['rows'];
		}

		// DISPLAY RESULT
		$parentfolder = $this->getFolderName($infolder);
		if ($this->write_fDB('new', array($fid)) > 0) {
			$this->log('Created folder \''. $_REQUEST['name'] .'\' in \''. $parentfolder .'\'');
			$this->status = $this->lang['foldercreated'] .' \''. $_REQUEST['name'] .'\'';
		} else {
			$this->log('Couldn\'t create folder \''. $_REQUEST['name'] .'\' in \''. $parentfolder .'\'');
			$this->status = sprintf('<div style="color:red">%s %s</div>',
									 $this->lang['error'],
									 $this->lang['iDB_error']
								 );
		}
		// NO PASSWORD CONFIRM
		if (!$confirm) {
			$this->status.= sprintf('<div style="margin-top:8px;color:#C69023">%s %s</div>',
									 $this->lang['warning'],
									 $this->lang['nopwdmatch']
								 );
		}
		$this->displaystatus();
	}

	//
	// FOLDER SETTING DIALOG
	// kh_mod 0.2.0, changed
	function editfolder($folderID, $page) {
		if (!isset($this->all_folders[$folderID])) {  // kh_mod 0.3.1, changed
			$this->displaystatus($this->lang['nofolderid'] .' #'. $folderID, 3);
			return 1;
		}

		// FOLDER VALUES
		$folderRC	= &$this->all_folders[$folderID];
		$parentID	= (int)$folderRC[1];
		$foldername = $folderRC[2];
		$publish	   = $this->time2date($folderRC[4], true);
		$introtext	= ($this->extendedset & 4)?	// WYSIWYG HTML-Editor
						  $folderRC[3]
						  :
						  $this->br2nl($folderRC[3]);
		$position   = $folderRC[5];
		$localcols  = $localrows = '';
		$globalthumbs = $localthumbs = '';
		if ($folderRC[9] > 0 || $folderRC[10] > 0) {
			$localthumbs = 'checked="checked"';
			$localcols	 = ($folderRC[9]  > 0)? $folderRC[9] : $this->lang['default'];
			$localrows	 = ($folderRC[10] > 0)? $folderRC[10]: $this->lang['default'];
		} else {
			$globalthumbs = 'checked="checked"';
		}

		// DISPLAY FOLDER DIALOG
		$icon = $this->getFolderIcon($folderID);
		$this->makeFolderlist($folderID);
		include(ADMIN_FOLDER .'admin2_editfolder.php');

		return $folderID;
	}

	//
	// UPDATE FOLDER ACTION
	// kh_mod 0.3.1, changed
	function updatefolder($list) {

		// MOVE TO FOLDER
		$move = (empty($_REQUEST['moveto']))?
				  -1
				  :
				  (int)$_REQUEST['moveto'];

		// ROOT FOLDER?, kh_mod 0.3.0, changed
		$movetoID = ((int)$list === 1)?
						'root'
						:
						((array_key_exists($move, $this->all_folders))? $move:false);
		$folderID = ((array_key_exists($list, $this->all_folders))? $list:false);

		// IS NOT EXISTS 'MOVETO' OR 'EDIT' FOLDER ID?
		if ($movetoID===false || $folderID===false) {
			$log_msg = 'ERROR: %s folder ID #%s not found! (function \'updatefolder\')';
			if ($movetoID===false) {
				$this->log($log_msg, 'Moveto', $move);
				$notfound = $move;
			}
			if ($folderID===false) {
				$this->log($log_msg, 'Edit', $list);
				$notfound = $list;
			}
			$this->displaystatus($this->lang['nofolderid'] .' #'. $notfound, 3);
			return false;
		}

		// CLEAN REQUEST VALUES
		$old_foldername = $this->all_folders[$folderID][2];
		list($password) = $this->cleaninput($movetoID, $old_foldername);	// kh_mod 0.3.0, changed

		// OLD PUBLISH DATE
		$publish = (isset($this->all_folders[$folderID][4]))?
		  			  (int)$this->all_folders[$folderID][4]
					  :
					  time();

		// NEW PUBLISH DATE
		$new_publish = $_REQUEST['publish'];
		do {
			if ($new_publish === false)				break;	// new publish date isn't valid
			if (abs($publish - $new_publish) < 60)	break;	// set no seconds of new date

			$publish = $new_publish;
		}
		while(0);

		// SAVE NEW VALUES
		$this->all_folders[$folderID][0]  = $folderID;
		$this->all_folders[$folderID][1]  = $movetoID;
		$this->all_folders[$folderID][2]  = $_REQUEST['name'];
		$this->all_folders[$folderID][3]  = $_REQUEST['introtext'];
		$this->all_folders[$folderID][4]  = $publish;
		$this->all_folders[$folderID][5]  = $_REQUEST['position'];
		$this->all_folders[$folderID][6]  = $_REQUEST['icon'];
		$this->all_folders[$folderID][7]  = $_REQUEST['sortby'];
		$this->all_folders[$folderID][7] |= $_REQUEST['direction'] << 4;
		if (!empty($password)) $this->all_folders[$folderID][8] = $password;

		// FOLDER TYPE: DEFAULT, kh_mod 0.3.0 add
		if (empty($this->all_folders[$folderID][11])) {
			// USE GLOBAL COLS AND ROWS DATA
			if ((int)$_POST['thumbgrid'] === 1) {
				$this->all_folders[$folderID][9]  = 0;
				$this->all_folders[$folderID][10] = 0;
			}
			// USE LOCAL COLS AND ROWS DATA
			elseif ((int)$_POST['thumbgrid'] === 2) {
				$this->all_folders[$folderID][9]  = (int)$_POST['cols'];
				$this->all_folders[$folderID][10] = (int)$_POST['rows'];
			}
		}

		// DELETE PASSWORD
		if ((int)$_REQUEST['deletepassword'] === 1) {
			$this->all_folders[$folderID][8] = '';
		}

		// SET THE POSITIONS OF SUB FOLDERS NEW, kh_mod 0.3.0, add
		if ($_REQUEST['generate_fPos'] === 'ok') $this->setFolderPositions($folderID);

		// SET THE POSITIONS OF IMAGES NEW, kh_mod 0.1.0, add
		if ($_REQUEST['generate_iPos'] === 'ok') $this->setImagePositions($folderID);

		// WRITE FOLDER DATABASE AND UPDATE, kh_mod 0.3.1, changed
		$fDB_ok = ($this->write_fDB('upd', array($folderID)) > 0);
		if ($fDB_ok) {
			$this->log('Update of folder \''. $old_foldername .'\' complete.');
			$this->displaystatus($this->lang['folderupdated']);
		} else {
			$this->log('Couldn\'t update folder \''. $old_foldername .'\'!');
			$this->displaystatus($this->lang['foldererror'], 3);
		}
		// end
	}

	//
	// CLEAN REQUEST VALUES FOR FOLDER
	// kh_mod 0.3.2, changed
	function cleaninput($moveto, $old_fname='') {

		// CHECK NEW FOLDER NAME
		$fname = $this->charfix($_REQUEST['name']);
		$fname = substr($fname,0,255);	// kh_mod 0.1.0 b3, add

		// IS NOT ROOT FOLDER?
		if ($moveto !== 'root') {
			// EMPTY FOLDER NAME?
			if ($this->string_empty($fname)) {
				$fname = ($this->string_empty($old_fname))?
							'autonamed#'. $moveto .'_'. rand(1000,9999)
							:
							$old_fname;
			}
			// SAME FOLDER NAMES FORBIDDEN?
			if ($old_fname !== $fname && !($this->extendedset & 2)) {
				foreach($this->all_folders as $record) {
					if (((int)$record[1] === $moveto) && ($record[2] === $fname)) {
						$fname.= '_autorenamed'. rand(1000,9999);
						break;
					}
				}
			}
		}

		// CLEAN INPUT VALUES, kh_mod 0.3.2 changed
		$_REQUEST['name']		  = $fname;
		$_REQUEST['sortby']    = (int)$_REQUEST['sortby'] & 15;
		$_REQUEST['direction'] = ($_REQUEST['direction']) ? 1:0;
		$introtext				  = $this->charfix($_REQUEST['introtext']);
		$introtext				  = substr($introtext, 0, 40960);
		$ln_break				  = '/\r\n|\r|\n/';									// line break stand for data record end
		$ln_replace				  = ($this->extendedset & 4)? '':'<br />';	// WYSIWYG HTML-Editor used?
		$_REQUEST['introtext'] = preg_replace($ln_break, $ln_replace, $introtext);
		$_REQUEST['position']  = (int)$_REQUEST['position'];
		if (($folder_icon	= (int)$_REQUEST['icon']) < 0)
		if ($select_icon  = (int)$_REQUEST['select_icon'] & 7) {				// is set random, first or latest image?
			$folder_icon = ($_REQUEST['incsubs'])?									// inkl. sub folders?
								-($select_icon | 8)
								:
								-$select_icon;
		}
		$_REQUEST['icon'] = $folder_icon;

		// CONVERT DATE TO TIMESTAMP, kh_mod 0.1.0 b3, changed
		$_REQUEST['publish'] = $this->date2time($_REQUEST['publish']);

		// CHECK PASSWORD/CONFIRM AND ENCRYPT 
		$password = '';
		$confirm  = false;
		do {
			$input = trim($_REQUEST['password']);
			if (empty($input)) {	$confirm=true;	break; }
			$password = md5(strrev(md5($input)));

			// FOR NEW FOLDERS ONLY
			if (!isset($_REQUEST['confirm']))	break;
			if ($password === md5(strrev(md5(trim($_REQUEST['confirm'])))))
				$confirm  = true;
			else
				$password = '';
		}
		while(0);

		return array($password, $confirm);
	}

	//
	// GENERATE IMAGE POSITION NUMBERS ACTION
	// kh_mod 0.3.0, add
	function setFolderPositions($folderID, $step=10) {
		$sortmode = $this->getFolderSortMode($folderID);
		$folders  = $this->select($folderID, $this->all_folders, 1, $sortmode);
		if (count($folders) > 1) {
			$pos = 10;
			$records = array();
			foreach ($folders as $record) {
				if ((int)$record[5] < 0) continue;	// locked folder?

				$subfolderID = (int)$record[0];
				$this->all_folders[$subfolderID][5] = $pos;
				$records[] = $subfolderID;
				$pos+=$step;
			}
			// WRITE FOLDER DATABASE AND UPDATE
			$this->write_fDB('upd', $records);
		}
	}

	//
	// GENERATE IMAGE POSITION NUMBERS ACTION
	// kh_mod 0.3.0, changed
	function setImagePositions($folderID, $step=10) {
		$sortmode = (int)$this->all_folders[$folderID][7];
		$images   = $this->select($folderID, $this->all_images, 1, $sortmode);
		if (count($images) > 1) {
			$pos = 10;
			$records = array();
			foreach ($images as $record) {
				if ((int)$record[5] < 0) continue;	// locked image?

				$imageID = (int)$record[0];
				$this->all_images[$imageID][5] = $pos;
				$records[] = $imageID;
				$pos+=$step;
			}
			// WRITE IMAGE DATABASE AND UPDATE
			$this->write_iDB('upd', $records);
		}
	}

	//
	// SET NEW IMAGE SORTING ACTION
	// kh_mod 0.3.0, add
	function setSortingPositions($folderID) {
		do {
			// INIT SORT RESULT
			$sortOK = 'nosort';

			// SORTING NOT ACTIVE?
			if ($_SESSION[GALLERY_ID]['sortstart'] !== true)	break;

			// RESET SORTING START
			$_SESSION[GALLERY_ID]['sortstart'] = false;

			// SORTING RESULT NOT EXISTS?
			if (!is_array($_SESSION[GALLERY_ID]['sorting']))	break;

			// VALID FOLDERID NOT EXISTS?
			isset($this->all_folders[$folderID]) or die('Wrong Folder ID, #'. $folderID);

			// GET CURRENT SORT MODE
			$images_sortby  = ($this->all_folders[$folderID][7] & 15);
			$images_sortway = ($this->all_folders[$folderID][7] & 16)? 1:0;

			// SORT DESCENDING?
			$new_sort = ($images_sortway)?
							array_reverse($_SESSION[GALLERY_ID]['sorting'])
							:
							$_SESSION[GALLERY_ID]['sorting'];

			// POSITIVE POSITIONS
			$newpos  = 0;
			$negativ = array();
			foreach ($new_sort as $imageID) {
				if ($this->all_images[$imageID][5] < 0) { $negativ[]= $imageID; continue; }
				$this->all_images[$imageID][5] = $newpos += 10;
			}
			// NEGATIVE POSITIONS
			if ($negativ) {
				$newpos = 0;
				foreach (array_reverse($negativ) as $imageID) {
					$this->all_images[$imageID][5] = $newpos -= 10;
				}
			}

			// SAVE NEW POSITIONS
			$sortOK = ($this->write_iDB('upd', $new_sort) > 0)? true:false;
			if ($images_sortby !== 5) {
				$this->all_folders[$folderID][7]  = 5;
				$this->all_folders[$folderID][7] |= $images_sortway << 4;
				$this->write_fDB('upd', array($folderID));
			}
		} while(0);

		// DISPLAY RESULT MESSAGE
		if ($sortOK === 'nosort')
			$this->displaystatus($this->lang['nonewsorting'], 1);
		elseif ($sortOK === true)
			$this->displaystatus($this->lang['newsortingsaved'], 0);
		else
			$this->displaystatus($this->lang['sortingnotsaved'], 3);
	}

	//
	// REBUILD THUMBS AND MEDIUMS IMAGES ACTION
	// kh_mod 0.1.0, add
	function rebuildfolder($folderID) {
		$n_updated = 0;
		$images = $this->select($folderID, $this->all_images, 1);
		if (count($images) > 0) {
			$records = array();
			foreach ($images as $record) {
				$imageID = (int)$record[0];
				if ($this->rebuildID($imageID)) $records[] = $imageID;	// kh_mod 0.3.0, changed
			}
			// WRITE IMAGE DATABASE AND UPDATE, kh_mod 0.3.1, changed
			$n_updated = count($records);
			if ($n_updated && ($this->write_iDB('upd', $records) < 1)) {
				$this->displaystatus($this->lang['iDB_error'], 3);
			}
		}
		else {
			$this->displaystatus($this->lang['rebuildempty'], 1);
		}
		return $n_updated;
	}

	//
	// ASK DELETE FOLDER DIALOG
	// kh_mod 0.3.1, changed
	function askdelfolder($delfolder, $folderID, $page) {
		if (!isset($this->all_folders[$delfolder])) {
			$this->displaystatus($this->lang['nofolderid'] .' #'. $delfolder, 3);
			return false;
		}

		// IS FOLDER EMPTY?
		$images  = $this->select($delfolder, $this->all_images, 1);
		$folders = $this->select($delfolder, $this->all_folders, 1);
		if (count($folders) > 0 || count($images) > 0) {
			$this->displaystatus($this->lang['foldernotempty'], 2);
			return false;
		}

		// GET FOLDER ICON ITEMS (ARRAY)
		$icon 	= $this->getFolderIcon($delfolder, true);
		$cancel	= ADMIN_INDEX .'?fID='. $folderID .'&amp;page='. $page;
		$href_ok	= ADMIN_INDEX .'?erasefolder='. $delfolder .'&amp;page='. $page;
		include(ADMIN_FOLDER .'admin2_deletefolder.php');
	}

	//
	// DELETE FOLDER ACTION
	// kh_mod 0.3.1, changed
	function erasefolder($folderID) {
		if (!isset($this->all_folders[$folderID])) {
			$this->displaystatus($this->lang['nofolderid'] .' #'. $folderID, 3);
			return 1;
		}

		$parentID   = $this->all_folders[$folderID][1];
		$foldername = $this->all_folders[$folderID][2];
		$parentname = $this->getFolderName($parentID);
		$del_ok = $this->arrayDelete($this->all_folders,$folderID);

		// WRITE FOLDER DATABASE AND UPDATE
		if ($del_ok && ($this->write_fDB('del', array($folderID)) > 0)) {
			$this->log('Folder \''.$foldername.'\' deleted from \''. $parentname .'\'');
			$this->displaystatus($this->lang['folderdeleted'] .' \''. $foldername .'\'');
		} else {
			$this->log('Couldn\'t delete folder \''.$foldername.'\' from \''. $parentname .'\'');
			$this->displaystatus($this->lang['foldernotdeleted'], 3);
		}
		return ($del_ok)? $parentID:$folderID;
	}

	//
	// CREATE THUMBNAIL AND MEDIUM IMAGE
	// kh_mod 0.3.1, changed
	function resizeImage($filename, $subdir, $modify=3) {
		$img_filepath = $this->get_path($filename, $subdir);
		if (!is_readable($img_filepath))
		{
			$this->log('ERROR: File \''. $img_filepath .'\' not readable!');
			$this->displaystatus(sprintf($this->lang['imagenotread'], $img_filepath), 3);
			return false;
		}

		// SOURCE IMAGE
		$fileInfo	= getimagesize($img_filepath);					// takes about 0.2 sec. for 50 MB
		$img_width	= $fileInfo[0];										// video or sound file!
		$img_height	= $fileInfo[1];
		$imageType	= array(1=>'gif',2=>'jpg',3=>'png');
		$fileExt		= ($imageExt = $imageType[$fileInfo[2]])?
							$imageExt
							:
							substr(strrchr($filename, '.'), 1);
		$fileCode	= $this->getContentCode($fileExt);

		switch ($imageExt) {
			case 'gif' : $imagecreate	= 'imagecreatefromgif';
							 $imageformat	= 'imagegif';
							 $imagequality	= NULL;
							 break;
			case 'jpg' : $imagecreate	= 'imagecreatefromjpeg';
							 $imageformat	= 'imagejpeg';
							 $imagequality	= abs($this->thumbquality);
							 break;
			case 'png' : $imagecreate	= 'imagecreatefrompng';
							 $imageformat	= 'imagepng';
							 $imagequality	= floor(abs($this->thumbquality)/10);
							 $imagequality	= min($imagequality, 9);
							 break;

			// NO SUPPORTED IMAGE FORMAT
			case NULL  : $modify = 0;				// create no thumb or medium
							 // META FORMAT 'VIDEO'
							 if (($fileCode & 448) === $this->contentCodeTable('video')) {
								$img_width	= 400;	// canvas width
								$img_height = 300;	// canvas height
								break;
							 }
							 // META FORMAT 'AUDIO'
							 if (($fileCode & 448) === $this->contentCodeTable('audio')) {
								$img_width	= 400;	// canvas width
								$img_height =  23;	// canvas height
								break;
							 }
			default:	$this->log('Notice: No supported image content \''.$filename .'\' to import.');
						return array (
									'image' => array('width'=>-1, 'height'=>-1),
									'thumb' => array('width'=>-1, 'height'=>-1),
									'code'  => 0,
									'error' => 3
								 );
		}

		// TIME SETTING
		@set_time_limit(60);

		// INIT RETURN VALUES
		$result = array (
						'image' => array('width'=>$img_width, 'height'=>$img_height),
						'thumb' => array('width'=>$img_width, 'height'=>$img_height),
						'code'  => $fileCode,
						'error' => 0
					 );

		// PREPARE MEDIUM VALUES?
		if (($modify & 2) && $this->mediumimage > 0) {
			// OVERWRITE (DELETE) THE ORIGINAL IMAGE?
			$key = ($modify & 4)? 'image':'medium';
			$create[$key] = array(2, $this->mediumimage, $this->mediumimage);
		}
		// PREPARE THUMB VALUES
		$create['thumb'] = array($modify & 1, $this->thumbMaxWidth, $this->thumbMaxHeight);

		// CREATE THUMB AND VALUE IMAGES
		foreach ($create as $key=>$imgCreateRC) {

			// MAX THUMB RESP. MEDIUM IMAGE SIZE
			$max_width  = $imgCreateRC[1];
			$max_height	= $imgCreateRC[2];

			// ORIGINAL GREATER THAN MAX THUMB RESP. MEDIUM SIZE
			if ($img_width > $max_width || $img_height > $max_height) {

				// CALCULATE THE SMALLER IMAGE SIDE
				if ($img_width < $img_height) {
					$max_width  = round($img_width/$img_height * $max_height);
				} else {
					$max_height = round($img_height/$img_width * $max_width);
				}

				// SAVE CALCULATED SIZE FOR RETURN
				$result[$key] = array('width'=>$max_width, 'height'=>$max_height);
			}
			elseif ($key === 'thumb') {
					$max_width  = $img_width;	// use the original size
					$max_height	= $img_height;	// use the original size
			}
			else continue;							// image to small to scale

			// DONT CREATE THUMBS?
			if (!$imgCreateRC[0])										continue;

			// GET IMAGE PATH
			$dst_filepath = $this->get_path($filename, $subdir, $key);

			// CREATE SOURCE IMAGE
			if (!isset($src_img)) {

				// MEMORY CONSUMPTION OF UNPACKED IMAGE
				$unpackedImageMemory = $img_width * $img_height;	// original image
				$unpackedImageMemory+= $max_width * $max_height;	// to create image
				$unpackedImageMemory*= $fileInfo['bits'] * 0.375;	// color part (3/8)
				$unpackedImageMemory*= 1.65;								// processing consumption

				// CHECK AVAILABLE MOMORY
				if ($this->currentMemory['available'] > 0		&&
					 $this->currentMemory['available'] < $unpackedImageMemory)
				{
					$message = sprintf($this->lang['memorytoosmall'],
										$filename,
										$this->convertBytes($this->currentMemory['limit'], 0)
								  );
					$result['error'] |= ($key === 'thumb')? 1:2;	continue;	// next task
				}

				// CREATE SOURCE IMAGE
				if (!$src_img = $imagecreate($img_filepath)) { $result['error'] = 3; break; }
			}

			// CREATE NEW PICTURE
			$dst_img = imagecreatetruecolor($max_width, $max_height);
			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $max_width, $max_height, $img_width, $img_height);
			touch($dst_filepath); // workaround for 'safe mode' bug
			$imageformat($dst_img, $dst_filepath, $imagequality);
			imagedestroy($dst_img);
		}

		// ERROR MESSAGE
		if ($result['error']) {
			$this->displaystatus($message, 2);
			$this->log(' * \''. $filename .'\' too large to scale');
		}

		// RETURN IMAGE AND THUMB SIZES
		return $result;
	}

	//
	// ROTATE ORIGINAL IMAGE ONLY
	// kh_mod 0.3.1, changed
	function rotateImage($imageID) {

		// EXISTS IMAGEROTATE FUNCTION
		if (!function_exists('imagerotate')) {
			$this->log('ERROR: GD function imagerotate missing!');
			$this->displaystatus('GD function imagerotate missing!', 3);
			return false;
		}

		if (!isset($this->all_images[$imageID])) {
			$this->displaystatus(sprintf($this->lang['nopictureid'], $imageID), 3);
			return false;
		}

		// GET IMAGE PATH
		$filename = $this->all_images[$imageID][6];
		$subdir	 = $this->all_images[$imageID][7];
		$filepath = $this->get_path($filename, $subdir);

		$rot_ok = false;
		do {
			// IMAGE DIRECTORY PERMISSIONS
			if ((fileperms(dirname($filepath)) & 0777) !== 0777)	break;

			// IMAGE FILE READABLE
			if (!is_readable($filepath))									break;

			// IMAGE INFO
			if (!$fileInfo = getimagesize($filepath))					break;

			// GET IMAGE FORMAT
			$imagetype = array(1=>'gif',2=>'jpg',3=>'png');
			$extension = $imagetype[$fileInfo[2]];

			// MEMORY CONSUMPTION OF UNPACKED IMAGE
			$unpackedImageMemory = $fileInfo[0] * $fileInfo[1];	// original image
			$unpackedImageMemory*= 2;										// rotate image
			$unpackedImageMemory*= $fileInfo['bits'] * 0.375;		// color part (3/8)
			$unpackedImageMemory*= 1.65;									// processing consumption

			// CHECK AVAILABLE MOMORY
			if ($this->currentMemory['available'] > 0		&&
				 $this->currentMemory['available'] < $unpackedImageMemory)
			{
				$message = sprintf($this->lang['memorytoosmall'],
								  $filename,
								  $this->convertBytes($this->currentMemory['limit'], 0)
							  );
				$this->displaystatus($message, 2);
				$this->log('ERROR: \''. $filename .'\' too large to rotate'); break;
			}

			// ROTATE IMAGE
			$degrees = ($_REQUEST['direction'] === 'left')? 90:-90;
			if ($extension === 'jpg') {
				$source = imagecreatefromjpeg($filepath);
				if ($rotate = imagerotate($source, $degrees, 0)) {
					imagedestroy($source);	// reclaim main memory
					unlink($filepath);		// to avoid denied permission
					$rot_ok = imagejpeg($rotate, $filepath, 100);
				}
			} elseif ($extension === 'png') {
				$source = imagecreatefrompng($filepath);
				if ($rotate = imagerotate($source, $degrees, 0)) {
					imagedestroy($source);	// reclaim main memory
					unlink($filepath);		// to avoid denied permission
					$rot_ok = imagepng($rotate, $filepath);
				}
			} elseif ($extension === 'gif' && (imagetypes() & IMG_GIF)) {
				$source = imagecreatefromgif($filepath);
				if ($rotate = imagerotate($source, $degrees, 0)) {
					imagedestroy($source);	// reclaim main memory
					unlink($filepath);		// to avoid denied permission
					if (function_exists('imagegif'))
						$rot_ok = imagegif($rotate, $filepath);
					else
						$rot_ok = imagepng($rotate, $filepath);
				}
			}
		}
		while(0);

		// ROTATE OK, UPDATE THUMB AND MEDIUM IMAGE
		if ($rot_ok) {
			$this->log('Rotated image #'. $imageID .' to '. $direction .' (\''. $filename .'\')');
			$this->displaystatus($this->lang['imagerotated'] .' '. $degrees .'&#176;');

			// WRITE IMAGE DATABASE AND UPDATE
			if (!$this->rebuildID($imageID) || ($this->write_iDB('upd', array($imageID)) < 1)) {
				$this->log('ERROR: Couldn\'t update image database!');
				$this->displaystatus(sprintf('%s <a href="%s?rebuildID=%d&amp;editID=%d">%s</a>',
												$this->lang['iDB_error'],
												ADMIN_INDEX,
												$imageID,
												$imageID,
												$this->lang['tryitagain']
											), 3);
			}
		}
		elseif ($extension === 'gif' && !(imagetypes() & IMG_GIF)) {
			$this->log('ERROR: GIF files can\'t be rotated due to limitations in GD lib!');
			$this->displaystatus($this->lang['gifnotrotated'], 3);
		}
		else {
			$this->log('Couldn\'t rotate image #'. $imageID .' to '. $direction);
			$this->displaystatus($this->lang['imagenotrotated'], 3);
		}
	}

	//
	// DISPLAY SETUP DIALOG
	// kh_mod 0.3.1, changed
	function setup($folderID, $page) {
		include(ADMIN_FOLDER .'admin3_setup.php');
	}

	//
	// COUNT OLD DATABASE ENTRIES
	// kh_mod 0.3.0, add
	function count_oldDB($db) {
		$num_records = 0;
		do {
			if (!is_file($db))				break;
			if (!$fp = @fopen($db,'rb'))	break;
			if (!is_resource($fp))			break;
			while (!feof($fp)) {
				if (fgets($fp,2) !== '*')	continue;
				if (fgetcsv($fp,4096,'*')) $num_records++;
			}
			fclose($fp);
		} while(0);
		return $num_records;
	}

	//
	// GET, CLEAN AND SAVE SETTING VALUES
	// kh_mod 0.3.1, changed
	function preparesetup() {

		// INITIALIZE MESSAGE BUFFER
		$message = '';

		// CLEAN AND PREPARE VALUES
		$this->websitelink			= $this->cleanLink(substr($this->charfix($_POST['websitelink']),0,4096));
		$this->websitetext			= substr($this->charfix($_POST['websitetext'], false, true),0,1024);
		$this->indexfile				= substr($this->charfix($_POST['indexfile'], '/'),0,4096);
		$this->imagefolder			= substr($this->charfix($_POST['imagefolder'], '/'),0,4096);
		$this->commentsets			= ($_POST['showcomments'])?	  1:0;
		$this->commentsets		  |= ($_POST['commentmode'])?	  1<<1:0;
		$this->commentsets		  |= ($_POST['showmail'])?		  1<<2:0;
		$this->commentsets		  |= ($_POST['allowcomments'])? 1<<3:0;
		$this->commentsets		  |= ($_POST['hidecommform'])?  1<<4:0;
		$this->commentsets		  |= ($_POST['lockcomments'])?  1<<5:0;
		$this->commentsets		  |= ($_POST['sendmail'])?		  1<<6:0;
		$this->commentsets		  |= ($_POST['jsvalidate'])?	  1<<7:0;
		$this->commentsets		  |= ($_POST['cpvalidate'])?	  1<<8:0;
		$this->commentsets		  |= ($_POST['whitelist'])?	  1<<9:0;
		$this->commentsets		  |= ($_POST['blacklist'])?	  1<<10:0;
		$this->commentsets		  |= ($_POST['logip'])?			  1<<11:0;
		$this->metasetting			= ($_POST['_gallery'])?			1:0;
		$this->metasetting		  |= ($_POST['_foldername'])?	1<<1:0;
		$this->metasetting		  |= ($_POST['_imagename'])?	1<<2:0;
		$this->metasetting		  |= ($_POST['_imagetitle'])?	1<<3:0;
		$this->metasetting		  |= ($_POST['meta_index'])?	((int)$_POST['_index'] &3)<<4:0;
		$this->metasetting		  |= ($_POST['meta_follow'])?	((int)$_POST['_follow']&3)<<6:0;
		$this->metasetting		  |= ($_POST['meta_archive'])? 1<<8:0;
		$this->metasetting		  |= ($_POST['meta_snippet'])? 1<<9:0;
		$this->metasetting		  |= ($_POST['http_index'])?	 1<<10:0;
		$this->metasetting		  |= ($_POST['http_follow'])?  1<<11:0;
		$this->metasetting		  |= ($_POST['http_archive'])? 1<<12:0;
		$this->metasetting		  |= ($_POST['http_snippet'])? 1<<13:0;
		$this->showexif				= ($_POST['_make'])?				1:0;
		$this->showexif			  |= ($_POST['_model'])?		1<<1:0;
		$this->showexif			  |= ($_POST['_expotime'])?	1<<2:0;
		$this->showexif			  |= ($_POST['_expocomp'])?	1<<3:0;
		$this->showexif			  |= ($_POST['_aperture'])?	1<<4:0;
		$this->showexif			  |= ($_POST['_focallen'])?	1<<5:0;
		$this->showexif			  |= ($_POST['_iso'])?			1<<6:0;
		$this->showexif			  |= ($_POST['_flash'])?		1<<7:0;
		$this->showexif			  |= ($_POST['_original'])?	1<<8:0;
		$this->showexif			  |= ($_POST['_software'])?	1<<9:0;
		$this->showexif			  |= ($_POST['_datetime'])?	1<<10:0;
		$this->showexif			  |= ($_POST['_colorspace'])?	1<<11:0;
		$this->showexif			  |= ($_POST['_artist'])?		1<<12:0;
		$this->showexif			  |= ($_POST['_gps'])?			1<<13:0;
		$this->layoutsetting			= (int)$_POST['clickonimage'] & 3;
		$this->layoutsetting		  |= ($_POST['transgif'])?		1<<2:0;
		$this->layoutsetting		  |= ($_POST['fileashead'])?	1<<3:0;
		$this->layoutsetting		  |= ($_POST['altattrib'])?	1<<4:0;

		$this->layoutsetting		  |= ((int)$_POST['fileformat'] & 15)<<6;
		$this->layoutsetting		  |= ($_POST['withextion'])?	1<<10:0;
		$this->marknew					= (int)$_POST['marknew'];
		$this->thumbquality			= min(abs((int)$_POST['thumbquality']), 100);
		$this->introwidth				= (int)$_POST['introwidth'];
		$this->introwidth			  .= ($_POST['introunit'] === '%')?  '%':'px';
		$this->mediumimage			= (int)$_POST['mediumimage'];
		$this->slideshowdelay		= (int)$_POST['slideshowdelay'];
		$this->navtype					= (int)$_POST['navtype'];
		$old_foldersetting			= $this->foldersetting;
		$this->foldersetting			= (int)$_POST['foldersort'] & 15;
		$this->foldersetting		  |= ($_POST['foldericons'])?		 1<< 4:0;		//    16
		$this->foldersetting		  |= ($_POST['displayheadline'])? 1<< 5:0;		//    32
		$this->foldersetting		  |= ($_POST['displayfile'])?		 1<< 6:0;		//    64
		$this->foldersetting		  |= ($_POST['clickcounter'])?	 1<< 7:0;		//   128
		$this->foldersetting		  |= ($_POST['commentcounter'])?	 1<< 8:0;		//   256
		$this->foldersetting		  |= ($_POST['thumbtooltip'])?	 1<< 9:0;		//   512
		$this->foldersetting		  |= ($_POST['categories'])?		 1<<10:0;		//  1024
		$this->foldersetting		  |= ($_POST['categories_icon'])? 1<<11:0;		//  2048
		$this->foldersetting		  |= ($_POST['categories_shad'])? 1<<12:0;		//  4096
		$this->foldersetting		  |= ($_POST['categories_desc'])? ((int)$_POST['categories_align']&3)<<13:0;	//  8192
		$this->foldersetting		  |= ($_POST['categories_subs'])? 1<<15:0;		// 32768

		// GALLERY HEADLINE
		$search  = array('&lt;','&gt;','&quot;','&amp;');
		$replace = array('<','>','"','&');
		$this->gallerytitle = $this->charfix($_POST['gallerytitle']);
		$this->gallerytitle = str_replace($search, $replace, $this->gallerytitle);

		// GALLERY COPYRIGHT
		$this->copyright = $this->charfix($_POST['copyright']);
		$this->copyright = str_replace($search, $replace, $this->copyright);

		// COMMENT COUNTER SWITCH OFF?
		if (($old_foldersetting & 256) && !($this->foldersetting & 256)) {
			$Counter = new MG2Counter($this->sqldatabase);
			$resetOK = $Counter->resetComments();
			// RESET FAILED?
			if (!$resetOK || $resetOK === -1) $this->foldersetting |= 1<<8;
		}

		// WEBMASTER EMAIL
		$newmail	= trim($_POST['adminemail']);
		$cClass	= '[_a-z'.$this->lang['specialchars'].'0-9-]';
		$regexp	= '/^'.$cClass.'+(\.'.$cClass.'+)*@'.$cClass.'+(\.'.$cClass.'+)*\.([a-z]{2,5})$/i';
		if (@preg_match($regexp, $newmail))
			$this->adminemail = $newmail;
		elseif ($newmail !== '')
			$message.= '<div style="margin-top:8px;color:red">'.$this->lang['emailerror'].'</div>';

		// SET ACTIV SKIN, kh_mod 0.1.0 b3, add
		$activeskin = $this->charfix($_POST["activeskin"], '/');
		if (!empty($activeskin) && is_readable('skins/'.$activeskin.'/templates')) {
			$this->activeskin = $activeskin;
		}

		// PASSWORD
		$oldpassword  = trim($_POST['oldpassword']);
		$newpassword  = trim($_POST['password']);
		$confpassword = trim($_POST['passwordconfirm']);
		if ($oldpassword != '' || $newpassword != '') {
			if ($newpassword === $confpassword && md5(strrev(md5($oldpassword))) === $this->adminpwd) {
				$this->adminpwd = md5(strrev(md5($newpassword)));
				$_SESSION[GALLERY_ID]['adminpwd'] = $this->adminpwd;
				$message.= '<div style="margin-top:8px">'. $this->lang['pwdchanged'].'</div>';
			}
			else {
				$message.= sprintf('<div style="margin-top:8px;color:#C69023">%s %s</div>',
									$this->lang['warning'],
									$this->lang['nopwdmatch']
							  );
			}
		}

		// ALLOW INACTIVE TIME IN MINUTES
		$newinactivetime = (int)$_POST['inactivetime'];
		$this->inactivetime = ($newinactivetime < 1)?
									 (int)$this->inactivetime
									 :
									 $newinactivetime;
		// SET INACTIVE TIME IN SECONDS
		$_SESSION[GALLERY_ID]['inactivetime'] = $this->inactivetime * 60;

		// THUMBNAIL SIZE
		if ((int)$_POST['thumbMaxWidth'] > 4 && (int)$_POST['thumbMaxHeight'] > 4) {
			$this->thumbMaxWidth  = (int)$_POST['thumbMaxWidth'];
			$this->thumbMaxHeight = (int)$_POST['thumbMaxHeight'];
		}
		else {
			$message.= sprintf('<div style="margin-top:8px;color:#C69023">%s %s</div>',
								$this->lang['warning'],
								$this->lang['nothumbsize']
						  );
		}

		// IMAGE COLS AND ROWS
		$this->imagecols = ((int)$_POST['imagecols'] < 1)?
								 max($this->imagecols, 1)
								 :
								 (int)$_POST['imagecols'];
		$this->imagerows = ((int)$_POST['imagerows'] < 1)?
								 max($this->imagerows, 1)
								 :
								 (int)$_POST['imagerows'];

		// ALLOWED FILE EXTENTIONS
		$this->extensions = strtolower(trim($_POST['extensions']));
		$this->extensions = strtr($this->extensions, array('"'=>'','\\'=>'',' '=>''));

		// GET DATE AND TIME FORMAT
		$this->dateformat = $this->getDateFormat((int)$_POST['dateformat']);
		$this->timeformat = $this->getTimeFormat((int)$_POST['timeformat']);

		// EXTENDEDSETTING
		$this->extendedset  = ($_POST['pwdrecursiv'])?		0:1;	// kh_mod 0.2.0 b2, changed
		$this->extendedset |= ($_POST['samefolders'])? 	1<<1:0;	// kh_mod 0.1.0, add
		$this->extendedset |= ($_POST['htmlarea'])?		1<<2:0;	// kh_mod 0.1.0, add
		$this->extendedset |= ($_POST['tooltips'])?		1<<3:0;	// kh_mod 0.1.0, add
		$this->extendedset |= ($_POST['calendar'])?		1<<4:0;	// kh_mod 0.1.0, add
		$this->extendedset |= ($_POST['seolink'])?		1<<5:0;	// kh_mod 0.3.0, add
		$this->extendedset |= ($_POST['ctype'])?			1<<6:0;	// kh_mod 0.3.0, add
		$this->extendedset |= ($_POST['adminmode'])?		1<<7:0;	// kh_mod 0.3.0, add

		// MOD VERSION
		if (is_readable(INC_FOLDER .'mg2_version.php')) {
			@include(INC_FOLDER .'mg2_version.php');
			if (preg_match('/^\d{1,2}\.\d{1,2}\.\d{1,2}.{0,5}$/', $modversion))
				$this->modversion = $modversion;
		}

		// WRITE MG2 SETTINGS
		$saveOk  = ($this->sqldatabase)?
					  $this->write2SQLTable('settings')
					  :
					  $this->write_sDBFlatfile('flat');
		$message = ($saveOk)?
					  $this->lang['settingssaved'] . $message
					  :
					  '<div style="color:red">'. $this->lang['nosettingssaved'] .'</div>'. $message;
		$this->displaystatus($message);
	}

	//
	// kh_mod 0.3.1, add
	// GET DATE FORMAT STRING
	function getDateFormat($idx=1) {

		$date = array(	1 => '%d.%m.%Y',
							2 => '%d. %b., %Y',
							3 => '%d. %b. %Y',
							4 => '%b. %d, %Y',
							5 => '%b. %e, %Y',
							6 => '%e.%m.%y',
							7 => '%m.%e.%y',
							8 => '%y%m%d',
							9 => '%Y%m%d',
						  10 => '%Y-%m-%d',
						  11 => '%d-%m-%Y',
						  12 => '%m-%d-%Y',
						  13 => '%d-%b-%Y',
						  14 => '%b-%d-%Y',
						  15 => '%d/%m/%Y',
						  16 => '%m/%d/%Y',
						  17 => '%e/%n/%y',
						  18 => '%n/%e/%y',
						  19 => '%d/%b/%Y',
						  20 => '%b/%d/%Y',
						  21 => '%d/%m %Y',
						  22 => '%m/%d %Y',
						  23 => '%d/%b %Y',
						  24 => '%b/%d %Y',
						  25 => '%n/%e %Y',
						  26 => '%e/%n %Y',
						  27 => '%e/%m %Y',
						  28 => '%n/%d %Y'
				  );

		return (isset($date[$idx]))?
				 $date[$idx]				// return the selected date string
				 :
				 $date[1];					// return the default date string
	}

	//
	// kh_mod 0.3.1, add, 0.4.0 b3 changed
	// GET TIME FORMAT STRING
	function getTimeFormat($idx=1) {

		$time = array(	1 => '%H:%M',			// (00 to 23)
							2 => '%H:%M:%S',		// (00 to 23)
							3 => '%i:%M %p',		// AM/PM		 (1 to 12)
							4 => '%i:%M:%S %p',	// AM/PM 	 (1 to 12)
							5 => '%i:%M %r',		// a.m./p.m. (1 to 12)
							6 => '%i:%M:%S %r',	// a.m./p.m. (1 to 12)
							7 => '%I:%M %p',		// AM/PM		 (01 to 12)
							8 => '%I:%M:%S %p',	// AM/PM		 (01 to 12)
							9 => '%I:%M %r',		// a.m./p.m. (01 to 12)
						  10 => '%I:%M:%S %r',	// a.m./p.m. (01 to 12)
				  );

		return (isset($time[$idx]))?
				 $time[$idx]				// return the selected time string
				 :
				 $time[1];					// return the default time string
	}

	//
	// kh_mod 0.3.0, add
	// SWITCH DATABASE, ACTION
	function switchDatabase($database) {

		switch ($database) {
			case 'flatfile':	if ($this->sqldatabase) {			// if set sql database
										if ($this->switch2Flatfile() && $this->write_sDBFlatfile()) {
											$this->status.= sprintf($this->lang['dbswitch_ok'], $this->lang['flatfile']);
											$this->sqldatabase = 0;		// use flat file
										}
										else
											$this->status.= sprintf($this->lang['dbswitch_error'], $this->lang['flatfile']);
									}
									else {
										$this->write_sDBFlatfile();
										$this->status = sprintf($this->lang['db_xyz_active'], $this->lang['flatfile']);
									}
									break;
			case 'mysql'	:	if ($this->sqldatabase != 1) {	// isn't set mysql database
										if ($this->switch2MySQL() && $this->write_sDBFlatfile('sql', 1)) {
											$this->status.= sprintf($this->lang['dbswitch_ok'], 'MySQL');
											$this->sqldatabase = 1;		// use mysql
										}
										else {
											$this->status.= sprintf($this->lang['dbswitch_error'], 'MySQL');
										}
									}
									else {
										$this->write_sDBFlatfile('sql');
										$this->status = sprintf($this->lang['db_xyz_active'], 'MySQL');
									}
									break;
			default: $this->status = $this->lang['nodbselected'];
		}
		$this->displaystatus();
	}

	// kh_mod 0.3.0, changed
	function movefiles() {
		$moveto = (int)$_POST['moveto'];
		if (!isset($this->all_folders[$moveto])) {
			$this->displaystatus($this->lang['nofolderid'] .' #'. $moveto, 3);
			return false;
		}

		$records = array();
		$n_checkboxes = (int)$_POST['selectsize'];
		for ($i=0; $i < $n_checkboxes; $i++) {
			$imageID = (int)$_POST['selectfile'. $i];
			if (empty($imageID))								continue;
			if (!isset($this->all_images[$imageID]))	continue;

			$this->all_images[$imageID][1] = $moveto;
			$records[] = $imageID;
		}
		// DISPLAY STATUS MESSAGE
		$movefolder = $this->getFolderName($moveto);
		$n_selected = count($records);
		if ($n_selected < 1) {
			$this->log('ERROR: No file selected to move!');
			$this->displaystatus($this->lang['filenotselected'], 1);
		}
		elseif ($this->write_iDB('upd', $records) > 0) {
			$this->log('Moved '.$n_selected.' file(s) to \''. $movefolder .'\'');
			$this->displaystatus(sprintf('%d %s: <a href="%s?fID=%d">%s</a>',
											$n_selected,
											$this->lang['filesmovedto'],
											ADMIN_INDEX,
											$moveto,
											$movefolder
										));
		} else {
			$this->log('ERROR: Couldn\'t move '. $n_selected .' files to \''. $movefolder. '\'!');
			$this->displaystatus($this->lang['iDB_error'], 3);
		}
	}

	//
	// DELETE DATABASE ENTRY
	// kh_mod 0.2.0, changed
	function arrayDelete(&$array, $index) {
		if (!is_array($array)) return false;

		unset($array[$index]);
		return (isset($array[$index]))? false:true;
	}

	//
	// MAKE FOLDER TREE
	// kh_mod 0.3.1, changed
	function makeFolderlist($currentID = -1) {
		settype($currentID, 'integer');
		$this->sortedfolders = array();
		foreach ($this->all_folders as $key=>$record) {
			$folderID    = $key;
			$circlelink  = false;
			$folderpath  = array();
			$folderstat  = ((int)$record[4] > time())?	1:0;	// date in future?
			$folderstat |= ((int)$record[5] < 0)?			2:0;	// folder locked?
			$folderstat |= ((int)$record[6] > 0)?			4:0;	// folder icon set?
			$folderstat |= (!empty($record[8]))?			8:0;	// password set?

			// GET PATH TO ROOT FOR EACH FOLDER
			while ($folderID > 1) {
				if ($folderID === $currentID) $folderstat |= 16;						// circle link to current folder
				$folderpath[$folderID] = trim($this->all_folders[$folderID][2]);	// folder name
				$folderID				  = (int)$this->all_folders[$folderID][1];	// parent folder id
				if (!empty($folderpath[$folderID])) break;								// circle data structure
			}
			$fullpath = implode(' : ', array_reverse($folderpath));

			// BUILD FOLDER ENTRY
			$this->sortedfolders[$key] = array($fullpath, $record[4], $folderstat);
		}
		// SORT ALL FOLDER ENTRIES
		asort($this->sortedfolders);

		//SET ROOT NAME
		if ($this->sortedfolders[1]) $this->sortedfolders[1][0] = $this->lang['root'];
	}

	//
	// GET SUBDIRECTORIES OF PICTURE DIR
	// kh_mod 0.3.0, changed
	function get_subdirs($path='', $level=0) {
		$level++;
		$subdirs  = array();
		$thispath = $this->imagefolder . $path;
		$fp = opendir($thispath);
		while ($node = readdir($fp)) {
			$subpath = $path .'/'. $node;
			$newpath = $thispath .'/'. $node;

			// EXECLUSION
			if ($node{0} === '.')	continue;
			if (!is_dir($newpath))	continue;

			$subdirs[] = array('path'=>$subpath, 'level'=>$level, 'name'=>$node, 'wrable'=>is_writeable($newpath));
			$subdirs	  = array_merge($subdirs, $this->get_subdirs($subpath, $level));
		}
		closedir($fp);
		return $subdirs;
	}

	//
	// GET FILE TREE OF PICTURE DIR
	// kh_mod 0.3.0 b4, add
	function get_tree($subdirs) {
		if (($n_subdirs = count($subdirs)) < 1) return array();

		$blr  = '&nbsp;&#9492;';	// BOX DRAWINGS LIGHT UP AND RIGHT
		$blv  = '&nbsp;&#9474;';	// BOX DRAWINGS LIGHT VERTICAL
		$blvr = '&nbsp;&#9500;';	// BOX DRAWINGS LIGHT VERTICAL AND RIGHT
		$tab  = '&nbsp;&nbsp;';

		$reverse   = array_reverse($subdirs, true);
		$explorer  = array_fill(0, $n_subdirs, '');
		$deep_base = 0;

		foreach($reverse as $key=>$dir) {
			$deep_eval = $dir['level'];
			if ($deep_base === $deep_eval) continue;

			if($deep_eval < $deep_base) $deep_base = 0; // NEXT TREE LEVEL
			if ($deep_base === 0) {
				$explorer[$key] = array_fill(0, $deep_eval+1, $tab);
				$explorer[$key][$deep_eval+1]	= $dir['name'];
			}

			// TREE BOTTOM
			if ($explorer[$key][$deep_eval] != $blvr) {
				$explorer[$key][$deep_eval] = $blr;
			}

			for($i = $key-1; $i >= 0; $i--) {
				$deep_step = $subdirs[$i]['level'];
				if ($deep_step < $deep_eval) break; 	// NEXT TREE LEVEL

				if ($deep_base === 0) {
					$explorer[$i] = array_fill(0, $deep_step+1, $tab);
					$explorer[$i][$deep_step+1] = $subdirs[$i]['name'];
				}
				$explorer[$i][$deep_eval] = ($deep_step > $deep_eval)?
														$blv		// SAME TREE LEVEL
														:
														$blvr;	// LOWER TREE LEVEL
			}
			if ($deep_base === 0) $deep_base = $deep_eval;
		}
		return $explorer;
	}

	//
	// GET CURRENT MEMORY STATUS
	// kh_mod 0.3.1, add; 0.3.3 changed
	function getMemoryStatus() {

		$this->currentMemory = array('limit'=>-1,'allocate'=>-1,'available'=>-1);
		do {
			// GET MEMORY LIMIT - RETURNS e.g. 32M or 32768K
			$global_memory = get_cfg_var('memory_limit');		// php.ini value
			$local_memory  = ini_get('memory_limit');				// current value

			// NO VALUE SET?
			if ($global_memory == '' && $local_memory == '')	break;

			// CURRENT MEMORY LIMIT
			$memory_limit = ($local_memory)?
								 $local_memory
								 :
								 $global_memory;

			// CALCULATE BYTES
			$memory_unit  = substr($memory_limit, -1);
			if (strcasecmp($memory_unit,'M') === 0)
				$limit = (int)$memory_limit * 1048576;
			elseif (strcasecmp($memory_unit,'K') === 0)
				$limit = (int)$memory_limit * 1024;
			else
				$limit = (int)$memory_limit;

			// SAVE LIMIT MEMORY
			$this->currentMemory['limit'] = $limit;

			// EXISTS FUNCTION - MEMORY USAGE
			if (!function_exists('memory_get_usage'))				break;

			// ALLOCATE MEMORY
			$allocate = memory_get_usage();

			// SET MEMORY STATUS
			$this->currentMemory['allocate']  = $allocate;
			$this->currentMemory['available'] = $limit - $allocate;
		}
		while(0);
	}

	//
	// ADMIN NAVIGATION
	// kh_mod 0.3.0, changed
	function adminnavigation($folderID){
		$path = array();
		do {
			if (!isset($this->all_folders[$folderID])) break;

			$foldername = ($folderID === 1)?
							  $this->lang['root']
							  :
							  $this->br2line($this->all_folders[$folderID][2]);
			$path[$folderID] = sprintf(' <a href="%s?fID=%d">%s</a>',
											ADMIN_INDEX,
											$folderID,
											$foldername
									 );
			$folderID = (int)$this->all_folders[$folderID][1];
			if (!empty($path[$folderID])) break;	 // circle link!
		}
		while ($folderID > 0);

		// REVERSE NAVIGATION STRING
		return implode(' : ', array_reverse($path));
	}

	//
	// ADMIN PAGE NAVIGATION
	// kh_mod 0.3.0, changed
	function adminpagenavigation($folderID,$npages,$page) {
		$dislpay = '';
		if ($npages > 1) {
			$navarr[] = ' - '. $this->lang['page'];
			$navlink  = '<a href="%s?fID=%d&amp;page=%s">%s</a>';
			for ($i=1; $i <= $npages; $i++) {
				$navarr[] = ($page === $i)?
								$i
								:
								sprintf($navlink, ADMIN_INDEX, $folderID, $i, $i);
			}
			$navarr[] = ($page === 'all')?
							$this->lang['all']
							:
							sprintf($navlink, ADMIN_INDEX, $folderID, 'all', $this->lang['all']);
			$dislpay = implode(' | ', $navarr);							
		}
		return $dislpay;
	}

	//
	// WRITE IMAGE DATABASE
	// kh_mod 0.3.0, add
	//
	// $action: 'all', 'del', 'add', 'upd', 'backup'
	//
	function write_iDB($action, $imageIDs=false) {

		// NO IMAGE ARRAY?
		if (!is_array($this->all_images))
		{
			$this->log('ERROR: \'$this->all_images\' is no array, don\'t write image database!');
			return -1;
		}

		// NO IMAGE TO ADD OR UPDATE?
		if (($action === 'add' || $action === 'upd') && count($this->all_images) < 1) {
			$this->log('ERROR: \'$this->all_images\' is empty, don\'t write image database!');
			return -1;
		}

		// SAVE IMAGE DATABASE
		return ($this->sqldatabase)?
				 $this->write2SQLTable('idatabase', $action, $imageIDs)
				 :
				 $this->write_iDBFlatfile();
	}

	//
	// WRITE IMAGE DATABASE (FLATFILE)
	// kh_mod 0.3.0, changed
	function write_iDBFlatfile($action='all') {

		$iDB_temp = DATA_FOLDER .'mg2db_idatabase_temp.php';
		$fok		 = false;
		$buffer	 = $this->autoid ."\n";
		foreach ($this->all_images as $record) {
			$buffer.= '#';
			$buffer.= implode("\t",$record);
			$buffer.= "\n";
		}
		$fp = fopen($iDB_temp, 'w');
		if (flock($fp, LOCK_EX)) {		// do an exclusive lock
			ftruncate($fp, 0);
			$fok = fwrite($fp, $buffer);
			flock($fp, LOCK_UN);			// release the lock
			fclose($fp);
			if ($fok!==false) $this->log('Writing temp image database file');
		} else {
			$this->log('ERROR: Could not lock image database file for writing');
			$this->displaystatus('Couldn\'t lock temp file (function \'write_iDBFlatfile\')', 3);
		}
		if ($fok!==false && $action!=='backup') $this->updateDBFlatfile('images');
		return ($fok)? count($this->all_images):-1;
	}

	//
	// WRITE FOLDER DATABASE
	// kh_mod 0.3.0, add
	//
	// $action: 'all', 'del', 'add', 'upd', 'backup'
	//
	function write_fDB($action, $folderIDs=false) {

		// NO FOLDER ARRAY OR EMPTY?
		if (!is_array($this->all_folders) || count($this->all_folders) < 1)
		{
			$this->log('ERROR: \'$this->all_folders\' is corrupted, don\'t write folder database!');
			return -1;
		}

		// SAVE FOLDER DATABASE
		return ($this->sqldatabase)?
				 $this->write2SQLTable('fdatabase', $action, $folderIDs)
				 :
				 $this->write_fDBFlatfile();
	}

	//
	// WRITE FOLDER DATABASE (FLATFILE)
	// kh_mod 0.3.0, changed
	function write_fDBFlatfile($action='all') {

		$fDB_temp = DATA_FOLDER .'mg2db_fdatabase_temp.php';
		$fok		 = false;
		$buffer	 = $this->folderautoid ."\n";
		foreach ($this->all_folders as $record) {
			$buffer.= '#';
			$buffer.= implode("\t",$record);
			$buffer.= "\n";
		}
		$fp = fopen($fDB_temp, 'w');
		if (flock($fp, LOCK_EX)) {		// do an exclusive lock
			ftruncate($fp, 0);
			$fok = fwrite($fp, $buffer);
			flock($fp, LOCK_UN);			// release the lock
			fclose($fp);
			if ($fok!==false) $this->log('Writing temp folder database file');
		} else {
			$this->log('ERROR: Couldn\'t lock folder database file for writing');
			$this->displaystatus('Couldn\'t lock temp file (function \'write_fDBFlatfile\')', 3);
		}
		if ($fok!==false && $action!=='backup') $this->updateDBFlatfile('folders');
		return ($fok)? count($this->all_folders):-1;
	}

	//
	// UPDATE DATABASE FROM TEMP FILES
	// kh_mod 0.2.0, changed
	function updateDBFlatfile($db='all') {

		// IMAGE DATABASE
		$iDB = DATA_FOLDER .'mg2db_idatabase.php';
		$iDB_temp = DATA_FOLDER .'mg2db_idatabase_temp.php';
		if (($db === 'all' || $db === 'images') && is_file($iDB_temp)) {
			if (!copy($iDB_temp, $iDB)) {
				$this->log('ERROR: Failed to copy temporary image database file');
				$this->displaystatus('Failed to copy temp file (function \'updateDBFlatfile\')', 3);
				exit();
			}
			else $this->log('Updated image database from temporary file');
		}

		// FOLDER DATABASE
		$fDB = DATA_FOLDER .'mg2db_fdatabase.php';
		$fDB_temp = DATA_FOLDER .'mg2db_fdatabase_temp.php';
		if (($db === 'all' || $db === 'folders') && is_file($fDB_temp)) {
			if (!copy($fDB_temp, $fDB)) {
				$this->log('ERROR: Failed to copy temporary folder database file');
				$this->displaystatus('Failed to copy temp file (function \'updateDBFlatfile\')', 3);
				exit();
			}
			else $this->log('Updated folder database from temporary file');
		}
	}

	// kh_mod 0.3.1, changed
	function write_sDBFlatfile($type='flat', $db=1) {
		// CREATE WRITE BUFFER
		if ($type === 'sql') {
			$filebuffer = "<?php\n";
			$filebuffer.= "define('DB_NAME','".		DB_NAME ."');\n";
			$filebuffer.= "define('DB_USERNAME','".DB_USERNAME ."');\n";
			$filebuffer.= "define('DB_PASSWORD','".DB_PASSWORD ."');\n";
			$filebuffer.= "define('DB_SERVER','".	DB_SERVER ."');\n";
			$filebuffer.= '$mg2->sqldatabase = '. $db .";\n";
			$filebuffer.= '?>';
		}
		else {
			$filebuffer = "<?php\n";
			$filebuffer.= '$mg2->gallerytitle = "'.	addcslashes($this->gallerytitle, "\"\\") ."\";\n";
			$filebuffer.= '$mg2->adminemail = "'.		$this->adminemail		."\";\n";
			$filebuffer.= '$mg2->metasetting = '.		$this->metasetting	.";\n";
			$filebuffer.= '$mg2->copyright = "'.		addcslashes($this->copyright, "\"\\") ."\";\n";
			$filebuffer.= '$mg2->defaultlang = "'.		$this->defaultlang	."\";\n";
			$filebuffer.= '$mg2->activeskin = "'.		$this->activeskin		."\";\n";
			$filebuffer.= '$mg2->dateformat = "'.		$this->dateformat		."\";\n";
			$filebuffer.= '$mg2->timeformat = "'.		$this->timeformat		."\";\n";
			$filebuffer.= '$mg2->navtype = '.			$this->navtype			.";\n";	
			$filebuffer.= '$mg2->showexif = '.			$this->showexif		.";\n";
			$filebuffer.= '$mg2->commentsets = '.		$this->commentsets	.";\n";
			$filebuffer.= '$mg2->foldersetting = '.	$this->foldersetting	.";\n";
			$filebuffer.= '$mg2->marknew = '.			$this->marknew			.";\n";
			$filebuffer.= '$mg2->adminpwd = "'.			$this->adminpwd		."\";\n";
			$filebuffer.= '$mg2->extensions = "'.		$this->extensions		."\";\n";
			$filebuffer.= '$mg2->introwidth = "'.		$this->introwidth		."\";\n";
			$filebuffer.= '$mg2->mediumimage = "'.		$this->mediumimage	."\";\n";
			$filebuffer.= '$mg2->indexfile = "'.		$this->indexfile		."\";\n";
			$filebuffer.= '$mg2->imagefolder = "'.		$this->imagefolder	."\";\n";
			$filebuffer.= '$mg2->layoutsetting = '.	$this->layoutsetting	.";\n";
			$filebuffer.= '$mg2->thumbquality = '.		$this->thumbquality	.";\n";
			$filebuffer.= '$mg2->thumbMaxWidth = '.	$this->thumbMaxWidth	.";\n";
			$filebuffer.= '$mg2->thumbMaxHeight = '.	$this->thumbMaxHeight.";\n";
			$filebuffer.= '$mg2->imagecols = '.			$this->imagecols		.";\n";
			$filebuffer.= '$mg2->imagerows = '.			$this->imagerows		.";\n";
			$filebuffer.= '$mg2->slideshowdelay = '.	$this->slideshowdelay.";\n";
			$filebuffer.= '$mg2->websitelink = "'.		addcslashes($this->websitelink, "\"\\") ."\";\n";
			$filebuffer.= '$mg2->websitetext = "'.		addcslashes($this->websitetext, "\"\\") ."\";\n";
			$filebuffer.= '$mg2->inactivetime = '.		$this->inactivetime	.";\n";
			$filebuffer.= '$mg2->extendedset = '.		$this->extendedset	.";\n";
			$filebuffer.= '$mg2->modversion = "'.		$this->modversion		."\";\n";
			$filebuffer.= '$mg2->installdate = "'.		$this->installdate	."\";\n";
			$filebuffer.= '?>';
		}

		// WRITE NEW SETTINGS, kh_mod 0.3.2 changed
		$sOK	 = false;
		$fname = 'mg2db_settings.php';
		$ftemp = 'mg2db_settings_temp.php';
		do {
			if (!$fp = fopen(DATA_FOLDER . $ftemp, 'wb'))	break;

			$sOK = fwrite($fp, $filebuffer);
			fflush($fp);
			fclose($fp);

			// WRITE ERROR OR CREATE ONLY A TEMP FILE FOR BACKUP
			if (!$sOK || $type === 'backup')						break;

			// 'unlink' NEEDED FOR rename() ON WINDOWS OS ONLY
			// '@' NEEDED FOR 'mg2_update.php' ONLY
			@unlink(DATA_FOLDER . $fname);

			// RENAME TEMP SETTING FILE
			$sOK = rename(DATA_FOLDER . $ftemp, DATA_FOLDER . $fname);

			// LOG MESSAGES
			$this->log('New settings saved in \''.$fname.'\'');
		}
		while(0);

		return $sOK;
	}

	//
	// DATABASE BACKUP
	// kh_mod 0.3.0, 0.4.0 b3 changed
	function backup_DB() {

		// INIT VALUES
		$path_backup = DATA_FOLDER . time() .'_mg2db_%s.php';	// raw backup path
		$messages	 = array();											// messages
		$msg_backup	 = array(
								'fdatabase'	=> array('folder',	$this->lang['folderdata'],	'folder data'),	// folder data
								'idatabase'	=> array('media',		$this->lang['imagedata'],	'image data'),		// image data
								'splashes'	=> array('media',		$this->lang['splashdata'],	'splash data'),	// splash data
								'settings'	=> array('settings',	$this->lang['setup'],		'setting data'),	// setting data
								'counter'	=> array('counter',	$this->lang['counterdata'],'counter')			// counter data
							);

		foreach($msg_backup as $key=>$item) {
			if ((int)$_POST[$item[0]] === 1) {
				$path_source = DATA_FOLDER .'mg2db_'. $key .'.php';
				if ($this->sqldatabase || is_file($path_source)) {
					$result = $this->backup_DBaction($path_backup, $key);
					if ($result === true) {
						$messages[] = '<div>Backup '. $item[1] .'  ok</div>';
						$this->log($item[1] .' backup complete');
					}
					elseif ($result === 0)
						$messages[] = sprintf('<div style="color:teal">%s There are no %s to backup</div>',
												$this->lang['notice'],
												$item[1]
										  );
					else
						$messages[] = sprintf('<div style="color:red">%s Couldn\'t backup %s!</div>',
												$this->lang['error'],
												$item[1]
										  );
				} else {
					$messages[] = sprintf('<div style="color:teal">%s '. $this->lang['nofiletobackup'] .'</div>',
											$this->lang['notice'],
											$item[2],
											'mg2db_'. $key .'.php'
									  );
				}
			}
		}

		(empty($messages))?
			$this->displaystatus($this->lang['nodataselected'] .' to backup!', 1)
			:
			$this->displaystatus(implode('', $messages));
	}

	//
	// DATABASE BACKUP ACTION
	// kh_mod 0.3.0, add; 0.4.0 b3 changed
	function backup_DBaction($path_backup, $db) {

		// WHITE LIST FOR $db AND BACKUP FUNCTIONS
		$mapping = array(	'fdatabase'	=> 'write_fDBFlatfile',
								'idatabase'	=> 'write_iDBFlatfile',
								'splashes'	=> 'backupDatabase',
								'settings'	=> 'write_sDBFlatfile',
								'counter'	=> 'backupDatabase'
					  );
		if (!isset($mapping[$db])) return false;

		// SQL DATABASE USED?
		if ($this->sqldatabase) {
			$writeBackup  = $mapping[$db];
			$tempFilePath = sprintf('%smg2db_%s_temp.php', DATA_FOLDER, $db);
			// COUNTER BACKUP
			if ($db === 'counter') {
				$Counter = new MG2Counter($this->sqldatabase);
				$path_database = ($Counter->$writeBackup() > 0)?			// return {false, 0, 1, ..n}
									  $tempFilePath
									  :
									  false;
			}
			// SPLASHES BACKUP
			elseif ($db === 'splashes') {
				$Splashes = $this->getInstance('MG2Splashes');
				$path_database = ($this->$Splashes->$writeBackup() > 0)?	// return {false, 0, 1, .. n}
									  $tempFilePath
									  :
									  false;
			}
			// ALL OTHER BACKUPS
			else {
				$path_database = ($this->$writeBackup('backup') > 0)?		// return {false, -1, 0, 1, .. n}
										$tempFilePath
										:
										false;
			}
		}
		// FLAT FILE USED!
		else {
			$path_database = (filesize(DATA_FOLDER . 'mg2db_'. $db .'.php'))?
								  DATA_FOLDER . 'mg2db_'. $db .'.php'
								  :
								  false;
		}

		// BACKUP FILE
		return ($path_database === false)?	// no data to backup?
				 0
				 :
				 copy($path_database, sprintf($path_backup, $db));
	}

	//
	// RESORE BACKUP
	// kh_mod 0.3.0, add
	function restoreBackup() {
		$use_folder	  = (isset($_POST['use_folder']))?		true:false;
		$use_media	  = (isset($_POST['use_media']))?		true:false;
		$use_settings = (isset($_POST['use_settings']))?	true:false;
		$use_counter  = (isset($_POST['use_counter']))?		true:false;

		$fTimestamp	  = substr($_POST['folder'],0,10);
		$iTimestamp	  = substr($_POST['media'],0,10);
		$sTimestamp   = substr($_POST['settings'],0,10);
		$cTimestamp   = substr($_POST['counter'],0,10);

		// RESTORE MESSAGES
		$messages = array();
		$noDataRecords	= '<span style="color:teal">'. $this->lang['notice'] .' There are no %s data to restore of %s.</span>';
		$resetedData	= '<span style="color:teal">'. $this->lang['notice'] .' %s data rseted, since there were no records!</span>';
		$couldntRead	= '<span style="color:red">'. $this->lang['error']  .' Couldn\'t read %s backup file \'%s_mg2db_%s.php\'!</span>';
		$restoredError	= '<span style="color:red">'. $this->lang['error']  .' Couldn\'t restore %s backup of %s.</span>';

		// RESTORE FOLDERS
		if ($use_folder && (int)$fTimestamp !== -1) {
			$restoreDate = $this->time2date($fTimestamp, true, true);
			list($foldersOK, $imagesOK) = $this->readDBFlatfile($fTimestamp);
			if ($foldersOK) {
				$messages[] = (($records = $this->write_fDB('all')) !== -1)?
								  sprintf('Restored %d of %d folder data records of %s.',
										$records,
										$foldersOK,
										$restoreDate
								  )
								  :
								  sprintf($restoredError, 'folder', $restoreDate);
			}
			elseif ($foldersOK === 0)
				$messages[] = sprintf($noDataRecords, 'folder', $restoreDate);
			else
				$messages[]	= sprintf($couldntRead, 'folder', $fTimestamp, 'fdatabase');
		}

		// RESTORE IMAGES
		if ($use_media && ((int)$iTimestamp !== -1)) {
			$restoreDate = $this->time2date($iTimestamp, true, true);
			if (!$use_folder || ($fTimestamp !== $iTimestamp)) {
				list($foldersOK, $imagesOK) = $this->readDBFlatfile($iTimestamp);
			}
			if ($imagesOK) {
				// WRITE IMAGE DATA
				if (($records = $this->write_iDB('all')) !== -1) {
					$messages[] = sprintf('Restored %d of %d image data records of %s.',
											$records,
											$imagesOK,
											$restoreDate
									  );

					// RESTORE SPLASHES, kh_mod 0.4.0 b3 add
					$Splashes	= $this->getInstance('MG2Splashes');						
					$splashesOK = $this->$Splashes->restoreDatabase($iTimestamp);
					if ($splashesOK === -2)
						$messages[]	= sprintf($couldntRead, 'splash', $iTimestamp, 'splashes');
					elseif ($splashesOK === -1)
						$messages[]	= sprintf($restoredError, 'splash', $restoreDate);
					elseif ($splashesOK === 0)
						$messages[]	= sprintf($resetedData, 'Splash');
					else
						$messages[] = sprintf('Restored %d of %d splash data records of %s.',
												$splashesOK,
												$this->$Splashes->getNumSplashes(),
												$restoreDate
										  );
				}
				// COULD NOT WRITE IMAGE DATA
				else {
					$messages[] = sprintf($restoredError, 'image', $restoreDate);
				}
			}
			// NO IMAGE DATA TO RESTORE
			elseif ($imagesOK===0)
				$messages[] = sprintf($noDataRecords, 'image', $restoreDate);
			else
				$messages[]	= sprintf($couldntRead, 'image', $iTimestamp, 'idatabase');
		}

		// RESTORE COUNTER
		if ($use_counter && ((int)$cTimestamp !== -1)) {
			$restoreDate = $this->time2date($cTimestamp, true, true);
			$Counter		 = new MG2Counter($this->sqldatabase, false);
			$counterOK	 = $Counter->restoreDatabase($cTimestamp);
			if ($counterOK === -3)
				$messages[] = sprintf($noDataRecords, 'counter', $restoreDate);
			elseif ($counterOK === -2)
				$messages[]	= sprintf($couldntRead, 'counter', $cTimestamp, 'counter');
			elseif ($counterOK === -1)
				$messages[]	= sprintf($restoredError, 'counter', $restoreDate);
			else
				$messages[] = sprintf('Restored %d of %d counter data records of %s.',
										$counterOK,
										$Counter->getNumCounters(),
										$restoreDate
								  );
		}

		// RESTORE SETTINGS
		if ($use_settings && ((int)$sTimestamp !== -1)) {
			if ($this->settingsOK) {
				$restoreDate= $this->time2date($sTimestamp, true, true);
				$records    = ($this->sqldatabase)?
								  $this->write2SQLTable('settings')
								  :
								  $this->write_sDBFlatfile('flat');
				$messages[] = ($records && ((int)$records !== -1))?
								  sprintf('Restored setting data of %s.', $restoreDate)
								  :
								  sprintf($restoredError, 'setting', $restoreDate);
			} else
				$messages[] = sprintf($couldntRead, 'setting', $sTimestamp, 'settings');
		}

		// $this->log('Database restore complete');
		(empty($messages))?
			$this->displaystatus($this->lang['nodataselected'] .' to restore!', 1)
			:
			$this->displaystatus(implode('<br />', $messages));
	}

	//
	// DELETE BACKUP
	// kh_mod 0.3.0, add; 0.4.0 b3 changed
	function deleteBackup() {

		$msg_delete = array(
							'fdatabase'	=> array('folder',	'folder data'),
							'idatabase'	=> array('media',		'image data'),
							'splashes'	=> array('media',		'splash data'),
							'settings'	=> array('settings',	'setting data'),
							'counter'	=> array('counter',	'counter data')
						  );

		foreach($msg_delete as $key=>$item) {
			if ((int)$_POST['use_'. $item[0]] === 1) {
				$timestamp	= substr($_POST[$item[0]],0,10);
				$backupDate	= $this->time2date($timestamp, true, true);
				$backupPath = $this->getBackupPath($timestamp);
				if (is_file(sprintf($backupPath, $key))) {
					$messages[] = unlink(sprintf($backupPath, $key))?
									  sprintf('Deleted %s file of %s.', $item[1], $backupDate)
									  :
									  sprintf('<span style="color:red">%s Couldn\'t delete %s file!</span>', $this->lang['error'], $item[1]);
				}
				elseif ((int)$timestamp !== -1)
					$messages[] = sprintf('<span style="color:teal">%s There\'s no %s file to delete!</span>',
											$this->lang['notice'],
											$item[1],
											$timestamp,
											$key
									  );
			}
		}

		// $this->log('Database delete complete');
		(empty($messages))?
			$this->displaystatus($this->lang['nodataselected'] .' to delete!', 1)
			:
			$this->displaystatus(implode('<br />', $messages));
	}

	//
	// BUILD BACKUP PATH
	// kh_mod 0.3.0, add, 0.4.0 b3 changed
	function getBackupPath($timestamp) {

		// BACKUP FILE
		$backupPath = DATA_FOLDER . $timestamp .'_mg2db_%s.php';

		// CHECK TIMESTAMP
		return (preg_match('/^[0-9]{10}$/', $timestamp))?
				 $backupPath
				 :
				 false;
	}
}	// END CLASS' MG2admin'
?>
