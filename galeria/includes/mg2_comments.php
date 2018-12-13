<?php
//
// COMMENT CLASS, IMPLEMENTATION SHOULD BE AS SINGLETON
// kh_mod 0.4.0 b3, add
class MG2Comments {

	var $mg2;				// parent object reference
	var $pathDBFlat;		// data path (flat file only)
	var $commentData;		// array with comment records
	var $commentAutoID;	// id of the latest record
	var $loadStatus;		// data and path read status (true/false)

/*
	Public Methods:

	- MG2Comments(&$parent)
	- getNumComments($itemID)
	- getComments($itemID)
	- addNewComment($itemID)
	- editComment($itemID, $commentID)
	- updateComment($itemID, $commentID)
	- askDelComment($itemID, $commentID)
	- adminCommentAction($itemID)

	'getComments' liest alle Kommentare aus '$commentData' eines
	Items und entfernt, für die Galerie-Ausgabe alle gesperrten
	Datensätze sowie,	wenn notwendig	alle eMail-Adressen.

	'addNewComment' arbeitet grundsätzlich wie gehabt, entfernt aber
	bei Misserfolg selbständig die neu eingelesenen Daten aus
	'$commentData'	und stellt sie als Rückgabewert zur Verfügung.

	'loadCommentDB' testet, ob die Daten in '$commentData' bereits
	vorhanden sind. Wenn nein, werden die Daten neu in '$commentData'
	eingelesen.
*/

	// INITIALIZE COMMENTS
	function MG2Comments(&$parent) {
		$this->mg2			= &$parent;		// reference to mg2 object
		$this->pathDBFlat = array();		// flat file path for each item
		$this->loadStatus	= array();		// load status for each item
	}

	// GET NUMBER OF COMMENTS
	function getNumComments($itemID) {

		// LOAD COMMENTS OF ITEM
		if ($this->loadCommentDB($itemID) !== true) return false;

		// COUNT UNLOCKED COMMENTS ONLY
		$countUnlocked	= 0;
		foreach ($this->commentData[$itemID] as $comment) {
			if ((int)$comment[7] === 1) $countUnlocked++;
		}

		// RETURN FOR GALLERY RESP. ADMIN
		return (!defined('USER_ADMIN'))?
				 $countUnlocked
				 :
				 array(
					$countUnlocked,
					count($this->commentData[$itemID])
				 );
	}

	// RETURNS COMMENTS FOR ITEM ID
	function getComments($itemID) {

		// INIT RETURN VALUE
		$comments = array();

		// LOAD COMMENTS OF ITEM
		if ($this->loadCommentDB($itemID) !== true) return $comments;

		// IF USER ADMIN?
		if (defined('USER_ADMIN')) {
			$comments = $this->commentData[$itemID];
		}
		else {
			foreach($this->commentData[$itemID] as $item) {
				if ((int)$item[7] !== 1) continue;	// don't output locked comments

				// DISPLAY E-MAIL IN COMMENTS?
				$comments[] = ($this->mg2->commentsets & 4)?
								  $item
								  :
								  array (
										$item[0],	// comment id
										$item[1],	// user name
										'',			// user email
										$item[3],	// comment content
										$item[4]		// time stamp (posted)
								  );
			}
		}

		// SORT COMMENTS
		$this->mg2->sort($comments, 4, $this->mg2->commentsets & 2);

		return $comments;
	}

	// ADD NEW COMMENT (GALLERY ONLY)
	function addNewComment($itemID) {

		// WRITE STATUS
		$writeOK = false;

		do {
			// NO VALID COMMENT LOAD STATUS?
			if ($this->loadCommentDB($itemID) !== true) {
				$this->mg2->status = $this->mg2->lang['commenterror'];	break;
			}

			// GET AND CLEAN NEW COMMENT INPUT
			$commentID = ++$this->commentAutoID[$itemID];
			if ($this->cleanComment($itemID, $commentID) !== true)		break;

			// WRITE NEW COMMENT
			$writeOK = ($this->writeCommentDB($itemID) > 0)? true:false;

			// DISPLAY STATUS MESSAGE
			if ($writeOK === false) {
				$this->mg2->status = $this->mg2->lang['commenterror'];	// for gallery output
			}
			else {
				$this->mg2->status = $this->mg2->lang['commentadded'];	// for gallery output
				if ($this->mg2->commentsets & 32) {								// new comments locked?
					$this->mg2->status.= sprintf('<div>%s</div>',
													$this->mg2->lang['hintcommentadded']
												);
				}
			}

			// SEND COMMENT EMAIL?
			if (($this->mg2->commentsets & 64) && !empty($this->mg2->adminemail))
				$this->sendCommentEmail($itemID, $commentID, $writeOK);
		}
		while(0);

		return $writeOK ? true:array_pop($this->commentData[$itemID]);
	}

	// ***************************************************************************** //
	// ************************* ADMIN PUBLIC METHODS ONLY ************************* //
	// ***************************************************************************** //

	// EDIT COMMENT DIALOG
	function editComment($itemID, $commentID) {

		// CHECK ITEM ID, COMMENT ID AND LOAD STATUS
		if ($this->adminCommentAccess($itemID, $commentID) !== true) return false;

		// GET TEMPLATE VALUES
		$numComments = count($this->commentData[$itemID]);
		$folderID    = $this->mg2->all_images[$itemID][1];
		$filename    = $this->mg2->all_images[$itemID][6];

		// BUILT COMMENT ARRAY FOR EDIT TEMPLATE
		$comment = array();
		$comment['date']	= $this->mg2->time2date($this->commentData[$itemID][$commentID][4], true);
		$comment['last']	= $this->mg2->time2date($this->commentData[$itemID][$commentID][5], true);
		$comment['edit']	= (int)$this->commentData[$itemID][$commentID][6];
		$comment['lock']	= (int)$this->commentData[$itemID][$commentID][7];
		$comment['name']	= $this->commentData[$itemID][$commentID][1];
		$comment['email']	= $this->commentData[$itemID][$commentID][2];
		$comment['body']	= ($this->mg2->extendedset & 4)?							// WYSIWYG HTML-Editor?
								  $this->commentData[$itemID][$commentID][3]
								  :
								  $this->mg2->br2nl($this->commentData[$itemID][$commentID][3]);

		// INCLUDE EDIT TEMPLATE
		include(ADMIN_FOLDER .'admin2_editcomment.php');

		return true;
	}

	// UPDATE ONE COMMENT ENTRY
	function updateComment($itemID, $commentID) {

		// INIT RETURN VALUE
		$updateOK = false;
		do {
			// CHECK ITEM ID, LOAD STATUS AND COMMENT ID
			if ($this->adminCommentAccess($itemID, $commentID) !== true)		break;

			// GET AND CLEAN COMMENT CHANGES
			if ($this->cleanComment($itemID, $commentID) !== true) {
				$this->mg2->displaystatus();												break;
			}

			// UPDATE CHANGE REGISTER
			$this->commentData[$itemID][$commentID][5] = time();	// last changed
			$this->commentData[$itemID][$commentID][6]++;			// count changes

			// WRITE COMMENT FILE
			$updateOK = ($this->writeCommentDB($itemID, 'upd') > 0)? true:false;

			// DISPLAY STATUS MESSAGE
			if ($updateOK) {
				$this->mg2->displaystatus(sprintf("%s '%s'",
													  $this->mg2->lang['commentupdated'],
													  $this->mg2->all_images[$itemID][6]
												  ));
			}
			// WRITE ERROR
			else {
				$this->mg2->displaystatus($this->mg2->lang['commentnotupdated'], 3);
			}
		} while(0);

		return ($updateOK)? $commentID:false;
	}

	// DELETE COMMENT DIALOG
	function askDelComment($itemID, $commentID) {

		// CHECK ITEM ID, LOAD STATUS AND COMMENT ID
		if ($this->adminCommentAccess($itemID, $commentID) !== true) return false;

		// GET COMMENT TO DELETE
		$comment				= array();
		$comment['name']	= $this->commentData[$itemID][$commentID][1];
		$comment['email']	= $this->commentData[$itemID][$commentID][2];
		$comment['body']	= $this->commentData[$itemID][$commentID][3];
		$comment['date']	= $this->mg2->time2date($this->commentData[$itemID][$commentID][4], true);
		$numComments		= count($this->commentData[$itemID]); 

		// GET ITEM ICON
		list(	$thumbID,
				$thumbFile,
				$thumbWidth,
				$thumbHeight) = $this->mg2->getFileIcon($itemID, null, true);

		// GET DIALOG VALUES
		$display		  = 'comment';
		$folderID	  = $this->mg2->all_images[$itemID][1];
		$fileName	  = $this->mg2->all_images[$itemID][6];
		$message		  = $this->mg2->lang['commentconfirm'];
		$cancel_href  = $href = ADMIN_INDEX .'?editID='. $itemID;
		$ok_href		  = $href .'&amp;action=admincomments&amp;delete=1&amp;comment0='. $commentID;
		$cancel_title = $this->mg2->lang['cancel'];
		$ok_title	  = $this->mg2->lang['ok'];

		// DISPLAY DIALOG TEMPLATE
		return (include(ADMIN_FOLDER .'admin2_delete.php'))? true:false;
	}

	// CONTROL FOR LOCK, UNLOCK AND DELETE ACTION
	function adminCommentAction($itemID) {

		// CHECK ITEM ID AND LOAD STATUS
		if ($this->adminCommentAccess($itemID) !== true) return false;

		// GET COMMENT VALUES
		$changes			= false;
		$countComments = count($this->commentData[$itemID]);
		$displComments = (isset($_POST['displayed']))?
							  (int)$_POST['displayed']
							  :
							  1;	// delete only one comment

		// CHECK NUMBER OF SELECTED COMMENTS
		if ($countComments < $displComments) $displComments = $countComments;

		// LOCK COMMENTS
		if (!empty($_POST['lock']))
			$changes = $this->lockComments($itemID, $displComments, -1);
		// UNLOCK COMMENTS
		elseif (!empty($_POST['unlock']))
			$changes = $this->lockComments($itemID, $displComments, 1);
		// DELETE COMMENTS?
		elseif (!empty($_REQUEST['delete']))
			$changes = $this->deleteComments($itemID, $displComments);

		// IF COMMENT COUNTER SET AND ANY CHANGES?
		if (($this->mg2->foldersetting & 256) && $changes) {
			list($countUnlocked, $countComments) = $this->getNumComments($itemID);

			// UPDATE COMMENT COUNTER
			$Counter = new MG2Counter($this->mg2->sqldatabase);
			$Counter->setDatabase($itemID, -1, $countUnlocked);
		}
	}

	// ***************************************************************************** //
	// ****************************** PRIVATE METHODS ****************************** //
	// ***************************************************************************** //

	// SEND NEW COMMENT BY EMAIL
	function sendCommentEmail($mediumID, $commentID, $writeOK) {
		$HOST		= $_SERVER['HTTP_HOST'];
		$URI		= $_SERVER['REQUEST_URI'];
		$comment = $this->commentData[$mediumID][$commentID];
		$to		= $this->mg2->adminemail;
		$subject	= $this->mg2->gallerytitle .": ". $this->mg2->lang['commentadded'];
		$body		= sprintf(
							"%s:\n".
							"%s (%s)\n\n".
							"%s:\n".
							"%s\n\n".
							"http://%s%s?iID=%d\n".
							"%s\n",
							$this->mg2->lang['from'],
							$comment[1],							// name
							$comment[2],							// email
							$this->mg2->lang['comment'],
							$this->mg2->br2nl($comment[3]),	// body
							$HOST,
							$URI,
							$mediumID,
							($writeOK)? "":"\nERROR: Couldn't save new comment!"
					  );
		$from	  = sprintf("From: %1\$s\nReply-to: %1\$s", $comment[2]);
		$mailOK = @mail($to, $subject, $body, $from);
		$this->mg2->log(($mailOK)? "Send comment by email":"ERROR: Couldn't send new comment by email!");
	}

	// CHECK ITEM ID, LOAD STATUS AND OPTINAL COMMENT ID
	function adminCommentAccess($itemID, $commentID=NULL) {

		$checkOK = false;
		do {
			// EXISTS ITEM ID?
			if (!isset($this->mg2->all_images[$itemID])) {
				$this->mg2->displaystatus(sprintf($this->mg2->lang['nopictureid'], $itemID), 3);		break;
			}

			// BELONG ITEM ID TO RECORD?
			if ((int)$this->mg2->all_images[$itemID][0] !== $itemID) {
				$this->mg2->displaystatus(sprintf($this->mg2->lang['nopictureid'], $itemID), 3);		break;
			}

			// NO VALID COMMENT LOAD STATUS?
			if ($this->loadCommentDB($itemID) !== true) {
				$message = $this->mg2->lang['commentnotread'] ." {$this->pathDBFlat[$itemID]}";
				$this->mg2->displaystatus($message, 3);															break;
			}

			// DO NOT EXISTS COMMENT FILE?
			if (!is_file($this->pathDBFlat[$itemID])) {
				$message = sprintf($this->mg2->lang['nocommentfile'], $this->pathDBFlat[$itemID]);
				$this->mg2->displaystatus($message, 2);															break;
			}

			// DO NOT EXISTS ANY COMMENT?
			if (count($this->commentData[$itemID]) < 1) {
				$message = sprintf($this->mg2->lang['nocommentexists'], $this->pathDBFlat[$itemID]);
				$this->mg2->displaystatus($message, 2);															break;
			}

			// CHECK COMMENT ID
			if (isset($commentID) && !isset($this->commentData[$itemID][$commentID])) {
				$this->mg2->displaystatus(sprintf('%s ID #%d<div>\'%s\'</div>',
													 $this->mg2->lang['nocommentid'],
													 $commentID,
													 $this->pathDBFlat[$itemID]
												  ), 3);																		break;
			}

			$checkOK = true;
		}
		while(0);

		return $checkOK;
	}

	// LOCK/UNLOCK COMMENTS ACTION (ADMIN ONLY)
	function lockComments($itemID, $displayed, $set) {

		// LOCK/UNLOCK SELECTED COMMENTS
		$changed = 0;
		for ($i = 0; $i < $displayed; $i++) {
			$commentID = (int)$_POST['comment'. $i];
			if (!isset($this->commentData[$itemID][$commentID])) continue;

			$this->commentData[$itemID][$commentID][7] = $set;
			$changed++;
		}

		// WRITE CHANGES
		$locked = ($changed > 0)?
					 $this->writeCommentFlatfile($itemID, 'upd')
					 :
					 false;

		// DISPLAY STATUS MESSAGE
		if ($set < 0) {
			$lockMode	 = 'locked';
			$lockMessage = sprintf($this->mg2->lang['commentslocked'], $changed);
		}
		elseif ($set > 0) {
			$lockMode	 = 'unlocked';
			$lockMessage = sprintf($this->mg2->lang['commentsunlocked'], $changed);
		}
		// NO CHANGES
		if ($changed < 1) {
			$this->mg2->displaystatus($this->mg2->lang['commentnotselected'], 1);
			$this->mg2->log(sprintf('No comment selected of %s to %s!',
									$this->pathDBFlat[$itemID],
									$lockMode
								 ));
		}
		// CHANGES WROTE
		elseif ($locked) {
			$this->mg2->displaystatus($lockMessage);
			$this->mg2->log(sprintf(' * %d Comment(s) %s',
									$changed,
									$lockMode
								 ));
		}
		// WRITE ERROR
		else {
			$this->mg2->displaystatus($this->mg2->lang['commentnotupdated'], 3);
			$this->mg2->log(' * No comments'. $lockMode);
		}
		return $locked;
	}

	// DELETE COMMENT ACTION (ADMIN ONLY)
	function deleteComments($itemID, $displayed) {

		// DELETE COMMENT ENTRIES
		$countToDelete = 0;
		$countComments = count($this->commentData[$itemID]);
		for ($i = 0; $i < $displayed; $i++) {
			$commentID = (int)$_REQUEST['comment'. $i];
			if (isset($this->commentData[$itemID][$commentID])) {
				$countToDelete++;
				unset($this->commentData[$itemID][$commentID]);
			}
		}
		// CALCULATE DELETED COMMENTS
		$countDeleted = $countComments - count($this->commentData[$itemID]);

		// WRITE COMMENT FILE OR DELETE IT
		$writeOK = false;
		if ($countDeleted > 0) {
			$this->mg2->log(sprintf('Deleted temporarily %d of %d comment(s) for image ID #%d',
									 $countDeleted,
									 $countComments,
									 $itemID
								 ));
			$writeOK = ($this->writeCommentDB($itemID, 'del') > 0)? true:false;
		}

		// DISPLAY STATUS MESSAGE
		$fileName = $this->mg2->all_images[$itemID][6];
		if ($countToDelete < 1) {
			$this->mg2->log('No comment selected to delete!');
			$this->mg2->displaystatus($this->mg2->lang['commentnotselected'], 1);
		} elseif ($writeOK) {
			if ($countDeleted < $countComments) {
				$this->mg2->displaystatus(sprintf('%d %s \'%s\'',
														$countDeleted,
														$this->mg2->lang['commentsdeleted'],
														$fileName
												  ));
			} else {
				$this->mg2->displaystatus(sprintf("%s '%s.comment'",
														$this->mg2->lang['filedeleted'],
														$fileName
												  ));
			}
		} else {
			$this->mg2->displaystatus(sprintf($this->mg2->lang['commentnotdeleted'],
													$fileName
											  ), 3);
		}

		return ($writeOK)? true:false;
	}

	// READ DATABASE
	function loadCommentDB($itemID) {

		// COMMENTS NOT YET READ?
		return (empty($this->loadStatus[$itemID]))?
				 $this->loadCommentFlatfile($itemID)
				 :
				 $this->loadStatus[$itemID];
	}

	// READ DATABASE - FLAT FILE
	function loadCommentFlatfile($itemID) {

		// INIT ITEM VALUES
		$this->pathDBFlat[$itemID]		= '';			// flat file path
		$this->commentAutoID[$itemID] = 0;			// auto id
		$this->commentData[$itemID]	= array();	// database array
		$this->loadStatus[$itemID]	   = false;		// load status

		// BUILT FLAT FILE PATH
		if ($this->builtCommentFlatfile($itemID) !== true) {
			$this->mg2->log("ERROR: Couldn't built comment file path for item #'{$itemID}'!");
			return false;
		}

		// CHECK FLAT FILE PATH
		if (is_file($this->pathDBFlat[$itemID])) {
			if (!$fp = fopen($this->pathDBFlat[$itemID], 'rb')) {
				$this->mg2->log("ERROR: Couldn't read comments from '{$this->pathDBFlat[$itemID]}'!");
				return false;
			}

			// GET COMMENT AUTO ID
			$this->commentAutoID[$itemID] = (int)fgets($fp, 16);

			// READ ALL COMMENTS OF A ITEM
			while ($fp && !feof($fp)) {
				if (fgets($fp, 2) !== '#')						continue;	// no data row?
				$record = fgetcsv($fp, 4600, "\t");
				if (($commentID = (int)$record[0]) < 1)	continue;	// invalid comment id
				$this->commentData[$itemID][$commentID] = $record;
			}
			fclose($fp);
		}

		// SET AND RETURN LOAD STATUS
		return $this->loadStatus[$itemID] = true;
	}

	// WRITE DATABASE
	function writeCommentDB($itemID, $action='add') {
		if ($this->loadStatus[$itemID] !== true) return -1;

		return $this->writeCommentFlatfile($itemID, $action);
	}

	// WRITE DATA - FLAT FILE
	function writeCommentFlatfile($itemID, $action) {

		// WRITE/DELETE STATUS
		$writeOK = $deleteOK = false;

		// CHECK COMMENT PATH AND FILE
		$commfile = $this->pathDBFlat[$itemID];
		switch ($action) {
			case 'add':	$todo = 'added to';	break;
			case 'del':	$todo = 'deleted in';	break;
			default:		$todo = 'changed in';
		}
		do {
			// CHECK IF COMMENT FILE WRITEABLE
			if (is_file($commfile) && !is_writeable($commfile)) {
				$message = sprintf("Comment couldn't be %s '%s', since the comment file is write protected",
									$todo,
									$commfile
							  );
				break;
			}
			// CHECK IF ITEM DIRECTORY WRITEABLE
			if (!is_writeable(dirname($commfile)))	{
				$message = sprintf("Comment couldn't be %s '%s', since the image folder is write protected",
									$todo,
									$commfile
							  );
				break;
			}

			// CREATE DATA CONTENT FOR COMMENT FILE
			$records = 0;
			$buffer	= $this->commentAutoID[$itemID] ."\n";
			foreach ($this->commentData[$itemID] as $record) {
				$buffer.= '#';
				$buffer.= implode("\t", $record);
				$buffer.= "\n";
				$records++;
			}

			// NO COMMENT RECORDS?
			if ($records < 1) {
				if ($action === 'del') $deleteOK = $this->deleteCommentFlatfile($commfile);
				break;
			}

			// OPEN COMMENT FILE
			if (!$fp = fopen($commfile, 'wb')) {
				$message = 'Commentfile \''.$commfile.'\' couldn\'t be opened for writing';
				break;
			}

			// LOCK COMMENT FILE
			if (!flock($fp, LOCK_EX)) {
				$message = 'Commentfile \''.$commfile.'\' couldn\'t be locked for writing';
			}
			// WRITE COMMENT FILE
			elseif ($writeOK = fwrite($fp, $buffer)) {
				$message = 'Write comment file \''.$commfile.'\'';
			}
			// COULDN'T WRITE COMMENT FILE
			else {
				$message = 'Couldn\'t write comment file \''.$commfile.'\'';
			}

			// UNLOCK AND CLOSE FILE
			flock($fp, LOCK_UN);
			fclose($fp);
		}
		while(0);

		// WRITE LOG FILE
		if ($this->mg2->commentsets & 2048) {
			$ip	= getenv('REMOTE_ADDR');
			$host = gethostbyaddr($ip);
		}
		else {
			$ip	= $host = '-';
		}
		$message.= sprintf("\n%s(%s IP: %s, HOST: %s)",
						  str_repeat(' ', 21),
						  $writeOK ? 'Filesize: '. $writeOK .' Bytes,':'From',
						  $ip,
						  $host
					  );
		$this->mg2->log($message);

		return ($writeOK xor $deleteOK)? 1:-1;
	}

	// BUILT COMMENT FLATFILE PATH
	function builtCommentFlatfile($itemID) {

		$this->pathDBFlat[$itemID] = $this->mg2->get_path (
													$this->mg2->all_images[$itemID][6],
													$this->mg2->all_images[$itemID][7],
													'comment'
											  );

		return ($this->pathDBFlat[$itemID])? true:false;
	}

	// DELETE COMMENT FLAT FILE
	function deleteCommentFlatfile($commfile) {

		$deleteOK = (is_file($commfile))?
						unlink($commfile)
						:
						false;

		// LOG ENTRY
		if ($deleteOK) {
			$this->mg2->log('Deleted comment file \''. $commfile .'\'');
		}
		else {
			$this->mg2->log('Couldn\'t delete comment file \''. $commfile .'\'');
		}

		return $deleteOK;
	}

	// CLEAN AND VERIFY FORM VALUES
	function cleanComment($mediumID, $commentID) {

		// INIT ERROR VALUES
		$cleanOK = false;
		$this->mg2->status = '';

		// CLEAN UP FORM VALUES
		$name	  = $this->mg2->charfix(substr($_POST['name'],0,90), false, true);
		$email  = $this->mg2->charfix(substr($_POST['email'],0,90), false, true);
		$body   = $this->mg2->charfix(substr($_POST['body'],0,4096));
		$body	  = (defined('USER_ADMIN'))?
					 strip_tags($body, '<b><i><u><strong><em><br><p><div><span><del><ins><h1><h2><h3><sup><sub>')
					 :
					 strip_tags($body, '<b><i><u><strong><em>');
		$body	  = $this->stripEventHandler($body);
		$body	  = preg_replace('/&(?=(.?[&;\s])|([^;]{5})|([^;]{0,5}$))/', '&amp;', $body);
		$bodybr = (defined('USER_ADMIN') && ($this->mg2->extendedset & 4))?	// admin and WYSIWYG HTML-Editor?
					 preg_replace('/\r\n|\r|\n/', '', $body)							// line break stand for data record end
					 :
					 preg_replace('/\r\n|\r|\n/', '<br />', $body);

		do {
			// IF RELOAD BY CAPTCHA ONLY
			if (!isset($_POST['submit']))														break;

			// ARE ALL FIELDS NOT EMPTY?
			if ($email === '' || $name === '' || $body === '') {
				$this->mg2->status = $this->mg2->lang['commentmissing'];				break;
			}

			// EMAIL CORRECT?
			$cClass = '[_a-z'.$this->mg2->lang['specialchars'].'0-9-]';
			$regexp = '/^'.$cClass.'+(\.'.$cClass.'+)*@'.$cClass.'+(\.'.$cClass.'+)*\.([a-z]{2,5})$/i';
			if (!@preg_match($regexp, $email)) {
				$this->mg2->status = $this->mg2->lang['emailerror']; 					break;
			}

			// CAPTCHA CORRECT?
			if (!defined('USER_ADMIN') && ($this->mg2->commentsets & 256)) {
				if (empty($_SESSION[GALLERY_ID]['token']) ||
					(sha1(trim($_POST['captcha'])) !== $_SESSION[GALLERY_ID]['token'])) {
						$this->mg2->status = $this->mg2->lang['captchaerror'];		break;
				}
			}

			// COMMENT ALLREADY EXISTS?
			foreach ($this->commentData[$mediumID] as $record) {
				if ($record[3] === $bodybr	&&			// body field
					 $record[1] === $name	&& 		// name field
					 $record[2] === $email)				// eMail field
				{
					$this->mg2->status = $this->mg2->lang['commentexists'];			break 2;
				}
			}

			// CHECK WHITELIST
			$list  = ADDON_FOLDER .'checklists/whitelist.php';
			$white = ($this->mg2->commentsets & 512)?
						$this->validCheckList($list, $name, $email, $body)
						:
						false;

			// CHECK BLACKLIST
			$list = ADDON_FOLDER .'checklists/blacklist.php';
			if (!$white)
			if ($this->mg2->commentsets & 1024)
			if ($this->validCheckList($list, $name, $email, $body)) {
				 $this->mg2->status = $this->mg2->lang['checklisterror'];			break;
			}

			// INPUT VALUES ARE CLEAN
			$cleanOK = true;

			// USE COMMENT ENTRY WITH <br />
			$body = $bodybr;
		}
		while(0);

		// REFERENCE TO COMMENTS OF THE CURRENT MEDIUM
		$currComments = &$this->commentData[$mediumID];

		// CHANGE COMMENT (ADMIN)
		if (defined('USER_ADMIN')) {
			$currComments[$commentID][0] = $commentID;	// comment id
			$currComments[$commentID][1] = $name;			// name of poster
			$currComments[$commentID][2] = $email;			// email of poster
			$currComments[$commentID][3] = $body;			// comment
		}
		// SAVE NEW COMMENT (GALLERY)
		else {
			$locked = ($this->mg2->commentsets & 32)? 0:1;
			$currComments[$commentID] = array (
													$commentID,		// comment id
													$name,			// name of poster
													$email,			// email of poster
													$body,			// comment
													time(),			// entry time
													-1,				// last changed
													0,					// count changes
													$locked			// display status
												 );
		}

		return $cleanOK;
	}

	// CLEAN COMMENT INPUT FROM EVENTHANDLER
	function stripEventHandler($subject) {

		// SPLIT STRING ON TAGS WITH EVENT HANDLER
		$tags = preg_split('/\<([^><]*on[^><]*)>?/i', $subject, -1, PREG_SPLIT_DELIM_CAPTURE);

		$regexp	= '/\s*on\w*\s*=\s*([\'"])?[^\1]*?(?(1)\1|[^>]*)/i';
		$replace	= ' ';
		$result	= '';
		foreach($tags as $key=>$tag) {
			$result.= ($key % 2)?
						 sprintf('<%s>', preg_replace($regexp, $replace, rtrim($tag)))
						 :
						 $tag;
		}
		return $result;
	}

	// CHECK WHITE AND BLACK LIST AGAINST COMMENT ENTRIES
	function validCheckList($pathCheckList, &$name, &$email, &$body) {
		do {
			if (!is_readable($pathCheckList)) {
				$this->mg2->log("ERROR: Couldn't read '{$pathCheckList}'");
				break;
			}

			if (!$checkList = file($pathCheckList)) break;

			$ip	= getenv('REMOTE_ADDR');
			$host	= gethostbyaddr($ip);
			foreach($checkList as $item) {
				$item = trim($item);
				if (empty($item))			continue;
				if ($item{0} === ';')	continue;

				$rule = explode(', ', $item);
				$search = trim($rule[0]);	if (empty($search)) continue;
				$where  = (int)$rule[1];	if (!($where & 63)) continue;

				// STRING SEARCH
				if (empty($rule[2])) {
					if ($where &  1 && (stristr($name,	$search)!==false)) return true;
					if ($where &  2 && (stristr($email, $search)!==false)) return true;
					if ($where &  4 && (stristr($body,	$search)!==false)) return true;
					if ($where &  8 && (stristr($ip,		$search)!==false)) return true;
					if ($where & 16 && (stristr($host,	$search)!==false)) return true;
				}
				// REG EXP SEARCH
				else {
					if ($where &  1 && preg_match($search, $name))	return true;
					if ($where &  2 && preg_match($search, $email))	return true;
					if ($where &  4 && preg_match($search, $body))	return true;
					if ($where &  8 && preg_match($search, $ip)) 	return true;
					if ($where & 16 && preg_match($search, $host))	return true;
				}
			}
		}
		while(0);

		return false;
	}
}
?>