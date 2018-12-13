<?php

// ITEM DATABASE
// FOR MG2 0.5.1 AND kh_mod < 0.2.0 b2
function convert_iDB() {

	// GET START TIME
	$start = microtime_float();
	global $mg2;

	$writeOK	= false;
	$maxID	= 0;
	$iDB		= 'mg2db_idatabase.php';

	if (count($mg2->all_images) > 0) {
		$mg2->displaystatus(($mg2->sqldatabase)?
					'Item data in sql table \'mg2db_idatabase\' alredy exists!'
					:
					'Item data in \'data/'. $iDB .'\' alredy exists!', 2
				);
		return false;
	}

	do {
		$mg2->all_images = array();
		if (!is_file($iDB)) {
			$message = 'Found no item database to import from the gallery root!';
			break;
		}
		if (!$fp = @fopen($iDB,'rb'))	break;		// cannot open data file
		if (!is_resource($fp))			break;		// $fp is no resource

		$mg2->autoid = trim(fgets($fp, 16));
		while (!feof($fp)) {
			if (fgets($fp, 2) !== '*')					continue; // no data row?
			$record = fgetcsv($fp, 4600, '*');
			if (($itemID = (int)$record[0]) < 1)	continue; // invalid item id
			$mg2->all_images[$itemID] = $record;
		}
		fclose($fp);

		// @include(INC_FOLDER .'mg2_exif.php');
		foreach ($mg2->all_images as $key=>$record) {
			if (empty($record[0]))		continue;	// no record id found

			if ($maxID < (int)$key) $maxID = (int)$key;
			$mg2->all_images[$key][1]  = (trim($record[2])==='root')? 1:(int)$record[2];
			$mg2->all_images[$key][2]  = str_replace("\t",'   ',trim($record[3]));
			$mg2->all_images[$key][3]  = str_replace("\t",'   ',trim($record[4]));
			$mg2->all_images[$key][4]  = (int)$record[10];
			$mg2->all_images[$key][5]  = (isset($record[11]))? (int)$record[11]:1;
			$mg2->all_images[$key][6]  = trim($record[1]);
			$mg2->all_images[$key][7]  = (isset($record[12]))? trim($record[12]):'';
			$mg2->all_images[$key][8]	= (int)$record[6];
			$mg2->all_images[$key][9]  = (int)$record[7];
			$mg2->all_images[$key][10] = (int)$record[8];
			$mg2->all_images[$key][11] = (int)$record[9];
			$mg2->all_images[$key][12] = (int)$record[5];
			$mg2->all_images[$key][13] = 0;
			$mg2->all_images[$key][14] = '';
			$mg2->all_images[$key][15] = 0;
			$mg2->all_images[$key][16] = $mg2->getContentCode(substr(strrchr($mg2->all_images[$key][6], '.'), 1));

/*
			// GET EXIF VALUES
			$imagefile = $mg2->get_path($mg2->all_images[$key][6], $mg2->all_images[$key][7]);
			exif($imagefile);
			$d = preg_split('/(:|\s)/', $exifData['DTOpticalCapture'],6,PREG_SPLIT_NO_EMPTY);
			$exifData['DTOpticalCapture'] = '';
			$mg2->all_images[$key][13] = mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0]);
*/
		}
		// CHECK ITEM AUTO ID
		if ($maxID > $mg2->autoid) $mg2->autoid = $maxID;

		// WRITE ITEM DATABASE
		$writeOK = $mg2->write_iDB('all');

		// BUILD STATUS MESSAGE
		$takes   = round(microtime_float() - $start,3);
		$records = count($mg2->all_images);
		$message = 'Item database imports '. $records .' records';
		$message.= ($writeOK)? ', it took '. $takes .' sek.':', Error!';
	}
	while(0);

	// DISPLAY STATUS MESSAGE
	$mg2->displaystatus($message);
}

// SET ALL CONTENT TYPES IN ITEM DATABASE
// FOR kh_mod < 0.4.0 b3 ONLY
function setContentType() {

	// GET START TIME
	$start = microtime_float();
	global $mg2;

	$writeOK = false;

	// READ DATABASE
	list($readFolders, $readItems) = $mg2->readDB();

	// ARE THERE ITEM RECORDS?
	if ($readItems > 0) {

		// SET CONTENT TYPE
		foreach ($mg2->all_images as $key=>$record) {
			$mg2->all_images[$key][16] &= ~2047;
			$mg2->all_images[$key][16] |= $mg2->getContentCode(substr(strrchr($mg2->all_images[$key][6], '.'), 1));
		}

		// WRITE ITEM DATABASE
		$writeOK = $mg2->write_iDB('all');
	}

	// BUILD STATUS MESSAGE
	$takes   = round(microtime_float() - $start,3);
	$message = 'Content type set in each item database record';
	$message.= ($writeOK)? ', it took '. $takes .' sek.':', Error!';

	// DISPLAY STATUS MESSAGE
	$mg2->displaystatus($message);
}

// FOLDER DATABASE
// FOR MG2 0.5.1 AND kh_mod < 0.2.0 b2
function convert_fDB() {
	$start = microtime_float();
	global $mg2;

	$db_ok = false;
	$maxID = 0;
	$fDB   = 'mg2db_fdatabase.php';

	if (count($mg2->all_folders) > 1) {
		$mg2->displaystatus(($mg2->sqldatabase)?
					'Folder data in sql table \'mg2db_fdatabase\' alredy exists!'
					:
					'Folder data in \'data/'.$fDB.'\' alredy exists!', 2
				);
		return false;
	}

	do {
		$mg2->all_folders = array();
		if (!is_file($fDB)) {
			$message = 'Found no folder database to import from the gallery root!';
			break;
		}
		if (!$fp = @fopen($fDB,'rb'))	break;		// cannot open data file
		if (!is_resource($fp))			break;		// $fp is no resource

		$mg2->folderautoid = trim(fgets($fp,16));
		while (!feof($fp)) {
			if (fgets($fp,2) !== '*')	continue;	// no data row?
			$record = fgetcsv($fp,4600,'*');
			$mg2->all_folders[$record[0]] = $record;
		}
		fclose($fp);

		foreach ($mg2->all_folders as $key=>$record) {
			if (empty($record[0]))		continue;	// no record id found

			if ($maxID < (int)$key) $maxID = (int)$key;
			$mg2->all_folders[$key][1]	 = (trim($record[1])==='root')? 'root':(int)$record[1];
			$mg2->all_folders[$key][2]	 = str_replace("\t",'   ',trim($record[2]));
			$mg2->all_folders[$key][3]	 = str_replace("\t",'   ',trim($record[10]));
			$mg2->all_folders[$key][4]  = (int)$record[6];
			$mg2->all_folders[$key][5]  = (isset($record[11]))? (int)$record[11]:1;
			$mg2->all_folders[$key][6]  = (isset($record[7]))?  _getimage($record[7]):-1;
			$mg2->all_folders[$key][7]	 = _getsortby($record[3]);	// sort by
			$mg2->all_folders[$key][7]	|= (int)$record[4] << 4;	// sort mode
			$mg2->all_folders[$key][8]  = $record[5];
			$mg2->all_folders[$key][9]  = '';
			$mg2->all_folders[$key][10] = '';
			if (isset($mg2->all_folders[$key][11])) unset($mg2->all_folders[$key][11]);
		}
		if ($maxID > $mg2->folderautoid) $mg2->folderautoid = $maxID;
		$db_ok = $mg2->write_fDB('all');

		// BUILD STATUS MESSAGE
		$takes   = round(microtime_float() - $start,3);
		$records = count($mg2->all_folders);
		$message = 'Folder database imports '. $records .' records';
		$message.= ($db_ok)? ', it took '. $takes .' sek.':', Error!';
	}
	while(0);

	// DISPLAY STATUS MESSAGE
	$mg2->displaystatus($message);
}

// COMMENT FILES
function convert_cDB() {
	global $mg2;

	$read		  = 0;
	$converted = 0;
	$saved	  = 0;
	$start	  = microtime_float();
	if ($handle = opendir($mg2->imagefolder)) {
		while (false !== ($file = readdir($handle))) {
			if (substr($file, -8) !== '.comment')			 continue;
			$read++;
			$commfile = $mg2->imagefolder .'/'. $file;
			if (!$comments = loadOldComments($commfile)) continue;

			// CONVERT RECORDS
			$commentID		= 0;
			$mg2->comments	= array();
			foreach ($comments as $record) {
				// NEW COMNMENTS
				if (isset($record['new'])) {
					$record['new'][0] = ++$commentID;
					$mg2->comments[$commentID] = $record['new'];
				}
				// OLD COMMENTS
				elseif (isset($record['old'])) {
					$mg2->comments[$commentID][0] = ++$commentID;
					$mg2->comments[$commentID][1] = str_replace("\t",'   ',trim($record['old'][1]));
					$mg2->comments[$commentID][2] = trim($record['old'][2]);
					$mg2->comments[$commentID][3] = str_replace("\t",'   ',trim($record['old'][3]));
					$mg2->comments[$commentID][4] = $record['old'][0];
					$mg2->comments[$commentID][5] = -1;
					$mg2->comments[$commentID][6] = 0;
					$mg2->comments[$commentID][7] = 1;
				}
			}
			$converted++;

			// WRITE COMMENT DATABASE
			if ($commentID) {
				$mg2->commentAutoID = $commentID;
				if (writeComments($commfile)) $saved++;
			}
		}
		closedir($handle);
	}
	$takes = round(microtime_float() - $start,3);

	// GET AND DISPLAY MESSAGES
	if ($read == 0) {
		$message = 'Found no comment files to convert in \''.$mg2->imagefolder.'\' directory!';
		$errtype = 1; // notice
	}
	elseif ($converted == 0) {
		$message = 'Read '. $read .' files, but no old comments to convert!';
		$errtype = 1; // notice
	}
	else {
		$message = 'Comment database converted '. $converted .' of '. $read;
		$message.= ' files, ';
		if ($converted > $saved) {
			$message.= ($converted - $saved) .' write error(s)!';
			$errtype = 3; // error
		} else {
			$message.= 'it took '. $takes .' sek.';
			$errtype = 0; // ok
		}
	}
	$mg2->displaystatus($message, $errtype);
}

//
// CLICK-COUNTER PLUG-IN DATABASE
function convert_clickDB() {

	$start = microtime_float();
	global $mg2;

	$Counter = new MG2Counter($mg2->sqldatabase);
	if (count($Counter->data) > 0) {
		$mg2->displaystatus(($mg2->sqldatabase)?
					'Counter data in sql table \'mg2db_counter\' alredy exists!'
					:
					'Counter data in \'data/mg2db_counter.php\' alredy exists!', 2
				);
		return false;
	}

	// CONVERT COUNTER DATA
	$plugin_data = readPluginCounter();

	do {
		// COULD NOT READ PLUGIN DATABASE FILE
		if (!is_array($plugin_data)) {
			$message = ($plugin_data === -1)?
						  'Couldn\'t read click counter plug-in file \'database.txt\'!'
						  :
						  'Couldn\'t import counter plug-in, because file \'database.txt\' doesn\'t exist!';
			$errtype = 3;	// error
			break;
		}

		// CONVERT PLUGIN DATA
		foreach ($plugin_data as $key=>$item) {
			if ($key < 1) { unset($plugin_data[$key]); continue; }	// no valid image index
			$plugin_data[$key] = array($key, $item, 0, 0);				// new counter record
		}

		// NO VALID DATA COUNTER DATA
		if (count($plugin_data) < 1) {
			$message = 'There are no valid counter plug-in data to import!';
			$errtype = 1; // notice
			break;
		}

		// IMPORT PLUGIN DATA
		if ($num = $Counter->importDatabase($plugin_data)) {
			$message = $num.' of '. count($plugin_data) .' click counter '.
						  'plug-in entries imported, it took '.
						  round(microtime_float() - $start,3) .' sek.';
			$errtype = 0;	// ok
			break;
		}

		// UNDEFINED ERROR
		$message = 'Couldn\'t import click counter plug-in database!';
		$errtype = 3;	// error
	}
	while(0);

	$mg2->displaystatus($message, $errtype);
}

//
// GET SORT BY (FOLDER DATABASE)
function _getsortby($idx) {
	switch ((int)$idx) {
		case  1:	$idx =  6; break;
		case  3:	$idx =  2; break;
		case  4:	$idx =  3; break;
		case  5:	$idx = 12; break;
		case  6:	$idx =  8; break;
		case  7:	$idx =  9; break;
		case 10:	$idx =  4; break;
		case 11:	$idx =  5; break;
		default: $idx =  6;
	}
	return $idx;
}

//
// CONVERT THUMB NAME TO IMAGE ID
function _getimage($thumb) {
	global $mg2;

	$ext	= strrchr(trim($thumb), '.');
	$name = basename($thumb, $ext);
	if (substr($name, -6) === '_thumb') {
		$filename = substr($name, 0, -6) . $ext;
		$imageRC  = $mg2->select($filename, $mg2->all_images, 6);
	}
	return ((int)$imageRC[0][0])? (int)$imageRC[0][0]:-1;	// -1. random image as icon
}

//
// GET CLICK-COUNTER PLUG-IN DATABASE
function readPluginCounter() {

	// INIT VALUES
	$data = array();
	$file = 'database.txt';

	// VERIFY PLUG-IN COUNTER FILE
	if (!is_readable($file)) return (is_file($file))? -1:-2;

	// READ PLUG-IN COUNTER FILE
	$fp   = @fopen('database.txt', 'r');
	while ($fp && !feof($fp)) {
		$line = fgets($fp, 128);
		if (empty($line))	continue;	// no data row?
		$item = split('\|', $line);
		$data[(int)$item[0]] = (int)$item[1];
	}
	fclose($fp);
	return $data;
}

//
// GET OLD COMMENT ENTRIES
function loadOldComments($commfile) {
	$comments = array();
	$old = $new = 0;
	if (is_readable($commfile)) {
		$fp = @fopen($commfile, 'r');
		while ($fp && !feof($fp)) {
			$linemark = fgets($fp, 2);
			if ($linemark === '#') {
				$record = fgetcsv($fp, 4600, "\t");
				if ((int)$record[0] < 1)	continue;	// invalid comment id
				if (count($record)  < 5)	continue;	// invalid new comment entry

				$comments[] = array('new'=>$record); $new++;
			}
			elseif ($linemark === '*') {
				$record = fgetcsv($fp, 4600, "*");
				if ((int)$record[0] < 1)	continue;	// invalid comment date
				if (count($record)  < 4) 	continue;	// invalid old comment entry

				$comments[] = array('old'=>$record); $old++;
			}
		}
		fclose($fp);
	}
	elseif (is_file($commfile)) {
		return false;
	}
	return ($old > 0)? $comments:false;
}

	//
	//	WRITE CONVERTED COMMENTS
	function writeComments($commfile) {
		global $mg2;

		// WRITE STATUS
		$writeOK = false;

		// CHECK COMMENT PATH AND FILE
		do {
			if (is_file($commfile) && !is_writeable($commfile)) {
				$message = 'Comment couldn\'t be wrote to \''.$commfile.'\', since the comment file is write protected';
				break;
			}
			if (!is_writeable($mg2->imagefolder))	{
				$message = 'Comment couldn\'t be wrote to \''.$commfile.'\', since the image folder is write protected';
				break;
			}
			if (!is_array($mg2->comments)) {
				$message = 'Comment couldn\'t be wrote to \''.$commfile.'\', since no entries were found.';
				break;
			}
			if (!$fp = fopen($commfile,'w')) {
				$message = 'Comment file \''.$commfile.'\' couldn\'t be opened for writing';
				break;
			}

			// CREATE DATA CONTENT FOR COMMENT FILE
			$buffer = $mg2->commentAutoID ."\n";
			foreach ($mg2->comments as $record) {
				$buffer.= '#';
				$buffer.= implode("\t",$record);
				$buffer.= "\n";
			}

			if (!flock($fp, LOCK_EX)) {
				$message = 'Commentfile \''.$commfile.'\' couldn\'t be locked for writing';
			}
			elseif ($writeOK = fwrite($fp, $buffer)) {
				$message = 'Write comment file \''.$commfile.'\'';
			}
			else {
				$message = 'Couldn\'t write comment file \''.$commfile.'\'';
			}

			flock($fp, LOCK_UN);	// unlock file
			fclose($fp);			// close file
		}
		while(0);

		// WRITE LOG FILE
		if ($mg2->commentsets & 2048) {
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
		$mg2->log($message);

		return $writeOK;
	}

//
// GET MILLISEK AS FLOAT
function microtime_float() {
	list($usec, $sec) = explode(' ', microtime());
	return ((float)$usec + (float)$sec);
}
?>