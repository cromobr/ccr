<?php
///////////////////////////////////////////////////////////////////////////////////
//                                                                               //
//	kh_mod                                                                        //
//	A PHP/SQL image gallery script based on MG2                                   //
//	http://www.minigal.de													                  //
//	support@minigal.de                                                            //
//                                                                               //
//	Project started: Oct/01 2006                                                  //
//                                                                               //
//    kh_mod is free software; you can redistribute it and/or modify             //
//    it under the terms of the GNU General Public License as published by       //
//    the Free Software Foundation version 2.                                    //
//                                                                               //
///////////////////////////////////////////////////////////////////////////////////

class SQLadmin extends MG2admin {

	// kh_mod 0.3.0, add, 0.4.0 b3 changed
	function switch2Flatfile() {

		// INIT WRITE STATUS
		$fWriteOK = $iWriteOK = $pWriteOK = $cWriteOK = $sWriteOK = false;

		// FLAT FILES
		$fFlatFile = 'mg2db_fdatabase.php';
		$iFlatFile = 'mg2db_idatabase.php';
		$pFlatFile = 'mg2db_splashes.php';
		$cFlatFile = 'mg2db_counter.php';
		$sFlatFile = 'mg2db_settings.php';

		// WRITE FLAT FILES
		if (($fWriteNum = $this->write_fDBFlatfile()) >= 1)					$fWriteOK = true;	// have to be one record or more
		if (($iWriteNum = $this->write_iDBFlatfile()) >= 0)					$iWriteOK = true;	// must not -1
		$Splashes = $this->getInstance('MG2Splashes');
		if (($pWriteNum = $this->$Splashes->switchDatabase('flat')) >= 0)	$pWriteOK = true;	// must not -1
		$Counter  = new MG2Counter($this->sqldatabase);
		if (($cWriteNum = $Counter->switchDatabase('flat')) >= 0)			$cWriteOK = true;	// must not -1
		if ($fWriteOK && $iWriteOK && $pWriteOK && $cWriteOK)					$sWriteOK = $this->write_sDBFlatfile();

		// CREATE FLAT FILE MESSAGES
		$this->status = '<div>';
		$this->status.= ($fWriteOK)?
							  $fWriteNum .' Folder records written to \''. $fFlatFile .'\'.'
							  :
							  'Couldn\'t write folders in directory \''. DATA_FOLDER .'\'!';
		$this->status.= '</div><div>';
		$this->status.= ($iWriteOK)?
							  $iWriteNum .' Image records written to \''. $iFlatFile .'\'.'
							  :
							  'Couldn\'t write images in directory \''. DATA_FOLDER .'\'!';
		$this->status.= '</div><div>';
		$this->status.= ($pWriteOK)?
							  $pWriteNum .' Splash records written to \''. $pFlatFile .'\'.'
							  :
							  'Couldn\'t write splashes in directory \''. DATA_FOLDER .'\'!';
		$this->status.= '</div><div>';
		$this->status.= ($cWriteOK)?
							 $cWriteNum .' Counter records written to \''. $cFlatFile .'\'.'
							 :
							 'Couldn\'t write counter in directory \''. DATA_FOLDER .'\'!';
		$this->status.= '</div><div style="margin: 0 0 12px 0">';
		$this->status.= ($sWriteOK)?
							  'Settings written to \''. $sFlatFile .'\'.'
							  :
							  'Couldn\'t write settings in directory \''. DATA_FOLDER .'\'!';
		$this->status.= '</div>';
		return $sWriteOK;
	}

	//
	// SWITCH FROM FLATFILE TO MYSQL
	// kh_mod 0.3.0, add, 0.4.0 b3 changed
	function switch2MySQL() {

		// DATABASE CONNECT
		include_once(INC_FOLDER .'mg2admin_sqlstart.php');
		if (!$database && mysql_query('CREATE DATABASE '. DB_NAME)) {
			$database = mysql_select_db(DB_NAME, $con);
		}
		if (!$database) return false;

		// INIT WRITE STATUS
		$fWriteOK = $iWriteOK = $pWriteOK = $cWriteOK = $sWriteOK = false;

		// SQL TABLES
		$fTable = 'fdatabase';
		$iTable = 'idatabase';
		$pTable = 'splashes';
		$sTable = 'settings';
		$cTable = 'counter';

		// CREATE SQL TABLES
		$fCreateOK = $this->createSQLTable($fTable);	// folder
		$iCreateOK = $this->createSQLTable($iTable);	// images
		$pCreateOK = $this->createSQLTable($pTable);	// splashes
		$cCreateOK = $this->createSQLTable($cTable);	// counter
		$sCreateOK = $this->createSQLTable($sTable);	// settings

		// WRITE DATA INTO TABLES
		if ($fCreateOK && $iCreateOK && $pCreateOK && $cCreateOK && $sCreateOK)
		do {
			if (($fWriteNum = $this->write2SQLTable($fTable)) < 1)			 break;	// have to be one record or more
			$fWriteOK = true;

			if (($iWriteNum = $this->write2SQLTable($iTable)) < 0)			 break;	// must not -1
			$iWriteOK = true;

			$Splashes = $this->getInstance('MG2Splashes');
			if (($pWriteNum = $this->$Splashes->switchDatabase('sql')) < 0) break;	// must not -1
			$pWriteOK = true;

			$Counter = new MG2Counter($this->sqldatabase);
			if (($cWriteNum = $Counter->switchDatabase('sql')) < 0)			 break;	// must not -1
			$cWriteOK = true;

			// SWITCH TO SQL
			$sWriteOK = $this->write2SQLTable($sTable);
		}
		while(0);

		// GET SQL-SERVER VERSION
		$r = mysql_query('SELECT VERSION() as `version`;');
		$mySQLVersion = 'MySQL-Version: '. (($row = mysql_fetch_object($r))? $row->version:'?');

		// DATABASE CONNECT MESSAGE WITH MYSQL VERSION
		$this->status = '<div>DB connect OK, '. $mySQLVersion .'</div>';

		// CREATE TABLE MESSAGES
		$this->status.= '<div style="margin: 12px 0 0 0">';
		$this->status.= ($fCreateOK)?
							  'MySQL folder table created.'
							  :
							  'Couldn\'t create folder table!';
		$this->status.= '</div><div>';
		$this->status.= ($iCreateOK)?
							  'MySQL image table created.'
							  :
							  'Couldn\'t create image table!';
		$this->status.= '</div><div>';
		$this->status.= ($pCreateOK)?
							  'MySQL splash table created.'
							  :
							  'Couldn\'t create splash table!';
		$this->status.= '</div><div>';
		$this->status.= ($cCreateOK)?
							  'MySQL counter table created.'
							  :
							  'Couldn\'t create counter table!';
		$this->status.= '</div><div>';
		$this->status.= ($sCreateOK)?
							  'MySQL setting table created.'
							  :
							  'Couldn\'t create setting table!';
		$this->status.= '</div>';

		// ARE CREATED ALL SQL TABLES? THEN BUILT WRITE MESSAGES!
		if ($fCreateOK && $iCreateOK && $pCreateOK && $cCreateOK && $sCreateOK) {
			$this->status.= '<div style="margin: 12px 0 0 0">';
			$this->status.= ($fWriteOK)?
								 $fWriteNum .' Folder records written into MySQL.'
								 :
								 'Couldn\'t write folders values into MySQL!';
			$this->status.= '</div><div>';
			$this->status.= ($iWriteOK)?
								 $iWriteNum .' Image records written into MySQL.'
								 :
								 'Couldn\'t write images values into MySQL!';
			$this->status.= '</div><div>';
			$this->status.= ($pWriteOK)?
								 $pWriteNum .' Splash records written into MySQL.'
								 :
								 'Couldn\'t write splash values into MySQL!';
			$this->status.= '</div><div>';
			$this->status.= ($cWriteOK)?
								 $cWriteNum .' Counter records written into MySQL.'
								 :
								 'Couldn\'t write counter values into MySQL!';
			$this->status.= '</div><div style="margin: 0 0 12px 0">';
			$this->status.= ($sWriteOK)?
								 'Settings written into MySQL.'
								 :
								 'Couldn\'t write settings into MySQL!';
			$this->status.= '</div>';
		}

		return  $sWriteOK;
	}

	//
	// CREATE A SQL TABLE
	// kh_mod 0.3.0, add
	function createSQLTable($table, $prefix='mg2db_') {
		switch ($table) {
			case 'fdatabase':
			case 'idatabase':
			case 'splashes' :
			case 'settings' :
			case 'counter'	 : break;
			default: return false;
		}

		return (mysql_query('DROP TABLE IF EXISTS `'. $prefix . $table .'`'))?
				 mysql_query($this->defineSQLTable($table, $prefix))
				 :
				 false;
	}

	//
	// DEFINE A SQL TABLE
	// kh_mod 0.3.0, add, 0.4.0 b3 changed
	function defineSQLTable($table, $prefix) {
		switch($table) {
			// FOLDER TABLE
			case 'fdatabase': return
				'CREATE TABLE `'. $prefix . $table .'` (
					`folderID` INT UNSIGNED NOT NULL,
					`parentID` VARCHAR(32) NOT NULL DEFAULT "root",
					`name` VARCHAR(255) NOT NULL DEFAULT "",
					`description` TEXT NOT NULL DEFAULT "",
					`timestamp` INT UNSIGNED NOT NULL DEFAULT 0,
					`position` INT NOT NULL DEFAULT 1,
					`thumbID` INT NOT NULL DEFAULT 0,
					`sortsetting` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
					`password` VARCHAR(255) NOT NULL DEFAULT "",
					`imagecols` VARCHAR(128) NOT NULL DEFAULT "0",
					`imagerows` VARCHAR(64)  NOT NULL DEFAULT "0",
					`foldertype` INT NOT NULL DEFAULT 0,
					PRIMARY KEY (`folderID`)
				) ENGINE = MYISAM';
			// IMAGE TABLE
			case 'idatabase': return
				'CREATE TABLE `'. $prefix . $table .'` (
					`imageID` INT UNSIGNED NOT NULL,
					`folderID` INT UNSIGNED NOT NULL DEFAULT 1,
					`title` VARCHAR(255) NOT NULL DEFAULT "",
					`description` TEXT NOT NULL DEFAULT "",
					`timestamp` INT UNSIGNED NOT NULL DEFAULT 0,
					`position` INT NOT NULL DEFAULT 1,
					`filename` VARCHAR(255) NOT NULL,
					`filepath` TEXT NOT NULL DEFAULT "",
					`imageWidth` INT UNSIGNED NOT NULL,
					`imageHeight` INT UNSIGNED NOT NULL,
					`thumbWidth` INT UNSIGNED NOT NULL,
					`thumbHeight` INT UNSIGNED NOT NULL,
					`filesize` INT UNSIGNED NOT NULL,
					`exifDate` INT UNSIGNED NULL,
					`artist` VARCHAR(255) NOT NULL DEFAULT "",
					`bookmarked` INT UNSIGNED NULL,
					`fileType` INT UNSIGNED NOT NULL DEFAULT 0,
					PRIMARY KEY (`imageID`)
				) ENGINE = MYISAM';
			// SETTINGS TABLE
			case 'settings': return
				'CREATE TABLE `'. $prefix . $table .'` (
					`settingID` INT UNSIGNED NOT NULL DEFAULT 1,
					`gallerytitle` VARCHAR(255) NOT NULL DEFAULT "My gallery",
					`adminemail` VARCHAR(255) NOT NULL,
					`metasetting` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 81,
					`defaultlang` VARCHAR(64) NOT NULL DEFAULT "english.php",
					`activeskin` VARCHAR(64) NOT NULL DEFAULT "subtel",
					`dateformat` VARCHAR(64) NOT NULL DEFAULT "%d.%m.%Y",
					`timeformat` VARCHAR(64) NOT NULL DEFAULT "%H.%M.%S",
					`navtype` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 1,
					`showexif` INT(10) UNSIGNED NOT NULL DEFAULT 510,
					`commentsets` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 159,
					`foldersetting` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 32,
					`marknew` INT(10) UNSIGNED NOT NULL DEFAULT 7,
					`copyright` VARCHAR(255) NOT NULL DEFAULT "Copyright &#169; 2009",
					`adminpwd` VARCHAR(40) NOT NULL DEFAULT "",
					`adminsalt` VARCHAR(20) NOT NULL DEFAULT "",
					`extensions` VARCHAR(128) NOT NULL DEFAULT "jpeg,jpg,gif,png",
					`introwidth` VARCHAR(32) NOT NULL DEFAULT "0",
					`mediumimage` VARCHAR(32) NOT NULL DEFAULT "700px",
					`indexfile` VARCHAR(255) NOT NULL DEFAULT "index.php",
					`imagefolder` VARCHAR(255) NOT NULL DEFAULT "pictures",
					`layoutsetting` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 1043,
					`thumbquality` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 85,
					`thumbMaxWidth` INT(10) UNSIGNED NOT NULL DEFAULT 150,
					`thumbMaxHeight` INT(10) UNSIGNED NOT NULL DEFAULT 150,
					`imagecols` INT(10) UNSIGNED NOT NULL DEFAULT 4,
					`imagerows` INT(10) UNSIGNED NOT NULL DEFAULT 6,
					`slideshowdelay` INT(10) UNSIGNED NOT NULL DEFAULT 8,
					`websitelink` TEXT NOT NULL,
					`websitetext` VARCHAR(255) NOT NULL DEFAULT "Home",
					`inactivetime` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 15,
					`extendedset` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 156,
					`modversion` VARCHAR(15) NOT NULL DEFAULT "",
					`installdate` INT(10) NOT NULL DEFAULT -1,
					PRIMARY KEY (`settingID`)
				) ENGINE=MyISAM';
			// COUNTER TABLE
			case 'counter': return
				'CREATE TABLE `'. $prefix . $table .'` (
					`imageID` INT(10) UNSIGNED NOT NULL,
					`imageClicks` INT(10) UNSIGNED NOT NULL DEFAULT 0,
					`firstClick` INT(10) UNSIGNED NOT NULL,
					`lastClick` INT(10) UNSIGNED DEFAULT NULL,
					`commentsNum` INT(10) UNSIGNED DEFAULT NULL,
					`commentsOk` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
					`ratingAvg` FLOAT DEFAULT NULL,
					`ratingNum` INT(10) UNSIGNED DEFAULT NULL,
					PRIMARY KEY (`imageID`)
				) ENGINE=MyISAM';
			// COMMENT TABLE
			case 'comments': return
				'CREATE TABLE `'. $prefix . $table .'` (
					`itemID` INT(10) UNSIGNED NOT NULL,
					`commentID` INT(10) UNSIGNED NOT NULL,
					`userName` VARCHAR(255) NOT NULL,
					`userEmail` VARCHAR(255) NOT NULL,
					`userComment` TEXT NOT NULL,
					`timestampNew` INT(10) UNSIGNED NOT NULL,
					`timestampChange` INT(10) UNSIGNED DEFAULT NULL,
					`numChanges` INT(10) UNSIGNED NOT NULL DEFAULT 0,
					`display` TINYINT(4) NOT NULL DEFAULT 1,
					PRIMARY KEY (`itemID`, `commentID`)
				) ENGINE=MyISAM';
			// SPLASHES TABLE
			case 'splashes': return
				'CREATE TABLE `'. $prefix . $table .'` (
					`itemID` INT UNSIGNED NOT NULL,
					`thumbID` INT UNSIGNED NOT NULL,
					`splashID` INT UNSIGNED NOT NULL,
					`backgroundID` INT UNSIGNED NOT NULL,
					`splashColor` INT NOT NULL DEFAULT -1,
					`bgColor` INT NOT NULL DEFAULT -1,
					`options` INT UNSIGNED NOT NULL DEFAULT 0,
					PRIMARY KEY (`itemID`)
				) ENGINE=MyISAM';
			// ITEM DESCRIPTION TABLE
			case 'idescription': return
				'CREATE TABLE `'. $prefix . $table .'` (
					`itemID` INT UNSIGNED NOT NULL,
					`description` TEXT NOT NULL DEFAULT "",
					PRIMARY KEY (`itemID`)
				) ENGINE=MyISAM';
			// ITEM - KEYWORD RELATION
			case 'ikeyword': return
				'CREATE TABLE `'. $prefix . $table .'` (
					`itemID` INT UNSIGNED NOT NULL,
					`tagID` INT UNSIGNED NOT NULL,
					PRIMARY KEY (`itemID`, `tagID`)
				) ENGINE=MyISAM';
			// KEYWORD - INDEX TABLE
			case 'keywords': return
				'CREATE TABLE `'. $prefix . $table .'` (
					`tagID` INT UNSIGNED NOT NULL,
					`tag` VARCHAR(255) NOT NULL,
					PRIMARY KEY (`tagID`)
				) ENGINE=MyISAM';
			// ITEM - SUBPATH TABLE
			case 'isubpath': return
				'CREATE TABLE `'. $prefix . $table .'` (
					`subpathID` INT UNSIGNED NOT NULL,
					`filepath` TEXT NOT NULL DEFAULT "",
					PRIMARY KEY (`subpathID`)
				) ENGINE=MyISAM';
		}
		return false;
	}

	//
	// WRITE MySQL DATABASE
	// kh_mod 0.3.0, add, 0.4.0 b3 changed
	function write2SQLTable($table, $action='all', $recordIDs=false) {
		// USE DIFFERENT RECORDS AND $records IS NOT AN ARRAY?
		if ($action !== 'all' && !is_array($recordIDs)) return -1;

		$result = -1;
		switch ($table) {
			case 'fdatabase':
				switch ($action) {
					case 'new':
					case 'upd':
						$values = array();
						foreach ($recordIDs as $folderID) {
							if (!array_key_exists($folderID, $this->all_folders)) continue;
							$record	 = $this->all_folders[$folderID];
							$values[] = $this->getSQLValues($table, $record);
						}
						$query = 'REPLACE INTO `mg2db_'. $table .'` VALUES'. implode(',', $values);
						if (mysql_query($query)) $result = mysql_affected_rows();
						break;
					case 'all':
						$query	= 'TRUNCATE TABLE `mg2db_'. $table .'`'; if (!mysql_query($query)) break;
						$towrite	= 0;
						$writeOk	= 0;
						foreach ($this->all_folders as $record) {
							$towrite++;
							$query = 'INSERT INTO `mg2db_'. $table .'` VALUES'. $this->getSQLValues($table, $record);
							if (mysql_query($query)) $writeOk++;
						}
						if ($writeOk === $towrite) $result = $writeOk;
						break;
					case 'del':
						$query = 'DELETE FROM `mg2db_'. $table .'` WHERE folderID IN ('.
									implode(',', $recordIDs)
									.')';
						if (mysql_query($query)) $result = mysql_affected_rows();
						break;
					default: $this->status = 'Could not write into table `mg2db_'. $table .'`';
				}
				break;
			case 'idatabase':
				switch ($action) {
					case 'new':
					case 'upd':
						$values = array();
						foreach ($recordIDs as $imageID) {
							if (!array_key_exists($imageID, $this->all_images)) continue;
							$record	 = $this->all_images[$imageID];
							$values[] = $this->getSQLValues($table, $record);
						}
						$query = 'REPLACE INTO `mg2db_'. $table .'` VALUES'. implode(',',$values);
						if (mysql_query($query)) $result = mysql_affected_rows();
						break;
					case 'all':
						$query	= 'TRUNCATE TABLE `mg2db_'. $table .'`'; if (!mysql_query($query)) break;
						$towrite	= 0;
						$writeOk	= 0;
						foreach ($this->all_images as $record) {
							$towrite++;
							$query = 'INSERT INTO `mg2db_'. $table .'` VALUES'. $this->getSQLValues($table, $record);
							if (mysql_query($query)) $writeOk++;							
						}
						if ($writeOk === $towrite) $result = $writeOk;
						break;
					case 'del':
						$query = 'DELETE FROM `mg2db_'. $table .'` WHERE imageID IN ('.
									implode(',', $recordIDs)
									.')';
						if (mysql_query($query)) $result = mysql_affected_rows();
						break;
					default: $this->status = 'Could not write into table `mg2db_'. $table .'`';
				}
				break;
			case 'settings':
				$query  = 'REPLACE INTO `mg2db_'. $table .'` VALUES'. $this->getSQLValues($table);
				$result = (mysql_query($query))? true:false;
				break;
			default: $this->status = $this->lang['nodbselected'];
		}
		return $result;
	}

	//
	// GET VALUES FOR WRITE FOLDER DATABASE
	// kh_mod 0.4.0 b3, add
	function getSQLValues($table, $record='') {

		$result = '';
		switch ($table) {
			// FOLDER DATA
			case 'fdatabase':
				$fType = (isset($record[11]))? (int)$record[11]:0;
				return  sprintf("(
								%d ,'%s','%s','%s', %d , %d, %d, %d ,'%s','%s','%s', %d
								)",
								$record[0],			// folder id
								trim($record[1]),	// 'root' or parent folder id
								mysql_real_escape_string(trim($record[2])),
								mysql_real_escape_string(trim($record[3])),
								$record[4],
								$record[5],
								$record[6],
								$record[7],
								mysql_real_escape_string(trim($record[8])),
								($fType === 1)? mysql_real_escape_string(trim($record[9])) :(int)$record[9],
								($fType === 1)? mysql_real_escape_string(trim($record[10])):(int)$record[10],
								$fType
						  );
			// MEDIA DATA
			case 'idatabase':
				return  sprintf("(
								%d ,'%d','%s','%s', %d , %d ,'%s','%s', %d,
								%d , %d , %d , %d , %s ,'%s', %d , %d
								)",
								$record[0],		// image id
								$record[1],		// parent folder id
								mysql_real_escape_string(trim($record[2])),
								mysql_real_escape_string(trim($record[3])),
								$record[4],
								$record[5],
								mysql_real_escape_string(trim($record[6])),
								mysql_real_escape_string(trim($record[7])),
								$record[8],
								$record[9],
								$record[10],
								$record[11],
								$record[12],
								(($record[13])? (int)$record[13]:'NULL'),
								mysql_real_escape_string(trim($record[14])),
								$record[15],
								$record[16]
						  );
			// SETTING DATA
			case 'settings':
				return  sprintf("(1,
								'%s','%s', %d ,'%s','%s','%s','%s', %d , %d , %d , %d ,
								 %d ,'%s','%s','%s','%s','%s','%s','%s','%s', %d , %d ,
								 %d , %d , %d , %d , %d ,'%s','%s', %d , %d ,'%s', %d
								)",
								mysql_real_escape_string(trim($this->gallerytitle)),
								mysql_real_escape_string(trim($this->adminemail)),
								$this->metasetting,
								trim($this->defaultlang),
								trim($this->activeskin),
								trim($this->dateformat),
								trim($this->timeformat),
								$this->navtype,
								$this->showexif,
								$this->commentsets,
								$this->foldersetting,
								$this->marknew,
								mysql_real_escape_string(trim($this->copyright)),
								mysql_real_escape_string(trim($this->adminpwd)),
								mysql_real_escape_string(trim($this->adminsalt)),
								mysql_real_escape_string(trim($this->extensions)),
								$this->introwidth,
								$this->mediumimage,
								mysql_real_escape_string(trim($this->indexfile)),
								mysql_real_escape_string(trim($this->imagefolder)),
								$this->layoutsetting,
								$this->thumbquality,
								$this->thumbMaxWidth,
								$this->thumbMaxHeight,
								$this->imagecols,
								$this->imagerows,
								$this->slideshowdelay,
								mysql_real_escape_string(trim($this->websitelink)),
								mysql_real_escape_string(trim($this->websitetext)),
								$this->inactivetime,
								$this->extendedset,
								mysql_real_escape_string(trim($this->modversion)),
								$this->installdate
						  );
			default: $this->log('ERROR: Could not create values for sql table \''.$table.'\'.');
		}
		return $result;
	}
}
?>
