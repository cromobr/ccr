<?php
//
// SPLASH IMAGES CLASS, IMPLEMENTATION SHOULD BE AS SINGLETON
// kh_mod 0.4.0 b3, add
class MG2Splashes {

	var $mg2;			// parent object reference
	var $pathDBFlat;	// data path (flat file only)
	var $splashData;	// array with splash image records
	var $loadStatus;	// data read status (true/false)

	// INITIALIZE SPLASHES
	function MG2Splashes(&$parent) {
		$this->mg2			= &$parent;
		$this->pathDBFlat = DATA_FOLDER .'mg2db_splashes.php';	
		$this->loadStatus = $this->loadSplashDB();
	}

	// GET NUMBER OF SPLASH RECORDS
	function getNumSplashes() {
		return ($this->loadStatus === true)?
				 count($this->splashData)
				 :
				 false;
	}

	function getSplashID($itemID, $type='thumb') {

		// CONVERT SPLASH TYPE
		if (($id = $this->getImageTypeID($type)) === false) return;

		// EXISTS SPLASH RECORD?
		return (isset($this->splashData[$itemID]))?
				 (int)$this->splashData[$itemID][$id]
				 :
				 0;
	}

	// GET SPLASH DATA OF FLASH ITEM
	function getSplashRecord($itemID) {

		// EXISTS SPLASH RECORD?
		return (isset($this->splashData[$itemID]))?
				 array (
					(int)$this->splashData[$itemID][0],
					(int)$this->splashData[$itemID][1],
					(int)$this->splashData[$itemID][2],
					$this->getHexColorValue($itemID, 'spColor'),
					$this->getHexColorValue($itemID, 'bgColor')
				 )
				 :
				 array();
	}

	// SET SPLASH DATA OF FLASH ITEM (ADMIN ONLY)
	function setSplashRecord($itemID) {

		// NO VALID ITEM ID
		if ((int)$itemID < 1)	return false;

		// CLEAN UP NEW INPUT
		$inputData = $this->cleanSplashes();

		// GET SPLASH RECORD
		$splashRC =	(isset($this->splashData[$itemID]))?
						$this->splashData[$itemID]
						:
						// CREATE A NEW RECORD
						array(
							 0,	// iconID
							 0,	// splashID image/folder
							 0,	// backgroundID
							-1,	// splash color (css)
							-1,	// background color (flash)
							 0		// options
					);

		// INIT COUNTER
		$changed = 0;

		// PUT NEW VALUES INTO SPLASH RECORD
		foreach ($inputData as $type=>$value) {

			// CONVERT SPLASH TYPE TO INDEX
			if (($id = $this->getImageTypeID($type)) === false) continue;

			// IF SET NOT YET SPLASH ID THEN SAVE IT
			if ($splashRC[$id] !== $value) {
				$splashRC[$id]	= $value;
				$changed++;
			}
		}

		// WRITE CHANGES INTO DATABASE
		if ($changed) {
			$this->splashData[$itemID] = $splashRC;
			$this->writeSplashDB('upd', array($itemID));
		}
	}

	// CLEAN AND SAVE SPLASHES INTO AN ARRAY
	function cleanSplashes() {

		// IMAGE IDs
		$iconID	 = (int)$_POST['image4icon'];
		$splashID = (int)$_POST['image4splash'];
		$bgID		 = (int)$_POST['image4bg'];

		// COLOR VALUES
		$regexp  = '/^\s?(#([0-9a-f]{3})|#([0-9a-f]{6})|([a-z]{3,7}))\s?$/i';
		$spColor = (preg_match($regexp, $_POST['spColor'], $match))?
					  $this->color2dec($match)
					  :
					  -1;
		$bgColor = (preg_match($regexp, $_POST['bgColor'], $match))?
					  $this->color2dec($match)
					  :
					  -1;

		return array(
					'icon'	 => $iconID,
					'splash'	 => $splashID,
					'bg'		 => $bgID,
					'spColor' => $spColor,
					'bgColor' => $bgColor
				 );
	}

	// GET INDEX OF IMAGE TYPE (THUMB, SPLASH, BG)
	function getImageTypeID($type) {

		// CODE TABLE
		$codeTable = array (
							'thumbnail'		=>	0,
							'thumb'			=>	0,
							'icon'			=>	0,
							'splash'			=>	1,
							'background'	=>	2,
							'bg'				=>	2,
							'splashcolor'	=>	3,
							'spcolor'		=>	3,
							'bgcolor'		=>	4
						 );

		// CONVERT TO LOWER CASE
		$type = strtolower($type);

		return (isset($codeTable[$type]))?
				 $codeTable[$type]
				 :
				 false;
	}

	// DELETE/UPDATE SPLASH ENTRIES (ADMIN ONLY)
	function deleteSplashes($toDeleteIDs) {
		if (!is_array($toDeleteIDs)) return false;

		do {
			if (empty($this->splashData))		break;

			// DELETE ITEM (FLASH) RECORDS
			$deletedIDs	= array();
			foreach ($toDeleteIDs as $toDeleteID) {
				if (isset($this->splashData[$toDeleteID])) {
					unset($this->splashData[$toDeleteID]);		// delete splash data record
					$deletedIDs[$toDeleteID] = $toDeleteID;	// store deleted id
				}
			}

			if (empty($this->splashData))		break;

			// DELETE SPLASH IMAGE ENTRIES
			$splashIDs	= array_flip($toDeleteIDs);
			$changedIDs	= array();
			foreach ($this->splashData as $itemID=>$record) {
				$changed = false;

				// THUMBNAILS
				if (isset($splashIDs[$record[0]])) { $this->splashData[$itemID][0] = 0; $changed = true; }
				// SPLASH IMAGES
				if (isset($splashIDs[$record[1]])) { $this->splashData[$itemID][1] = 0; $changed = true; }
				// BACKGROUND IMAGES
				if (isset($splashIDs[$record[2]])) { $this->splashData[$itemID][2] = 0; $changed = true; }

				if (!$changed) continue;

				// ARE THERE NO RECORD ENTRIES?
				if ($this->emptyRecord($itemID)) {
					unset($this->splashData[$itemID]);	// delete splash data record
					$deletedIDs[$itemID] = $itemID;		// save deleted id (used for sql)
				}
				else {
					$changedIDs[$itemID] = $itemID;		// save changed id (used for sql)
				}
			}
		}
		while(0);

		// WRITE SQL DATBASE
		if ($this->mg2->sqldatabase) {
			if (!empty($deletedIDs)) $this->writeSplashDB('del', $deletedIDs);
			if (!empty($changedIDs)) $this->writeSplashDB('upd', $changedIDs);
		}
		// WRITE FLAT FILE DATABASE
		elseif (!empty($deletedIDs) || !empty($changedIDs)) {
			$this->writeSplashDB('upd');
		}
	}

	// CHECK SPLASH RECORD
	function emptyRecord($itemID) {

		return (
					(int)$this->splashData[$itemID][0]			||	 // exists icon
					(int)$this->splashData[$itemID][1]			||	 // exists splash
					(int)$this->splashData[$itemID][2]			||	 // exists bg image
					(int)$this->splashData[$itemID][3] > -1	||	 // exists bg color (css)
					(int)$this->splashData[$itemID][4] > -1		 // exists bg color (flash)
				 )
				 ?
				 false
				 :
				 true;
	}

	// SWITCH DATABASE (ADMIN ONLY)
	function switchDatabase($db) {
		if ($this->loadStatus === false) return -1;

		return (strcasecmp($db,'sql') === 0)?
				 $this->writeSplashDBSQL('all', false)
				 :
				 $this->writeSplashDBFlatfile();
	}

	// BACKUP DATABASE (ADMIN ONLY)
	function backupDatabase() {
		if ($this->loadStatus === false)	return false;

		$this->pathDBFlat = DATA_FOLDER . 'mg2db_splashes_temp.php';
		return (($records = $this->writeSplashDBFlatfile()) < 0)?
				 false
				 :
				 $records;
	}

	// RESTORE DATABASE (ADMIN ONLY)
	function restoreDatabase($timestamp) {
		$restorePath		= DATA_FOLDER . $timestamp .'_mg2db_splashes.php';
		$this->loadStatus = (preg_match('/^[0-9]{10}$/', $timestamp))?
								  $this->loadSplashDBFlatfile($restorePath)
								  :
								  false;

		// READ STATUS OK?
		return ($this->loadStatus === false)?
				 -2										// couldn't read backup file
				 :
				 $this->writeSplashDB('all');		// -1 write error, 0 to n number of records
	}

	// READ DATABASE
	function loadSplashDB() {
		return ($this->mg2->sqldatabase)?
				 $this->loadSplashDBSQL()
				 :
				 $this->loadSplashDBFlatfile();
	}

	// READ DATABASE - SQL
	function loadSplashDBSQL() {
		$query = 'SELECT * FROM `mg2db_splashes` WHERE 1';
		if (!$result = mysql_query($query)) return false;

		while ($record = mysql_fetch_array($result, MYSQL_NUM)) {
			$this->splashData[(int)$record[0]] = array (
																(int)$record[1],
																(int)$record[2],
																(int)$record[3],
																(int)$record[4],
																(int)$record[5]
															 );
		}
		return true;
	}

	// READ DATABASE - FLAT FILE
	function loadSplashDBFlatfile($pathDB='default') {

		// SPLASH FILE PATH
		$pathDB = ($pathDB === 'default')? $this->pathDBFlat : $pathDB;

		// EXISTS SPLASH DATA
		$this->splashData = (is_file($pathDB))?
								  unserialize(file_get_contents($pathDB))
								  :
								  array();

		return is_array($this->splashData) ? true:false;
	}

	// WRITE DATABASE (ADMIN ONLY)
	function writeSplashDB($action='all', $itemIDs=false) {
		if ($this->loadStatus !== true) return -1;

		// SAVE SPLASH DATABASE
		return ($this->mg2->sqldatabase)?
				 $this->writeSplashDBSQL($action, $itemIDs)
				 :
				 $this->writeSplashDBFlatfile();
	}

	// WRITE DATABASE - SQL
	function writeSplashDBSQL($action, $itemIDs) {
		// USE DIFFERENT RECORDS AND $records IS NOT AN ARRAY?
		if ($action !== 'all' && !is_array($itemIDs)) return -1;

		$table  = 'mg2db_splashes';
		$result = -1;
		switch ($action) {
			case 'new':
			case 'upd':
				$values = array();
				foreach ($itemIDs as $id) {
					if (!array_key_exists($id, $this->splashData)) continue;	// no record?
					$record	 = $this->splashData[$id];
					$values[] = $this->getSQLSplashValues($id, $record);
				}
				$query = 'REPLACE INTO `'. $table. '` VALUES'. implode(',', $values);
				if (mysql_query($query)) $result = mysql_affected_rows();
				break;
			case 'all': if(!defined('USER_ADMIN')) break;
				$query = 'TRUNCATE TABLE `'. $table. '`'; if (!mysql_query($query)) break;
				$towrite = 0;
				$written = 0;
				foreach ($this->splashData as $itemID=>$record) {
					$towrite++;
					$query = 'INSERT INTO `'. $table. '` VALUES'. $this->getSQLSplashValues($itemID, $record);
					if (mysql_query($query)) $written++;
				}
				if ($written === $towrite) $result = $written;
				break;
			case 'del': if(!defined('USER_ADMIN')) break;
				$query = 'DELETE FROM `'. $table. '` WHERE itemID IN ('. implode(',', $itemIDs) .')';
				if (mysql_query($query)) $result = mysql_affected_rows();
				break;
			default: $this->mg2->status = 'Error in '. $table. ' sql database';
		}
		return $result;
	}

	// GET VALUES TO WRITE INTO SQL DATABASE
	function getSQLSplashValues($itemID, $record) {
		return sprintf("(%d, %d, %d, %d, %d, %d, %d)",
					 $itemID,			// item id
					 $record[0],		// thumbID
					 $record[1],		// splashID image/folder
					 $record[2],		// backgroundID
					 $record[3],		// splash color (css)
					 $record[4],		// background color (flash)
					 $record[5]			// options
				 );
	}

	// WRITE DATA - FLAT FILE
	function writeSplashDBFlatfile() {

		// INIT RETURN VALUE
		$writeOK = false;

		// NO SPLASH RECORDS
		if (count($this->splashData) < 1)
		{
			$writeOK = (is_file($this->pathDBFlat))?
						  unlink($this->pathDBFlat)	// there's nothing to write, delete splash file
						  :
						  true;								// there's nothing to write, but no file
		}
		// WRITE SPLASH FILE
		elseif (($fp = @fopen($this->pathDBFlat, 'wb')) ||
				  (rename($this->pathDBFlat, sprintf('%s%s_mg2db_splashes.php',
															DATA_FOLDER,
															time()
													  ))				&&
					($fp = fopen($this->pathDBFlat, 'wb')))
				 )
		{
			flock($fp, LOCK_EX);		// do an exclusive lock
			$writeOK = fwrite($fp, serialize($this->splashData));
			flock($fp, LOCK_UN);		// release the lock
			fclose($fp);
		}

		return ($writeOK)? count($this->splashData):-1;
	}

	function getHexColorValue($itemID, $type) {

		// GET DEC COLOR VALUE
		$decColor = (($id = $this->getImageTypeID($type)) !== false)?
						(int)$this->splashData[$itemID][$id]
						:
						-1;

		// -1 MEANS NO COLOR SET
		return (-1 < $decColor && $decColor < 16777216)?
				 sprintf('#%06X', $decColor)
				 :
				 'transparent';
	}

	// CONVERT COLOR TO DEC VALUE
	function color2dec($match) {

		// INPUT THREE DIGEST
		if ($match[2]) {
			$color = sprintf('%1$s%1$s%2$s%2$s%3$s%3$s',
							$match[2]{0},
							$match[2]{1},
							$match[2]{2}
						);
		}
		// INPUT SIX DIGEST
		elseif ($match[3]) {
			$color = $match[3];
		}
		// INPUT COLOR NAME
		elseif ($match[4]) {
			$color = $this->colorMap($match[4]);
		}
		// NO VALID COLOR
		else {
			$color = false;
		}

		// RETURN DEC COLOR VALUE
		return ($color)? hexdec($color):-1;
	}

	// CONVERT COLOR NAME TO HEX VALUE
	function colorMap($name) {

		$name  = strtolower($name);
		$color = array (
						'black'	=> '000000',
						'gray' 	=>	'808080',
						'maroon'	=>	'800000',
						'red'		=> 'FF0000',
						'green'	=> '008000',
						'lime'	=> '00FF00',
						'olive'	=> '808000',
						'yellow'	=> 'FFFF00',
						'navy'	=>	'000080',
						'blue'	=> '0000FF',
						'purple'	=> '800080',
						'fuchsia'=> 'FF00FF',
						'teal'	=>	'008080',
						'aqua'	=>	'00FFFF',
						'silver'	=>	'C0C0C0',
						'white'	=>	'FFFFFF'
					);

		// RETURN HEX COLOR VALUE
		return array_key_exists($name, $color)? $color[$name]:false;
	}
}
?>
