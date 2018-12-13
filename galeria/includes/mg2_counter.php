<?php
//
// COUNTER CLASS, IMPLEMENTATION SHOULD BE AS SINGLETON
// kh_mod 0.1.0 rc1, add, 0.3.0 changed
class MG2Counter {

	var $path;	// database path (flat file only)
	var $type;	// database type (flat file or sql)
	var $data;	// database data array
	var $chgd;	// changed data records
	var $load;	// database read status (true/false)

	// INITIALIZE COUNTER
	function MG2Counter($sqlDB=0, $loadDB=true) {
		$this->setDatabaseType($sqlDB);
		$this->path = DATA_FOLDER .'mg2db_counter.php';
		$this->load = ($loadDB)?
						  $this->loadDatabase()
						  :
						  false;
	}

	// GET NUMBER OF CONTER RECORDS
	function getNumCounters() {
		return ($this->load === true)?
				 count($this->data)
				 :
				 false;
	}

	// SET DATABASE TYPE (SQL/FLAT)
	function setDatabaseType($sqlDB) {
		$this->type = ($sqlDB)? 'sql':'flat';
	}

	// GET NUMBER OF CLICKS
	function getNumClicks($imageID) {
		return (isset($this->data[$imageID][1]))?
				 $this->data[$imageID][1]
				 :
				 '0';
	}

	// GET NUMBER OF COMMENTS
	function getNumComments($imageID) {
		return (isset($this->data[$imageID][5]) && (int)$this->data[$imageID][5] === 1)?
				 (int)$this->data[$imageID][4]
				 :
				 -1;	// number of comments unknown
	}

	// SET CLICK AND COMMENT COUNTER
	function setDatabase($imageID, $clicks=-1, $comments=-1, $write=true) {
		settype($imageID, 'integer');	if ($imageID < 1) return;

		// EXISTS COUNTER ENTRY NOT YET
		if (!isset($this->data[$imageID])) {
			$this->data[$imageID] = array (
												$imageID,	// image id
												0,				// num mouse clicks
												0,				// first click
												0				// last click
											);
		}
		else $this->data[$imageID][0] = $imageID;

		// SET CLICK COUNTER, BUT COUNTER LOCKED, E.G. IN ADMIN MODE
		if ($clicks === 1 && empty($_SESSION[GALLERY_ID]['adminmode'])) {
			$this->data[$imageID][1]++;
			($this->data[$imageID][1] === 1)?		// first entry?
				$this->data[$imageID][2] = time()
				:
				$this->data[$imageID][3] = time();
		}

		// SET COMMENT COUNTER
		if ($comments >= 0) {
			$this->data[$imageID][4] = $comments;	// comments
			$this->data[$imageID][5] = 1;				// comment count ok
		}

		// SAVE CHANGES
		($write === true)?
			$this->writeDatabase('upd', array($imageID))
			:
			$this->chgd[] = $imageID;
	}

	// UPDATE DATABASE CHANGES
	function updateRecords() {
		$result = $this->writeDatabase('upd', $this->chgd);
		if ((int)$result > 0) $this->chgd = array();
	}

	// DELETE IMAGE ENTRIES (ADMIN ONLY)
	function deleteRecords($imageIDs) {
		if (!is_array($imageIDs)) return false;

		// INIT ARRAY
		$deletedIDs = array();

		// EXISTS COUNTER ENTRIES
		if (!empty($this->data)) {
			foreach($imageIDs as $ID) {
				if (empty($this->data[$ID]))	continue;	// no entry

				unset($this->data[$ID]);						// delete counter array item
				$deletedIDs[] = $ID;								// save deleted id (used for sql)
			}
			$this->writeDatabase('del', $deletedIDs);
		}
	}

	// RESET COMMENT COUNTER (ADMIN ONLY)
	function resetComments($action='upd') {
		$changes = array();
		foreach($this->data as $key=>$line) {
			// NO ENTRY OR ALREADY SET INVALID TO 0
			if (empty($line[5]))	continue;

			$this->data[$key][5] = 0; // set counter invalid
			$changes[] = $key;
		}
		return ($action==='upd' && empty($changes))?
				 true
				 :
				 $this->writeDatabase($action, $changes);
	}

	// SWITCH DATABASE (ADMIN ONLY)
	function switchDatabase($db) {
		$db = (strtolower($db) === 'sql')? 'sql':'flat';
		if ($this->type === $db) return -1;

		$this->type = $db;
		return $this->writeDatabase();
	}

	// BACKUP DATABASE (ADMIN ONLY)
	function backupDatabase() {
		if ($this->load === false) return false;

		$this->path = DATA_FOLDER . 'mg2db_counter_temp.php';
		return (($records = $this->writeDatabaseFlatfile()) < 0)?
				 false
				 :
				 $records;
	}

	// RESTORE DATABASE (ADMIN ONLY)
	function restoreDatabase($timestamp) {
		$restorePath = DATA_FOLDER . $timestamp .'_mg2db_counter.php';
		$this->load  = (preg_match('/^[0-9]{10}$/', $timestamp))?
							$this->loadDatabaseFlatfile($restorePath)
							:
							false;

		if ($this->load === false)		return -2;	// couldn't read backup file
		if (count($this->data) < 1)	return -3;	// no data to restore
		return $this->resetComments('all');			// -1 write error, 0 to n number of records
	}

	// IMPORT A COMPLETE COUNTER DATABASE (USED CONVERT, ADMIN ONLY)
	function importDatabase($database=false) {
		if (!is_array($database)) return false;

		$this->data = $database;
		return (($records = $this->writeDatabase()) === -1)?
				 false
				 :
				 $records;
	}

	// READ DATABASE
	function loadDatabase() {
		$this->data = array();	// database data array
		$this->chgd = array();	// changed data records
		return ($this->type === 'sql')?
				 $this->loadDatabaseSQL()
				 :
				 $this->loadDatabaseFlatfile();
	}

	// READ DATABASE - FLAT FILE
	function loadDatabaseFlatfile($pathDB='default') {

		// COUNTER FILE PATH
		$pathDB = ($pathDB === 'default')? $this->path : $pathDB;

		if (is_file($pathDB)) {
			if (!$fp = fopen($pathDB, 'rb')) return false;

			while (!feof($fp)) {
				if (fgets($fp, 2) !== '#')					continue; // no data row?
				$record = fgetcsv($fp, 1024, "\t");
				if (($imageID = (int)$record[0]) < 1)	continue; // invalid item id
				$this->data[$imageID] = $record;
			}
			fclose($fp);
		}
		return true;
	}

	// READ DATABASE - SQL
	function loadDatabaseSQL() {
		$query = 'SELECT * FROM `mg2db_counter` WHERE 1';
		if (!$result = mysql_query($query)) return false;

		while ($record = mysql_fetch_array($result, MYSQL_NUM)) {
			$this->data[(int)$record[0]] = $record;
		}
		return true;
	}

	// WRITE DATA
	function writeDatabase($action='all', $imageIDs=false) {
		if ($this->load !== true) return -1;

		return ($this->type === 'sql')?
				 $this->writeDatabaseSQL($action, $imageIDs)
				 :
				 $this->writeDatabaseFlatfile();
	}

	// WRITE DATA - FLAT FILE
	function writeDatabaseFlatfile() {
		$buffer  = "-- MG2/kh_mod click and comment counter --\n";
		$records = 0;
		$writeOK = false;
		foreach($this->data as $line) {
			if (!is_array($line)) continue;	// no record?
			$buffer.= '#';
			$buffer.= implode("\t", $line);
			$buffer.= "\n";
			$records++;
		}

		// NO COUNTER RECORDS
		if ($records < 1)
		{
			$writeOK = (is_file($this->path))?
						  unlink($this->path)	// there's nothing to write, delete counter file
						  :
						  true;						// there's nothing to write, but no counter file
		}
		// WRITE COUNTER FILE
		elseif (($fp = @fopen($this->path, 'wb'))	||
				  (rename($this->path, sprintf('%s%s_mg2db_counter.php',
													DATA_FOLDER,
													time()
											  ))				&&
					($fp = fopen($this->path, 'wb')))
				 )
		{
			flock($fp, LOCK_EX);					// do an exclusive lock
			$writeOK = fwrite($fp, $buffer);
			flock($fp, LOCK_UN);					// release the lock
			fclose($fp);
		}
		return ($writeOK)? $records:-1;
	}

	// WRITE DATABASE - SQL
	function writeDatabaseSQL($action, $imageIDs) {
		// USE DIFFERENT RECORDS AND $records IS NOT AN ARRAY?
		if ($action !== 'all' && !is_array($imageIDs)) return -1;

		$table  = 'mg2db_counter';
		$result = -1;
		switch ($action) {
			case 'new':
			case 'upd':
				$values = array();
				foreach ($imageIDs as $id) {
					if (!array_key_exists($id, $this->data)) continue;	// no record?
					$record	 = $this->data[$id];
					$values[] = $this->getSQLCounterValues($record);
				}
				$query = 'REPLACE INTO `'. $table .'` VALUES'. implode(',', $values);
				if (mysql_query($query)) $result = mysql_affected_rows();
				break;
			case 'all': if(!defined('USER_ADMIN')) break;
				$query = 'TRUNCATE TABLE `'. $table .'`'; if (!mysql_query($query)) break;
				$towrite = 0;
				$written = 0;
				foreach ($this->data as $record) {
					$towrite++;
					$query = 'INSERT INTO `'. $table .'` VALUES'. $this->getSQLCounterValues($record);
					if (mysql_query($query)) $written++;
				}
				if ($written === $towrite) $result = $written;
				break;
			case 'del': if(!defined('USER_ADMIN')) break;
				$query = 'DELETE FROM `'. $table .'` WHERE imageID IN ('. implode(',', $imageIDs) .')';
				if (mysql_query($query)) $result = mysql_affected_rows();
				break;
			default: $this->status = 'Error in counter database';
		}
		return $result;
	}

	// GET VALUES TO WRITE INTO SQL DATABASE
	function getSQlCounterValues(&$record) {
		return sprintf("(%d ,%d ,%d ,%d ,%d ,%d ,%f ,%d)",
					 $record[0],												// image id
					 $record[1],												// num mouse clicks
					 $record[2],												// first click
					 $record[3],												// last click
					 (array_key_exists(4, $record))? $record[4]:0,	// num comments
					 (array_key_exists(5, $record))? $record[5]:0,	// comment valid (boolean)
					 (array_key_exists(6, $record))? $record[6]:0,	// avg rating
					 (array_key_exists(7, $record))? $record[7]:0	// num rating
				 );
	}
}
?>
