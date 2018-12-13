<?php
//
// KEYWORDS CLASS, IMPLEMENTATION SHOULD BE A STATIC CLASS
// kh_mod 0.4.0 b3, add
class MG2Keywords {

	var $keywordIDs;			// keyword - index  relation
	var $keywordData;			// all keywords in an array
	var $keywordtAutoID;		// id of the latest data record
	var $loadStatus;			// data and id read status (true/false)

	// INITIALIZE KEYWORDS
	function MG2Keywords(&$parent) {
		$this->mg2				  = &$parent;
		$this->pathDBFlatIndex = DATA_FOLDER .'mg2db_ikeyword.php';
		$this->pathDBFlatData  = DATA_FOLDER .'mg2db_keywords.php';
		$this->loadStatus		  = $this->loadKeywordDB();
	}

	function getKeywords($itemID) {

		// INIT RETURN VALUE
		$keywords = array();

		// GET KEYWORD INDICES AS ARRAY
		$keywordIDs = $this->getKeywordIDs($itemID);

		if ($keywordIDs !== false)
		foreach($keywordIDs as $keywordID) {
			if (!array_key_exists($keywordID, $this->keywordData)) continue;

			// CONVERT FIRST TO UPPER CASE
			$keywords[] = mb_convert_case(
									$this->keywordData[$keywordID],
									MB_CASE_TITLE, $this->mg2->charset
							  );
		}

		return $keywords;
	}

	// SET KEYWORDS FOR ITEM
	function setKeywords($itemID) {

		// CLEAN UP NEW INPUT
		$inputIDs = $this->cleanKeywords();

		// GET KEYWORD INDICES AS ARRAY
		$savedIDs = $this->getKeywordIDs($itemID);

		// IF DIFFRENT KEYWORD INPUT TO SAVED?
		if (array_diff($savedIDs, $inputIDs) ||
			 array_diff($inputIDs, $savedIDs))
		{
			// IF KEYWORD INPUT EMPTY, DELETE IT
			if (empty($inputIDs)) {
				unset($this->keywordIndex[$itemID]);
			}
			// SAVE KEYWORD INDICES AS STRING
			else {
				$this->keywordIndex[$itemID] = implode(',', $inputIDs);
			}

			// WRITE ITEM KEYWORDS INTO DATA BASE
			$setOK = $this->writeKeywordDB('index', 'upd', array($itemID));
		}
		// KEYWORDS ALREDY SET
		else {
			$setOK = 1;
		}

		return ($setOK > 0)? count($inputIDs):false;
	}

	// ***************************************************************************** //
	// ****************************** PRIVATE METHODS ****************************** //
	// ***************************************************************************** //

	// CLEAN AND SAVE KEYWORDS INTO AN ARRAY
	function cleanKeywords() {

		// INIT RETURN VALUE
		$keywordInput = array();

		// CLEAN UP FORM VALUES
		$keysString = (get_magic_quotes_gpc())?
						  stripslashes($_POST['keywords'])
						  :
						  $_POST['keywords'];
		$keysString = trim(strip_tags($keysString));
		$keysString = substr($keysString, 0, 2048);
		$keysArray  = preg_split("/\s*[,\t]+\s*/", $keysString, -1, PREG_SPLIT_NO_EMPTY);

		// CONVERT KEYWORDS TO INDICES
		$newKeywordIDs = array();
		foreach($keysArray as $keyword) {

			// CONVERT KEYWORD TO LOWER CASE
			$keyword = extension_loaded('mbstring')?
						  mb_convert_case($keyword, MB_CASE_LOWER, $this->mg2->charset)
						  :
						  strtolower($keyword);

			// KEYWORD EXISTS NOT YET IN DATABASE
			if (($keywordID = array_search($keyword, $this->keywordData)) === false) {
				$keywordID							 = ++$this->keywordAutoID;
				$newKeywordIDs[]					 = $keywordID;	// save new keyword id
				$this->keywordData[$keywordID] = $keyword;	// save new keyword
			}

			// SAVE KEYWORD ID FOR ITEM
			$keywordInput[$keywordID] = $keywordID;
		}

		// THERE ARE NEW KEWORDS, WRITE THEM INTO DATABASE
		if (count($newKeywordIDs) > 0) {
			$this->keywordData[0] = $this->keywordAutoID;
			$this->writeKeywordDB('data', 'add', $newKeywordIDs);
		}

		return $keywordInput;
	}

	// GET KEYWORD INDICES OF ITEM AS ARRAY
	function getKeywordIDs($itemID) {

		// LOAD STATUS OK?
		if ($this->loadStatus !== true) return false;

		// ALREADY EXISTS KEYWORDS FOR THIS ITEM
		return array_key_exists($itemID, $this->keywordIndex)?
				 explode(',', $this->keywordIndex[$itemID])
				 :
				 array();
	}

	// DELETE ITEM RECORD OF KEYWORDS
	function deleteItemKeywords($toDeleteIDs) {
		if (!is_array($toDeleteIDs))		return false;
		if ($this->loadStatus !== true)	return false;

		// DELETE ITEM KEYWORD RECORDS
		$deletedIDs	= array();
		foreach ($toDeleteIDs as $toDeleteID) {
			if (isset($this->keywordIndex[$toDeleteID])) {
				unset($this->keywordIndex[$toDeleteID]);		// delete item keyword record
				$deletedIDs[$toDeleteID] = $toDeleteID;		// store deleted id
			}
		}

		// WRITE DATABASE
		if (!empty($deletedIDs)) $this->writeKeywordDB('index', 'del', $deletedIDs);
	}

	// READ DATABASE
	function loadKeywordDB() {

		// KEYWORDS NOT YET READ?
		return (empty($this->loadStatus))?
				 $this->loadKeywordFlatfile()
				 :
				 $this->loadStatus;
	}

	// READ DATABASE - FLAT FILE
	function loadKeywordFlatfile($pathDB='default') {

		// GET KEYWORD - INDEX RELATION
		$this->keywordIndex	= (is_file($this->pathDBFlatIndex))?
									  unserialize(file_get_contents($this->pathDBFlatIndex))
									  :
									  array();

		// GET KEYWORD DATA
		$this->keywordData	= (is_file($this->pathDBFlatData))?
									  unserialize(file_get_contents($this->pathDBFlatData))
									  :
									  array();

		// GET AUTO ID OF KEYWORD DATABASE
		$this->keywordAutoID	= array_key_exists(0, $this->keywordData)?
									  $this->keywordData[0]
									  :
									  count($this->keywordData);

		// LOADING DATABASES OK?
		return (is_array($this->keywordIndex) && is_array($this->keywordData))?
				 true
				 :
				 false;
	}

	// WRITE DATABASE
	function writeKeywordDB($db='data', $action='all', $itemIDs=false) {
		if ($this->loadStatus !== true) return -1;

		return $this->writeKeywordFlatfile($db);
	}

	// WRITE DATA - FLAT FILE
	function writeKeywordFlatfile($db) {

		// INIT WRITE STATUS
		$writeOK = false;

		switch (strtolower($db)) {
			case 'data' :	$flatfile = 'pathDBFlatData';
								$towrite  = 'keywordData';
								break;
			case 'index':	$flatfile = 'pathDBFlatIndex';
								$towrite  = 'keywordIndex';
								break;
			default: return -1;
		}

		// NO KEYWORD RECORDS
		if (count($this->$towrite) < 1)
		{
			$writeOK = (is_file($this->$flatfile))?
						  unlink($this->$flatfile)		// there's nothing to write, delete keyword file
						  :
						  true;								// there's nothing to write, but no file
		}
		// WRITE KEYWORD DATA FILE
		elseif (($fp = @fopen($this->$flatfile, 'wb'))	||
				  (rename($this->$flatfile, sprintf('%s%s_mg2db_dkeywords.php',
												DATA_FOLDER,
												time()
											))							&&
					($fp = fopen($this->$flatfile, 'wb')))
				 )
		{
			flock($fp, LOCK_EX);	// do an exclusive lock
			$writeOK = fwrite($fp, serialize($this->$towrite));
			flock($fp, LOCK_UN);	// release the lock
			fclose($fp);
		}

		return ($writeOK)? count($this->keywordData):-1;
	}
}
?>