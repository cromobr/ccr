<?php
//////////////////////////////////////////////////////////////////////////////////
//                                                                              //
//    MG2                                                                       //
//    A PHP/HTML based image gallery script.                                    //
//                                                                              //
//    Copyright 2005 by Thomas Rybak                                            //
//    http://www.minigal.dk                                                     //
//    support@minigal.dk                                                        //
//                                                                              //
//    The script utilises Exif reader v 1.2 (free to use)                       //
//    Exif reader v 1.2                                                         //
//    By Richard James Kendall (richard@richardjameskendall.com)                //
//                                                                              //
//    -----------------                                                         //
//                                                                              //
//    MG2 is free software; you can redistribute it and/or modify               //
//    it under the terms of the GNU General Public License as published by      //
//    the Free Software Foundation; either version 2 of the License, or         //
//    (at your option) any later version.                                       //
//                                                                              //
//    MG2 is distributed in the hope that it will be useful,                    //
//    but WITHOUT ANY WARRANTY; without even the implied warranty of            //
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the              //
//    GNU General Public License for more details.                              //
//                                                                              //
//    TO COMPLY WITH THIS LICENSE, DO NOT REMOVE THE LINK TO THE MINIGAL        //
//    WEBSITE FROM YOUR GALLERY FRONT PAGE. THIS IS THE LEAST YOU CAN DO TO     //
//    SUPPORT THE DEVELOPMENT OF MG2!                                           //
//                                                                              //
//    You should have received a copy of the GNU General Public License         //
//    along with this program; if not, you can find it here:                    //
//    http://www.gnu.org/copyleft/gpl.html                                      //
//                                                                              //
//////////////////////////////////////////////////////////////////////////////////

//
// DISPLAY THUMBNAIL HEADER
// kh_mod 0.1.0, add, 0.3.1 changed
function thumbnails_begin($folderID) {
	global $mg2;
	$mg2->headline = ($folderID === 1)?
						  $mg2->gallerytitle
						  :
						  $mg2->br2line($mg2->all_folders[$folderID][2]);
	include_once('skins/'.$mg2->activeskin.'/templates/gallery_header.php');
}

//
// DISPLAY IMAGE OR FOLDER NOT EXISTS
// kh_mod 0.3.0, changed
function notexists($imageID, $folderID='', $page='') {
	global $mg2;
	if ($imageID === 'demaged')
		$noexists = $mg2->lang['requestfolder'] . $mg2->lang['damaged'];
	elseif ((int)$imageID > 0)
		$noexists = $mg2->lang['noimage'];
	else
		$noexists = $mg2->lang['requestfolder'] . $mg2->lang['notexists'];
	$folderID = ($folderID)? $folderID:'1';
	$page		 = ($page)? $page:'1';
	$mg2->headline = $mg2->gallerytitle;
	include_once ('skins/'.$mg2->activeskin.'/templates/gallery_header.php');
	echo '
		<div style="text-align:center;white-space:nowrap;font-weight:bold">
			<div style="margin-top:24px">'.$noexists.'</div>
			<div style="margin-top:15px">
				<a href="'.$mg2->getGalleryLink(array('fID'=>$folderID,'page'=>$page)).'">'
				.$mg2->lang['viewgallery'].'</a>
			</div>
		</div>
	';
	include_once('skins/'.$mg2->activeskin.'/templates/gallery_footer.php');
	exit();
}


class mg2db {

	//
	// RETURNS A SINGLETON INSTANCE OF THE CLASS
	// kh_mod 0.4.0, add
	function getInstance($class) {

		if (!isset($this->$class)) {
			$this->$class = new $class($this);
		}
		return $class;
	}

	//
	// GET ICON FOR FLASH CONTENT
	// kh_mod 0.4.0, add
	function getFileIcon($imageID, $contentType=null, $useImage=false) {

		// DEFAULT ICON ID
		$iconID = $imageID;

		// IF SET CONTENT TYPE NOT YET
		if (!isset($contentType)) $contentType = $this->getContentType($imageID);

		// GET IMAGES PATH
		$imagesPath = defined('USER_ADMIN')?
						  ADMIN_IMAGES
						  :
						  'skins/'.$this->activeskin.'/images/';

		do {
			do {
				// IF ITEM 'IMAGE'
				if (isset($contentType['image']))									break;

				// OR IF SET AND EXISTS SPLASH ICON
				$Splashes = $this->getInstance('MG2Splashes');
				$iconID	 = $this->$Splashes->getSplashID($imageID);
				if (isset($this->all_images[$iconID]))								break;

				// GET DEFAULT ICON (FLASH)
				$iconID		= 0;
				$iconFile	= (isset($contentType['video']))?
								  $imagesPath .'video.gif'
								  :
								  $imagesPath .'audio.gif';
				$iconInfo   = getimagesize($iconFile);
				$iconWidth  = $iconInfo[0];
				$iconHeight = $iconInfo[1];
				break 2;
			}
			while(0);

			// ITEM 'IMAGE' OR IF SET SPLASH ICON
			$imageRC		= &$this->all_images[$iconID];
			$iconFile	= $this->get_path($imageRC[6], $imageRC[7], 'thumb');
			$iconWidth	= $imageRC[10];
			$iconHeight = $imageRC[11];

			// DO NOT EXISTS THUMB PICTURE?
			if($useImage && !is_readable($iconFile)) {
				$iconFile = $this->get_path($imageRC[6], $imageRC[7], 'medium');
				// DO NOT EXISTS MEDIUM PICTURE?
				if(!is_readable($iconFile)) {
					$iconFile = $this->get_path($imageRC[6], $imageRC[7]);
				}
			}
		}
		while(0);

		// CHECK ICON FILE
		if (!is_readable($iconFile)) {
			$iconID		= -1;
			$iconFile   = $imagesPath .'1x1.gif';
			$iconWidth  = $this->thumbMaxWidth;
			$iconHeight = $this->thumbMaxHeight;
		}

		return array (	$iconID,
							$iconFile,
							$iconWidth,
							$iconHeight,
				 );
	}

	//
	// READ ENTIRE FLATFILE DATABASE
	// kh_mod 0.3.0, add
	function readDB() {

		// READ FROM MYSQL, kh_mod 0.3.0, add
		return ($this->sqldatabase)?
				 $this->readDBSQL()
				 :
				 $this->readDBFlatfile();
	}

	//
	// READ ENTIRE FLATFILE DATABASE
	// kh_mod 0.3.1, changed
	function readDBFlatfile($pathDB='default') {

		// INIT  RETURN VALUES
		$readFolders = $readImages = false;

		// GET DEFAULT PATH
		if ($pathDB === 'default') {
			$fDB = DATA_FOLDER .'mg2db_fdatabase.php';
			$iDB = DATA_FOLDER .'mg2db_idatabase.php';
		}
		// GET RESTORE PATH (ADMIN ONLY)
		elseif ($backup = $this->getBackupPath($pathDB)) {
			$fDB = sprintf($backup, 'fdatabase');
			$iDB = sprintf($backup, 'idatabase');
		}
		else return array($readFolders, $readImages);	// no valid path

		// ************************************ FOLDER DATABASE  ************************************ //

		// RESET FOLDER ARRAY
		$this->all_folders  = array();
		$this->folderautoid = 0;

		$now = time();	// in order to check if the folder or image date in future
		do {
			if (!is_file($fDB)) {
				$this->folderautoid = 1;
				$this->all_folders[$this->folderautoid] = $this->getDefaultRootFolder();
				break; 								// no data file
			}

			$fp = fopen($fDB,'rb');
			if (!is_resource($fp))	break;	// cannot open data file

			$num_records		  = 0;
			$this->folderautoid = (int)fgets($fp, 16);
			if (defined('USER_ADMIN')) {
				while (!feof($fp)) {
					if (fgets($fp, 2) !== '#')					continue;	// no data row?
					$record = fgetcsv($fp, 4600, "\t");
					if (($folderID = (int)$record[0]) < 1) continue;	// invalid folder id
					$this->all_folders[$folderID] = $record;
					$num_records++;
				}
			}
			else {
				while (!feof($fp)) {
					if (fgets($fp, 2) !== '#')					continue;	// no data row?
					$record = fgetcsv($fp, 4600, "\t");
					if (($folderID = (int)$record[0]) < 1) continue;	// invalid folder id
					if ((int)$record[4] > $now)				continue;	// date in future?
					if ((int)$record[5] < 0)   				continue;	// folder locked?
					$this->all_folders[$folderID] = $record;
					$num_records++;
				}
			}
			fclose($fp);
			$readFolders = $num_records;
		}
		while(0);

		// ************************************ IMAGE DATABASE  ************************************ //

		// RESET IMAGE ARRAY
		$this->all_images	= array();
		$this->autoid		= 0;

		do {
			if (!is_file($iDB)) 		break;	// no data file

			$fp = fopen($iDB,'rb');
			if (!is_resource($fp))	break;	// cannot open data file

			$num_records  = 0;
			$this->autoid = (int)fgets($fp, 16);
			if (defined('USER_ADMIN')) {
				while (!feof($fp)) {
					if (fgets($fp, 2) !== '#')					continue;	// no data row?
					$record = fgetcsv($fp, 4600, "\t");
					if (($imageID = (int)$record[0]) < 1)	continue;	// invalid image id
					$this->all_images[$imageID] = $record;
					$num_records++;
				}
			}
			else {
				while (!feof($fp)) {
					if (fgets($fp, 2) !== '#')					continue;	// no data row?
					$record = fgetcsv($fp, 4600, "\t");
					if (($imageID = (int)$record[0]) < 1)	continue;	// invalid image id
					if ((int)$record[4] > $now)				continue;	// date in future?
					if ((int)$record[5] < 0)   				continue;	// folder locked?
					$this->all_images[$imageID] = $record;
					$num_records++;
				}
			}
			fclose($fp);
			$readImages = $num_records;
		}
		while(0);

		return array($readFolders, $readImages);
	}

	//
	// READ ENTIRE MYSQL DATABASE
	// kh_mod 0.3.0, add
	function readDBSQL() {

		// INIT  RETURN VALUES
		$readFolders = $readImages = false;

		// FOLDER DATABASE
		$query = (defined('USER_ADMIN'))?
					'SELECT * FROM `mg2db_fdatabase` WHERE 1'
					:
					// date in past and position >= 0 only
					'SELECT * FROM `mg2db_fdatabase` WHERE
					 timestamp < UNIX_TIMESTAMP() AND position >=0';
		if ($result = mysql_query($query)) {
			$this->all_folders  = array();
			$this->folderautoid = 0;
			while ($record = mysql_fetch_array($result,MYSQL_NUM)) {
				$folderID = (int)$record[0];
				$this->all_folders[$folderID] = $record;
				if ($this->folderautoid < $folderID) $this->folderautoid = $folderID;
			}
			if ($this->folderautoid < 1) {
				$this->folderautoid = 1;
				$this->all_folders[$this->folderautoid] = $this->getDefaultRootFolder();
			}
			$readFolders = mysql_num_rows($result);
		}

		// IMAGE DATABASE
		$query = (defined('USER_ADMIN'))?
					'SELECT * FROM `mg2db_idatabase`'
					:
					// date in past and position >= 0 only
					'SELECT * FROM `mg2db_idatabase` WHERE
					 timestamp < UNIX_TIMESTAMP() AND position >=0';
		if ($result = mysql_query($query)) {
			$this->all_images	= array();
			$this->autoid		= 0;
			while ($record = mysql_fetch_array($result,MYSQL_NUM)) {
				$imageID = (int)$record[0];
				$this->all_images[$imageID] = $record;
				if ($this->autoid < $imageID) $this->autoid = $imageID;
			}
			$readImages = mysql_num_rows($result);
		}
		return array($readFolders, $readImages);
	}

	//
	// DEFAULT ROOT FOLDER
	//  kh_mod 0.3.0, add
	function getDefaultRootFolder() {
		return	array(
						1,							// folderID						[0]
						'root',					// in folder (#id)			[1]
						'',						// folder name					[2]
						'',						// introtext					[3]
						$this->installdate,	// timestamp (publish off)	[4]
						1,							// folder position			[5]
						0,							// imageID for folder icon	[6]
						6,							// folder sort mode			[7]
						'',						// password						[8]
						0,							// number of cols				[9]
						0,							// number of rows				[10]
						0							// folder type					[11]
					);
	}

	//
	// READ THE SETTINGS FROM MYSQL
	// kh_mod 0.3.0, add; 0.4.0 b3 changed
	function read_sDBSQL() {
		// CONNECT TO SQL SERVER
		if (!$con = @mysql_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD)) {
			$message = 'MySQL-Error: No connect!';
			include('admin/fatal_error.php');
			exit();
		}
		// SELECT DATABASE
		if (!$cDB = @mysql_select_db(DB_NAME, $con)) {
			$message = 'MySQL-Error: Database couldn\'t be selected!';
			include('admin/fatal_error.php');
			exit();
		}
		// GET SETTINGS
		$query = 'SELECT * FROM `mg2db_settings` WHERE 1';
		if ($result = mysql_query($query)) {
			$values = mysql_fetch_array($result,MYSQL_ASSOC);
			if (is_array($values)) {
				foreach($values as $key=>$setting) {
					$this->$key = $setting;
				}
				settype($this->metasetting, 'integer');
				settype($this->navtype, 'integer');
				settype($this->showexif, 'integer');
				settype($this->commentsets, 'integer');
				settype($this->foldersetting, 'integer');
				settype($this->layoutsetting, 'integer');
				settype($this->thumbMaxWidth, 'integer');
				settype($this->thumbMaxHeight, 'integer');
				settype($this->imagecols, 'integer');
				settype($this->imagerows, 'integer');
				settype($this->inactivetime, 'integer');
				settype($this->extendedset, 'integer');
				settype($this->installdate, 'integer');
			}
		}
	}

	//
	// SELECT ENTRIES FROM DATABASE
	// kh_mod 0.3.0, changed
	function select($item, &$array, $row, $sortmode=-1) {
		$selectarray = array();
		if (!is_array($array)) return $selectarray;

		foreach ($array as $record) {
			if ($record[$row] == $item) $selectarray[] = $record;
		}
		if ($sortmode > -1 && count($selectarray) > 1) {
			$this->sort($selectarray, $sortmode & 15, $sortmode & 16);
		}
		return $selectarray;
	}

	//
	// SORT SELECT RESULTS
	// kh_mod 0.3.0, changed
	function sort(&$datatable, $field=0, $direct=0) {
		$comp = ($direct)? '$b,$a':'$a,$b';
		$code = '$result=';
		$code.= ($field===2||$field===3||$field===6)?	// folder name/image title, description or file name
				  'strnatcasecmp($a['.$field.'],$b['.$field.']);'
				  :
				  '(int)$a['.$field.'] - (int)$b['.$field.'];';
		$code.= 'return ($result===0)? $a[0]-$b[0]:$result;';
		$comp_func = create_function($comp, $code);
		usort($datatable, $comp_func);
	}

	//
	// GET SORT MODE FOR FOLDER
	// kh_mod 0.3.0, add
	function getFolderSortMode($folderID=false) {

		// USE GLOBAL SORT SETTING
		if ($this->foldersetting & 15) {
			$tmp_sortmode = $this->foldersetting;
		}
		// USE FOLDER SETTING WITH ID
		elseif (isset($this->all_folders[$folderID])) {
			$tmp_sortmode = (int)$this->all_folders[$folderID][7];
		}
		// USE CURRENT FOLDER SETTING
		else {
			$tmp_sortmode = $this->images_sortmode;
		}

		// MAP IMAGE SORT MOD TO FOLDER
		switch ($tmp_sortmode & 15) {
			case  6 : $trans_sortby = 2; break;	// filename	->	foldername
			case  2 : $trans_sortby = 5; break;	// title		->	position
			case  3 : $trans_sortby = 3; break;	// description->	description
			case 12 : $trans_sortby = 5; break;	// filesize	->	position
			case  8 : $trans_sortby = 5; break;	// width		->	position
			case  9 : $trans_sortby = 5; break;	// height		->	position
			case  4 : $trans_sortby = 4; break;	// timestamp	->	timestamp
			case  5 : $trans_sortby = 5; break;	// position	->	position
			default : $trans_sortby = 2;
		}

		return ($trans_sortby | ($tmp_sortmode & 16));
	}

	//
	// BUILD A LINK FOR GALLERY
	// kh_mod 0.3.1, changed
	function getGalleryLink($param=array()) {

		// SEO LINK
		if ($this->extendedset & 32) {

			$raw_link = array('index'=>'index', 'showform'=>'', 'page'=>'', 'term'=>'.html');

			// FOLDER PAGE
			if (!empty($param['page'])) {
				$raw_link['page'] = '-'. $param['page'];
				unset($param['page']);
			}

			// FOLDER ID
			if (!empty($param['fID'])) {
				$raw_link['index'] = 'folder'. (int)$param['fID'];
				unset($param['fID']);
			}
			// IMAGE ID
			elseif (!empty($param['iID'])) {
				$raw_link['index'] = 'image'. (int)$param['iID'];
				unset($param['iID']);
				if (!empty($param['showform'])) {
					$raw_link['showform'] = '-showform';
					unset($param['showform']);
				}
			}
			// SLIDESHOW ID
			elseif (!empty($param['slideshow'])) {
				$raw_link['index'] = 'slideshow'. (int)$param['slideshow'];
				unset($param['slideshow']);
			}

			// BUILD SEO LINK
			$link = implode('', $raw_link);
		}
		else {
			// DEFAULT LINK
			$link = $this->indexfile;
		}

		// QUERY APPENDIX
		$and = '?';
		foreach ($param as $key=>$query) {
			$link.= $and . $key .'='. $query;
			$and  = '&amp;';
		}

		return "http://".$_SERVER['HTTP_HOST']."/galeria/".$link;
	}

	//
	// BUILD A TOKEN FOR VALIDATION FORM REQUESTS
	// kh_mod 0.3.1, add
	function getVerificationToken($n=6) {
		// INIT VERIFY TOKEN
		$token = '';

		// DEFINE AVAILABLE CHARS
		$availableChars = "23456789abcdfghjkmnpqrstvwxABCDEFGHJKLMNPRSTUVWXYZ";
		$maxCharsIndex  = strlen($availableChars) - 1;

		// CONCATENATE RANDOM CHARS TO TOKEN
		for ($idx=0; $idx<$n; $idx++) {
			$availableChars = str_shuffle($availableChars);
			$token 			.= $availableChars[mt_rand(0, $maxCharsIndex)];
		}

		return (string)$token;
	}

	//
	// DISPLAY SLIDIESHOW ICON
	// kh_mod 0.3.0, changed
	function displaySlideshowIcon($attrib='',$inactiv=false) {
		if (!empty($this->startimage)) {
			$image = '<a href="'. $this->startimage .'">';
			$image.= '<img src="skins/'. $this->activeskin .'/images/slideshow.gif" ';
			$image.= 'width="15" height="17" '. $attrib .' alt="'. $this->lang['viewslideshow'];
			$image.= '" title="'. $this->lang['viewslideshow'] .'" border="0" /></a>';
			echo $image;
		} elseif ($inactiv) {
			$image = '<img src="skins/'. $this->activeskin .'/images/slideshow.gif" ';
			$image.= 'width="15" height="17" '. $attrib .' alt="" border="0" />';
			echo $image;
		}
	}

	//
	// IMAGE NAVIGATION
	// kh_mod 0.3.0, changed
	function imagenavigation($imageID, $thumbset=false) {

		// GET FOLDER ID AND IMAGES
		$folderID = (int)$this->all_images[$imageID][1];
		$images   = $this->select($folderID, $this->all_images, 1, $this->images_sortmode);

		// ONLY ONE IMAGE?
		if (($totalImages = count($images)) < 2) return array(1,-1,-1);

		// NUMBER OF FOLDERS - ADMIN OR SHOW FOLDERS IN CATEGORIES
		$cfolders = (defined('USER_ADMIN') || ($this->foldersetting & 1024))?
						0
						:
						count($this->select($folderID, $this->all_folders, 1));

		// CALCULATE CURRENT IMAGE AND PAGE
		$currPage  = 1;
		$currImage = 0;
		$currCols  = (int)$this->all_folders[$folderID][9];	// local setting
		$currRows  = (int)$this->all_folders[$folderID][10];	// local setting
		if ($currCols < 1) $currCols = $this->imagecols;		// glabal setting
		if ($currRows < 1) $currRows = $this->imagerows;		// glabal setting
		for ($i=0; $i < $totalImages; $i++) {
			if ($imageID == $images[$i][0]) {
				$currImage = $i + 1;
				$currPage  = (int)ceil(($currImage + $cfolders) / ($currCols * $currRows));
				break;
			}
		}

		// NAVIGATION ITEM ACTIV
		$activ['first'] = (bool)($images[0][0] != $imageID);
		$activ['prev']  = (bool)(isset($images[$currImage - 2][0]));
		$activ['next']  = (bool)(isset($images[$currImage][0]));
		$activ['last']  = (bool)($images[$totalImages - 1][0] != $imageID);

		// QUERIES FOR NAVIGATION STRING
		if ($activ['first']) $queries['first'] = array('iID'=>$images[0][0],'page'=>$currPage);
		if ($activ['prev'])  $queries['prev']  = array('iID'=>$images[$currImage - 2][0],'page'=>$currPage);
		if ($activ['next'])  $queries['next']  = array('iID'=>$images[$currImage][0],'page'=>$currPage);
		if ($activ['last'])  $queries['last']  = array('iID'=>$images[$totalImages - 1][0],'page'=>$currPage);

		// NAVIGATION LINKS FOR ADMIN
		if (defined('USER_ADMIN')) {
			$str_navlink = '<a href="'. ADMIN_INDEX .'?editID=%s&amp;page=%s">%%s</a>';
			foreach ($queries as $key=>$query) {
				$links[$key] = vsprintf($str_navlink, $query);
			}
		}
		// NAVIGATION LINKS FOR GALLERY
		else {
			$str_navlink = '<a href="%s">%%s</a>';
			foreach ($queries as $key=>$query) {
				$links[$key] = sprintf($str_navlink, $this->getGalleryLink($query));
			}
		}

		//  IMAGE NUMBER OF TOTAL
		$this->nav_this = sprintf('%d %s %d', $currImage, $this->lang['of'],	$totalImages);

		if ($this->navtype & 1)	$this->text_imagenavigation($links, $activ);
		if ($this->navtype & 2) $this->icon_imagenavigation($links, $activ);
		if ($this->navtype & 4) $this->thumb_imagenavigation($currImage, $images, $thumbset);

		return array(
					$currPage,
					($activ['prev'])?$images[$currImage - 2][0]:-1,	// prev image id
					($activ['next'])?$images[$currImage][0]:-1		// next image id
				);
	}

	//
	// IMAGE NAVIGATION BY TEXTSTRING
	// kh_mod 0.3.0, changed
	function text_imagenavigation($links, $activ) {
		// FIRST IMAGE
		$this->nav_first = ($activ['first'])?
								 sprintf($links['first'], $this->lang['first'])
								 :
								 $this->lang['first'];

		// PREV IMAGE
		$this->nav_prev = ($activ['prev'])?
								sprintf($links['prev'], $this->lang['prev'])
								:
								$this->lang['prev'];

		// NEXT IMAGE
		$this->nav_next = ($activ['next'])?
								sprintf($links['next'], $this->lang['next'])
								:
								$this->lang['next'];

		// LAST IMAGE
		$this->nav_last = ($activ['last'])?
								sprintf($links['last'], $this->lang['last'])
								:
								$this->lang['last'];
	}

	//
	// IMAGE NAVIGATION BY ICONS
	// kh_mod 0.3.0, changed
	function icon_imagenavigation($links, $activ) {

		// GET NAVIGATION ICON PATH
		$iconpath = (defined('USER_ADMIN'))?
						ADMIN_IMAGES
						:
						'skins/'. $this->activeskin .'/images/';

		// SET NAVIGATION NAMES
		$navigation = array ('first' => 'nav_first',
									'prev'  => 'nav_prev',
									'next'  => 'nav_next',
									'last'  => 'nav_last'
								  );

		// BUILT NVIGATION ITEMS
		foreach ($navigation as $key=>$item) {
			// NAVIGATION ITEM INACTIV
			if ($activ[$key]) {
				$icon  = $iconpath . $key .'_activ.gif';
				$size	 = (is_readable($icon))? getimagesize($icon):'';
				$image = sprintf('<img class="icon" src="%s" %s alt="%$3s" title="%$3s" />',
								$icon,
								$size[3],
								$this->lang[$key]
							);
				$this->$item = sprintf($links[$key], $image);
			}
			// NAVIGATION ITEM INACTIV OR NOT EXIST
			else {
				$icon  = $iconpath . $key .'_inactiv.gif';
				$size  = (is_readable($icon))? getimagesize($icon):'';
				$image = sprintf('<img class="icon" src="%s" %s alt="" />',
								$icon,
								$size[3]
							);
				$this->$item = $image;
			}
		}
	}

	//
	// IMAGE NAVIGATION BY THUMBS
	// kh_mod 0.2.0, add, 0.4.0 changed
	function thumb_imagenavigation($currImage, &$images, $thumbset) {

		// SET THUMB NUMBER AND SIZE
		$nthumbs = 5;		// default displayed nav thumbs total
		$divisor = 2.5;	// default divisor (thumb size)
		$poscurr = 3;		// default position of current thumb
		$isuffix = '';		// image suffix
		if (is_array($thumbset)) {
			$nthumbs = ($thumbset['num'] < 1)? 0:  (int)$thumbset['num'];
			$divisor = ($thumbset['div'] < 1)? 1:(float)$thumbset['div'];
			$poscurr = (int)ceil($nthumbs/2);
		}
		elseif (defined('USER_ADMIN')) {
			$divisor = 3.2;
			$isuffix = (string)('?'. rand(0,10000));
		}

		// GET START AND END INDEX
		$totalImages = count($images);
		$idx = max($currImage - $poscurr, 0);
		if ($idx + $nthumbs < $totalImages)
			$end = $idx + $nthumbs;
		else {
			$end = $totalImages;
			$idx = max($end - $nthumbs, 0);
		}

		// CREATE THUMB NAVIGATION LINE
		$imageTag		 = '<img class="%s" src="./%s'.$isuffix.'" %s title="%4$s" alt="%4$s" />';
		$this->nav_this = '';
		for (; $idx < $end; $idx++) {

			// CURRENT IMAGE ID
			$currID = (int)$images[$idx][0];

			// GET THUMB FILE
			list($thumbID, $thumbFile, $thumbWidth, $thumbHeight) = $this->getFileIcon($currID);

			// THUMB SIZE ATTRIBUTE
			$thumbSize = sprintf('width="%d" height="%d"',
								 max(round($thumbWidth/$divisor,  0), 5),
								 max(round($thumbHeight/$divisor, 0), 5)
							 );

			// THUMB TITLE
			$title = ($idx+1) .' '. $this->lang['of'] .' '. $totalImages;

			// DISPLAYED IMAGE
			if ($currImage === $idx+1) {
				$class = 'activ';
				$this->nav_this.= sprintf($imageTag, $class, $thumbFile, $thumbSize, $title);
				$this->nav_this.= "&nbsp;\n";
			}
			// NOT DISPLAYED IMAGES
			elseif (is_readable($thumbFile)) {
				$class = 'inactiv';
				$this->nav_this.= (defined('USER_ADMIN'))?
										sprintf("<a href=\"%s?editID=%s\">%s</a>&nbsp;\n",
											ADMIN_INDEX,
											$currID,
											sprintf($imageTag, $class, $thumbFile, $thumbSize, $title)
										)
										:
										sprintf("<a href=\"%s\">%s</a>&nbsp;\n",
											$this->getGalleryLink(array('iID'=>$currID)),
											sprintf($imageTag, $class, $thumbFile, $thumbSize, $title)
										);
			}
		}
	}

	//
	// CREATE METATAG TITLE
	// kh_mod 0.2.0, add, kh_mod 0.3.0, changed
	function getPagetitle($imageID, $folderID, $delimiter=' - ') {
		$metatitle	  = array();
		$foldername	  = $folderID ? trim($this->all_folders[$folderID][2]):'';
		$imagetitle	  = $imageID  ? trim($this->all_images[$imageID][2])  :'';
		$filename	  = $imageID  ? $this->getFilename($imageID)				:'';
		$gallerytitle = trim($this->gallerytitle);
		if (!$gallerytitle) $gallerytitle = ucfirst(trim($this->lang['gallery']));

		// GALLERY TITLE
		if ($this->metasetting & 1)						$metatitle[] = $gallerytitle;
		// FOLDER NAME
		if ($this->metasetting & 2) if ($foldername)	$metatitle[] = $foldername;
		elseif ($folderID == 1 && empty($metatitle))	$metatitle[] = $gallerytitle;
		// FILE NAME
		if ($this->metasetting & 4 && $filename)		$metatitle[] = $filename;
		// IMAGE TITLE
		if ($this->metasetting & 8 && $imagetitle)	$metatitle[] = $imagetitle;

		// RETURN METATEG TITEL
		return @htmlspecialchars(strip_tags(implode($delimiter, $metatitle)));
	}

	//
	// CREATE METATAG TITLE
	// kh_mod 0.3.0, add
	function getFilename($imageID) {

		$filename = trim($this->all_images[$imageID][6]);

		// ORIGINAL BUT FIRST CAPITAL
		if ($this->layoutsetting & 64) {
			$filename = ucfirst($filename);
		}

		// FIRST CAPITAL ONLY
		elseif ($this->layoutsetting & 128) {
			$filename = ucfirst(strtolower($filename));
		}

		// ALL CAPITALS
		elseif ($this->layoutsetting & 256) {
			$filename = strtoupper($filename);
		}

		// ALL LOWER CASES
		elseif ($this->layoutsetting & 512) {
			$filename = strtolower($filename);
		}

		// WITHOUT EXTENSION?
		if (~$this->layoutsetting & 1024)
			$filename = substr($filename,0,strrpos($filename,'.'));

		return $filename;
	}

	//
	// GET CONTENT TYPE
	// kh_mod 0.4.0 b3, add
	function getContentType($imageID, $type=NULL) {

		// EXISTS IMAGE ID
		if (!isset($this->all_images[$imageID])) return false;

		// GET TYPE CODE OF CONTENT ITEM
		if (!$typeCode = (int)$this->all_images[$imageID][16] & 1023) {
			$fileName  = $this->all_images[$imageID][6];
			$subDirec  = $this->all_images[$imageID][7];
			$filePath  = $this->get_path($fileName, $subDirec);
			$imageType = array(1=>'gif',2=>'jpg',3=>'png');
			$fileExt	  = ($fileInfo = @getimagesize($filePath))?	// takes about 0.2 sec. for 50 MB
							 $imageType[$fileInfo[2]]						// video or sound file!
							 :
							 substr(strrchr($fileName, '.'), 1);
			$typeCode  = $this->getContentCode($fileExt);
		}

		// SPLIT TYPE CODE IN FORMAT SECTIONS
		$formats = array (
							($typeCode &  63)	=> 'base',	// base format
							($typeCode & 448)	=> 'meta',	// meta format
							($typeCode & 512)	=> 'plugin'	// needed plug-in
						 );

		// LOOK UP ONE FORMAT ONLY
		if (isset($type)) {
			$code = $this->contentCodeTable($type);
			return (isset($formats[$code]))? true:false;
		}
		// GET ALL ITEM FORMATS
		else {
			$contentTypes = array();
			foreach ($this->contentCodeTable() as $type=>$code) {
				if (isset($formats[$code])) $contentTypes[$type] = $code;
			}
			return $contentTypes;
		}
	}

	//
	// GET CONTENT TYPE CODE
	// kh_mod 0.4.0 b3, add
	function getContentCode($fileExt) {

		// GET BASE (EXTENSION) CODE
		$baseCode = $this->contentCodeTable($fileExt);

		// BUILT TYPE CODE
		switch ($baseCode) {
			case  1:
			case  2:
			case  3: $typeCode =	$baseCode + 64;			// image format
						break;
			case 16: $typeCode =	$baseCode + 128 + 512;	// audio format and flash
						break;
			case 32:
			case 33:
			case 34:
			case 35: $typeCode =	$baseCode + 192 + 512;	// video format and flash
						break;
			default: $typeCode = 0;
		}

		return $typeCode;
	}

	//
	// TABLE OF FILE TYPES
	// kh_mod 0.4.0 b3, add
	function contentCodeTable($type=NULL) {

		// CODE TABLE
		$codeTable = array (
							'gif'		=>   1,	// 1 upto 15, images
							'jpg'		=>   2,
							'jpeg'	=>   2,
							'png'		=>   3,
							'mp3'		=>  16,	// 16 upto 31, audio
							'mp4'		=>  32,	// 32 upto 63, video
							'flv'		=>  33,
							'swf'		=>  34,
							'mov'		=>  35,
							'img'		=>  64,	// meta formats
							'image'	=>  64,
							'audio'	=> 128,
							'video'	=> 192,
							'flash'	=> 512	// flash plug-in
						 );

		// LOOK UP ONE FORMAT ONLY
		if (isset($type)) {
			$type = strtolower($type);
			return (isset($codeTable[$type]))? $codeTable[$type]:'notype';
		}
		// RETURN CODE TABLE
		else {
			return $codeTable;
		}
	}

	//
	// GET GALLERY IMAGE ATTRIBUTES
	// kh_mod 0.4.0 b3, add
	function getImgAttributes($imageName) {

		// GALLERY SKIN IMAGE PATH
		$imagePath = 'skins/'. $this->activeskin .'/images/';

		// GET IMAGE ATTRIBUTES
		$buttonInf = (is_readable($imagePath . $imageName))?
						 getimagesize($imagePath . $imageName)
						 :
						 '';
		if ($buttonInf) {
			$buttonRad = $buttonInf[1]/2;
			$imageName = rawurlencode($imageName);
			$imageSize = $buttonInf[3];
		}
		else {
			$buttonRad = 0;
			$imageName = '1x1.gif';
			$imageSize = 'width="1" height="1"';
		}

		return array(
					$imagePath . $imageName,
					$buttonRad,
					$imageSize
				 );
	}

	//
	// GALLERY NAVIGATION
	// kh_mod 0.3.0, changed
	function gallerynavigation($delimiter) {
		if (defined('IMAGEVIEW')) {
			$folderID = $GLOBALS['folderID'];
		}
		elseif (!empty($_REQUEST['fID'])) {
			$folderID = $this->parentfolder;
		}
		else {
			$folderID = 'root';
		}

		$path = array();
		do {
			if (!$folderRC = $this->all_folders[$folderID]) break;

			$foldername = (empty($folderRC[2]))?	// kh_mod 0.3.0, changed
								$this->lang['gallery']
								:
								$this->br2line($folderRC[2]);

			$path[$folderID] = sprintf('<a href="%s">%s</a>',
										 $this->getGalleryLink(array('fID'=>$folderID)),
										 $foldername
									 );
			$folderID		  = $folderRC[1];
			if (!empty($path[$folderID])) break;	// circle link!
		}
		while ((int)$folderID > 0);

		// SET LINK TO MAINPAGE, kh_mod 0.3.1, changed
		if (!empty($this->websitelink) && !$this->string_empty($this->websitetext))
			$path[] = '<a href="'. $this->websitelink .'" target="_top">'. $this->websitetext .'</a>';
		echo implode($delimiter, array_reverse($path));
	}

	//
	// PAGE NAVIGATION
	// kh_mod 0.3.0, changed
	function pagenavigation($folderID,$npages,$page=0,$delim=' | ') {
		if ($npages < 2) return;

		// INITIALISE VALUES
		$navlink = '<a href="%s">%s</a>';
		$navarr  = array($this->lang['page']);

		// GENERATE PAGE LINKS
		for ($i=1; $i <= $npages; $i++) {
			if ($page === $i) {
				$navarr[] = $i;
			} else {
				$query	 = array('fID'=>$folderID,'page'=>$i);
				$navarr[] = sprintf($navlink, $this->getGalleryLink($query), $i);
			}
		}

		// ALL PAGES LINK
		if ($page === 'all') {
			$navarr[] = $this->lang['all'];
		} else {
			$query	 = array('fID'=>$folderID, 'page'=>'all');
			$navarr[] = sprintf($navlink, $this->getGalleryLink($query),$this->lang['all']);
		}

		// BUILT NAVIGATION STRING
		return implode($delim, $navarr);
	}

	//
	// GET FOLDER ICON
	// kh_mod 0.3.2, changed
	function getFolderIcon($folderID) {

		$thumbID = ($this->foldersetting & 16)?	// global default folder icon?
					  0
					  :
					  (int)$this->all_folders[$folderID][6];

		do {
			// IMAGE FILE AS FOLDER ICON
			if ($thumbID > 0 && ($iconFile = $this->getThumbIcon($thumbID)))								break;

			// PASSWORD DEFAULT ICON
			if (trim($this->all_folders[$folderID][8]) && ($iconFile = $this->getPasswordIcon()))	break;

			// RANDOM, FIRST OR LATEST IMAGE AS FOLDER ICON
			if ($thumbID < 0 && ($iconFile = $this->getRandomIcon($folderID, $thumbID)))				break;

			// DEFAULT FOLDER ICON
			$iconFile = $this->getDefaultIcon($folderID);
		}
		while(0);

		// RETURN ICON FILE
		return $iconFile;
	}

	//
	// GET SETTED THUMBNAIL AS FOLDER ICON
	// kh_mod 0.3.0, add
	function getThumbIcon($thumbID) {
		$filename = $this->all_images[$thumbID][6];
		$subdir	 = $this->all_images[$thumbID][7];
		$iconFile = $this->get_path($filename, $subdir, 'thumb');
		if (is_file($iconFile)) {
			$this->width  = (int)$this->all_images[$thumbID][10];
			$this->height = (int)$this->all_images[$thumbID][11];
			$this->subfolder_class = 'subfolder border';
			return $iconFile;
		}
	}

	//
	// GET DEFAULT ICON FOR SETTED FOLDER PASSWORD
	// kh_mod 0.3.0, add
	function getPasswordIcon() {
		$iconFile	  = "skins/$this->activeskin/images/locked.gif";
		$fileInfo	  = getimagesize($iconFile);
		$this->width  = $fileInfo[0];
		$this->height = $fileInfo[1];
		$this->subfolder_class = 'subfolder';
		return $iconFile;
	}

	//
	// GET FIRST OR RANDOM IMAGE AS FOLDER ICON
	// kh_mod 0.3.0, add
	function getRandomIcon($subfolderID, $thumbID) {

		$thumbID = abs($thumbID);
		$icon		= false;

		// FIRST IMAGE
		if ($thumbID & 2) {
			$sortmode = (int)$this->all_folders[$subfolderID][7];	// sortmode of subfolder
		// LATEST IMAGE
		} elseif ($thumbID & 4) {
			$sortmode = 20;
		// RANDOM IMAGE
		} else {
			$sortmode = -1;
		}

		// GET ALL IMAGES OF THIS FOLDER
		$images = $this->select($subfolderID, $this->all_images, 1, $sortmode);

		// INKL. SUB FOLDERS?
		if ($thumbID & 8) {
			// RANDOM OR LATEST IMAGE
			if ($thumbID & 5) {
				$subfolders = $this->select($subfolderID, $this->all_folders, 1, -1);
				foreach ($subfolders as $folderRC) {
					if (trim($folderRC[8]) !== '') continue;						// is password set
					$images = array_merge($images, $this->select($folderRC[0], $this->all_images, 1, -1));
				}
				// IF LATEST IMAGE, SORT BY DATE
				if ($thumbID & 4) $this->sort($images, 4, true);
			}
			// FIRST IMAGE
			elseif (empty($images)) {
				$sortmode   = $this->getFolderSortMode($sortmode);				// sortmode of subfolder for sub sub folder
				$subfolders = $this->select($subfolderID, $this->all_folders, 1, $sortmode);
				foreach ($subfolders as $folderRC) {
					if (trim($folderRC[8]) !== '') continue;						// is password set
					$submode = $this->getFolderSortMode((int)$folderRC[7]);	// sortmode of sub sub folder
					$images	= $this->select($folderRC[0], $this->all_images, 1, $submode);
					if (!empty($images)) break;
				}
			}
		}

		// DOES FOLDER CONTAIN IMAGES, kh_mod 0.4.0 b3 changed
		while($images) {
			$selected = ($thumbID & 6)?
							key($images)			// first image (2), latest image (4)
							:
							array_rand($images);	// random image (1)

			if (($iconFile = $this->get_path($images[$selected][6],$images[$selected][7],'thumb')) === false ||
				 !is_file($iconFile))
			{
				unset($images[$selected]); continue;
			}

			$this->width  = (int)$images[$selected][10];
			$this->height = (int)$images[$selected][11];
			$this->subfolder_class = 'subfolder border';
			$icon = $iconFile;
			break;
		}

		return $icon;
	}

	//
	// GET DEFAULT ICON
	// kh_mod 0.3.0, add
	function getDefaultIcon($folderID) {

		// FOLDER CONTENT
		$images  = $this->select($folderID, $this->all_images, 1);
		$folders = $this->select($folderID, $this->all_folders, 1);

		// FOLDER NOT EMPTY?
		$iconFile = (count($images) > 0 || count($folders) > 0)?
						"skins/$this->activeskin/images/folder.gif"
						:
						"skins/$this->activeskin/images/emptyfolder.gif";

		// ICON SIZE
		$fileInfo	  = getimagesize($iconFile);
		$this->width  = $fileInfo[0];
		$this->height = $fileInfo[1];
		$this->subfolder_class = 'subfolder';

		return $iconFile;
	}

	//
	// READ FOLDER SETTINGS
	// kh_mod 0.3.0, changed
	function getFolderSettings($folderID) {
		if (!isset($this->all_folders[$folderID])) return false;

		// GET SETTINGS
		$folderRC = $this->all_folders[$folderID];
		$this->parentfolder	  = ($folderID === 1)?
										 'root'
										 :
										 (int)$folderRC[1];
		$this->introtext		  = (!isset($folderRC[3]))?
										 ''
										 :
										 $folderRC[3];
		$this->folder_publish  = (!isset($folderRC[4]))?
										 0
										 :
										 (int)$folderRC[4];
		$this->folder_position = (!isset($folderRC[5]))?
										 1
										 :
										 (int)$folderRC[5];
		$this->images_sortmode = ((int)$folderRC[7] < 1)?
										 6	// sort by by name, ascending
										 :
										 (int)$folderRC[7];
		return true;
	}

	/*
		Die Funktion 'getValidSubfolders' erwartet eine gültige, zuvor geprüfte $currentID. Das heißt, der zugehörige
		Ordner MUSS valid sein. Kann dies nicht gewährleistet werden, so muss dies noch vor der foreach-Schleife in der
		Funktion geprüft werden, z.B. mit:  if (!isset($this->all_folders[$currentID]))	return $subfolder;
	*/
	//
	// GET ALL VALID SUB FOLDER IDs
	// kh_mod 0.3.1a, add, 0.3.2 changed
	function getValidSubfolders($currentID) {
		settype($currentID, 'integer');										// set type on integer
		$subfolders = array();													// all valid sub folders
		$folderpath	= array(0=>1);												// first invalid id value

		foreach ($this->all_folders as $key=>$record) {
			$folderID  = $key;													// to inspect folder id
			$forbidden = $folderpath;											// invalid sub folders

			// GET PATH UPTO CURRENT ID FOR EACH FOLDER
			do {
				if ($folderID === $currentID)	{
					$subfolders[end($folderpath)][$key] = 1;				// contained in current folder
					$folderpath = $forbidden;									// reset invalid sub folders
					break;
				}

				$folderpath[$folderID] = $folderID;							// keep current folder id
				if (!isset($this->all_folders[$folderID]))  break;		// parent folder not exists
				$folderID = (int)$this->all_folders[$folderID][1];		// parent folder id
			}
			while (!isset($folderpath[$folderID]));						// damaged folder record (folderID == 0)
																						// or circle data
		}
		return $subfolders;
	}

	//
	// COUNT PICTURES INCL. ALL SUB FOLDERS
	// kh_mod 0.3.1a, add, 0.3.2 changed
	function countFolderPictures($subfolders) {

		// INIT NUMBER OF PICTURES IN SUB FOLDERS
		$pictures = array();

		// IS SUBFOLDERS EMPTY
		if (empty($subfolders)) return $pictures;

		// IN PHP 5.2 OR ABOVE BETTER 'array_fill_keys'
		foreach ($subfolders as $folderID=>$subs) {
			$pictures[$folderID] = 0;
		}

		// COUNT PICTURES FOR EACH SUB FOLDER
		foreach ($this->all_images as $record) {
			foreach ($subfolders as $folderID=>$subs) {
				// COUNT AND GO TO NEXT PICTURE ID
				if (isset($subs[(int)$record[1]])) { $pictures[$folderID]++; break; }
			}
		}

		// RETURN
		return $pictures;
	}

	//
	// JAVASCRIPT VERIFY FOR COMMENTS
	// kh_mod 0.1.0, add; 0.3.0, changed
	function jsformvalid() {
		$validpath = INC_FOLDER .'mg2_jsformvalid.php';
		if (($this->commentsets & 128) && is_readable($validpath)) {
			include($validpath);
		} else {
			echo '<script language="JavaScript" type="text/javascript">
					<!--
						function validateCompleteForm(a,b) { return true; }
					-->
					</script>';
		}
	}

	//
	// GET GD LIB VERSION
	//
	function gd_version() {
		$gdInfo = gd_info();
		$this->gd_version_number = trim(preg_replace("/[a-z()]/i", "", $gdInfo["GD Version"]));	// kh_mod 0.1.0, changed
		return $this->gd_version_number;
	}

	//
	// GALLERY SECURITY
	// kh_mod 0.3.0, changed
	function gallerysecurity($folderID, $imageID) {

		// FOLDER DISPLAY NOT YET OR FORBIDDEN
		$nextID  = $folderID;
		$pathIDs = array();
		do {
			if (!is_array($this->all_folders[$nextID])) notexists($imageID);
			$pathIDs[$nextID] = $nextID;
			if ((int)$nextID === 1) break;

			$nextID = (int)$this->all_folders[$nextID][1];			// get parent folder ID
			if (isset($pathIDs[$nextID])) notexists('demaged');	// circle link
		}
		while(1);

		// CHECK FOLDER PASSWORD
		foreach ($pathIDs as $id) {
			$folderpwd = trim($this->all_folders[$id][8]);

			// ADMIN MODE?
			if (!empty($_SESSION[GALLERY_ID]['adminmode']) &&
				$_SESSION[GALLERY_ID]['adminmode'] === $this->adminpwd) break;
			if (!empty($folderpwd) && $folderpwd !== $_SESSION[GALLERY_ID]['folderpwd'][$id]) {
				// PASSWORD ENTRY CORRECT?
				if (md5(strrev(md5($_POST['password']))) === $folderpwd) {
					$_SESSION[GALLERY_ID]['folderpwd'][$id] = $folderpwd;
					unset($_POST['password']);
				}
				// PASSWORD DIALOG
				else {
					$this->startimage	 = '';
					$pwdheadline		 = ($_POST['password'])?
												$this->lang['wronglogin']
												:
												$this->lang['enterpassword'];
					$pwdconfirm2url	 = $this->getGalleryLink((empty($imageID))?
												array('fID'=>$folderID)
												:
												array('iID'=>$imageID));
					$_REQUEST['fID']	  = $id;	 								// for gallerynavigation()
					$this->parentfolder = $this->all_folders[$id][1];	// for gallerynavigation()
					thumbnails_begin($id);
					include("skins/$this->activeskin/templates/password.php");
					include("skins/$this->activeskin/templates/gallery_footer.php");
					exit();
				}
			}
			if ($this->extendedset & 1) break;	// folder passwords don't include sub foders
		}
	}

	// kh_mod 0.3.0, changed
	function charfix($string, $trim=false, $html=false) {
		$string = trim($string);
		if ($trim) $string = trim($string, $trim);
		$string = str_replace("\t", "   ", $string);							// item delimiter in flat file database
		if (get_magic_quotes_gpc()) $string = stripslashes($string);	// eliminate magic quots 
		if ($html) $string = @htmlspecialchars($string, ENT_QUOTES);
		return $string;
	}

	// kh_mod 0.3.0, add
	function cleanLink($link='') {
		$search  = array('<','>','"',' ','\\');
		$replace = array('%3C','%3E','%22','%20','/');
		return str_replace($search, $replace, trim($link));
	}

	// kh_mod 0.3.0, add
	function cleanTarget($target='') {
		$target = trim($target);
		$regexp = '/^[_a-z][_a-z0-9]{0,64}$/i';
		return (preg_match($regexp, $target))?
				 $target
				 :
				 '_blank';
	}

	// kh_mod 0.3.1, add
	function setTextAlign($subject, $justify) {

		// SPLIT STRING ON TAGS WITH TEXT-ALIGN
		$tags = preg_split('/\<([^><]*text-align\s*:[^><]*)>?/i', $subject, -1, PREG_SPLIT_DELIM_CAPTURE);

		$regexp	= '/text-align\s*:\s*\w*(\s*;)?/i';
		$replace	= 'text-align:'. $justify .';';
		$result	= '';
		foreach($tags as $key=>$tag) {
			$result.= ($key % 2)?
						 sprintf('<%s>', preg_replace($regexp, $replace, rtrim($tag)))
						 :
						 $tag;
		}
		return ($this->string_empty($tags[0]))?		// no intro text or text begin with align tag
				 $result
				 :
				 sprintf('<div style="text-align:%s;">%s</div>', $justify, $result);
	}

	// kh_mod 0.3.0, add, 0.3.1a changed
	function br2line($item) {
		return preg_replace('/<br[^>]*>/i', ' ', $item);
	}

	// kh_mod 0.3.1a, add
	function br2nl($item) {
		return preg_replace("/<br[^>]*>/i", "\n", $item);
	}

	// kh_mod 0.3.0, add
	function string_empty($item) {
		return preg_match('/^(<[^>]*>|&nbsp;|\s)*$/i', $item) ? true:false;
	}

	// kh_mod 0.3.0, add
	function mb_shorten($item, $limit, $wildcard='...') {

		// NO CUTTING
		if ($limit === 0) return $item;

		// START AND END VALUES
		$chars  = abs($limit);
		$prefix = $suffix = '';
		$strip  = strip_tags($item);
		if ($limit > 0) {
			$start  = 0;
			$suffix = $wildcard;
		}
		else {
			$start  = $limit;
			$prefix = $wildcard;
		}
		$wildcard = strip_tags($wildcard);	// strip to calculate lenght

		// CUTTING STRING, kh_mod 0.3.1a changed
		if (extension_loaded('mbstring')							&&
			 ($lenght = @mb_strlen($strip, $this->charset))	&&
			 ($bonus  = @mb_strlen($wildcard)) !== false)
		{
			if ($lenght > $chars + $bonus) {
				$item = $prefix . mb_substr($strip, $start, $chars, $this->charset) . $suffix;
			}
		}
		elseif ((strcasecmp($this->charset, 'utf-8'))===0) {
			$bonus = strlen(utf8_decode($wildcard));
			$item_latin = utf8_decode($strip);
			if (strlen($item_latin) > $chars + $bonus) {
				$item = utf8_encode($prefix . substr($item_latin, $start, $chars) . $suffix);
			}
		}
		elseif (strlen($strip) > $chars + strlen($wildcard)) {
			$item = $prefix . substr($strip, $start, $chars) . $suffix;
		}
		return $item;
	}

	// kh_mod 0.2.0, add, 0.3.1 changed
	function displayCaptcha($width=250, $height=100, $size=22, $wave=2) {
		if		 ($width  > 500) $width  = 500;
		elseif ($width  < 100) $width  = 100;
		if		 ($height > 200) $height = 200;
		elseif ($height <  40) $height =  40;
		if		 ($size   >  36) $height =  36;
		elseif ($size   <  12) $height =  12;
		$_SESSION[GALLERY_ID]['captcha_width']  = $width;
		$_SESSION[GALLERY_ID]['captcha_height'] = $height;
		$_SESSION[GALLERY_ID]['captcha_size']   = $size;
		$_SESSION[GALLERY_ID]['captcha_wave']   = (int)$wave;
		return sprintf('<input type="image" src="%scaptcha/mg2_captcha.php?gID=%s&amp;rl=%d%s" style="width:%dpx;height:%dpx;border:0" alt="%7$s" title="%7$s" onclick="checkNoForm=true;" />',
						ADDON_FOLDER,
						GALLERY_ID,
						mt_rand(1000,9999),
						SID ? '&amp;'. SID:'',
						$width,
						$height,
						$this->lang['reload_captcha']
				 );
	}

	// kh_mod 0.1.0 rc1, add, 0.3.0 changed
	function checkLanguage($lang) {
		$lang_path = sprintf('%s%2$s/%2$s.%%s.php', LANG_FOLDER, $lang);
		$test_path = sprintf($lang_path, defined('USER_ADMIN')? 'admin':'gallery');
		return (empty($lang) || !is_readable($test_path))?
				 false
				 :
				 $lang_path;
	}

	// kh_mod 0.1.0, changed
	function output($var) {
		echo (isset($this->$var))? $this->$var:'';
	}

	// kh_mod 0.3.1, changed
	function log($entry='', $block=false) {
		$logfile  = DATA_FOLDER .'mg2_log.txt';
		$filesize = (is_file($logfile))? filesize($logfile):-1;

		// ENTRY DATE
		if (!$block) $entry = date('Ymd, H:i:s - ') . $entry . "\n";

		// SHORTEN LARGE LOG FILE
		if ($filesize > 307200 &&									// log file greater than 300 KB
			($content = file($logfile)))							// read file into array
		{
			$filesize = 0;												// write a new file head
			$content	 = array_slice($content, max(count($content) - 360, 0));
			$entry	 = implode('', $content) . $entry;
			unlink($logfile);
		}
		// CHECK FILE STATUS
		elseif (($filesize !== -1)			&&						// file exists
					!is_writeable($logfile)	&&						// but isn't writeable
					!chmod($logfile, 0666)	&&						// chmod don't work
				  ($content = file_get_contents($logfile)))	// but file is readable
		{
			$entry = $content . $entry;
			unlink($logfile);
		}

		// ADD LOG ENTRY
		if ($filesize > 0) {
			$fp = fopen($logfile, 'a+');
		}
		// CREATE NEW LOGFILE
		else {
			$head  = "MG2/kh_mod LFS (Log File System)\n\n";
			$head .= "Version: 0.5.1/$this->modversion\n";
			$head .= "Install date: " . date("Y-m-d",$this->installdate);
			$head .= "\n\n------------------ LOG BEGIN ------------------\n\n";
			$entry = $head . $entry;
			$fp = fopen($logfile, 'wb');
		}
		// WRITE NEW LOG FILE
		if (is_resource($fp)) {
			flock($fp, LOCK_EX);	// do an exclusive lock
			fwrite($fp, $entry);
			flock($fp, LOCK_UN);	// release the lock
			fclose($fp);
		}
	}

	//
	// BUILD PATH FOR IMAGE, MEDIUM, THUMB AND COMMENT
	// kh_mod 0.2.0, add
	function get_path($fname, $subdir, $type='image') {
		$ext = strrchr($fname, '.');
		$len = strlen($ext);
		if ($len > 1) {
			$path = $this->imagefolder . $subdir .'/';
			switch ($type) {
				case 'image':	return $path . $fname;
				case 'medium':	return $path . substr($fname, 0, -$len) .'_medium'. $ext;
				case 'thumb':	return $path . substr($fname, 0, -$len) .'_thumb'. $ext;
				case 'comment':return $path . $fname .'.comment';
			}
		}
		return false;
	}

	//
	// CONVERT DATE TO TIMESTAMP
	// kh_mod 0.1.0 b3, add, 0.3.1 changed
	function date2time($date='') {
		if (empty($date))	return time();

		// DATE FORMAT
		$dateForm  = $this->dateformat;

		// ALLOW DATE FORMATS
		$regexp1 = '/(%d|%e)([^%]*)(%b|%m|%n)([^%]*)(%y|%Y)/';  // DAY, MONTH, YEAR
		$regexp2 = '/(%b|%m|%n)([^%]*)(%d|%e)([^%]*)(%y|%Y)/';  // MONTH, DAY, YEAR
		$regexp3 = '/(%y|%Y)([^%]*)(%b|%m|%n)([^%]*)(%d|%e)/';  // YEAR, MONTH, DAY

		// CHECK DATE FORMAT AND SET NEW ORDER (YEAR, MONTH, DAY)
		if (preg_match($regexp1,$dateForm, $item)) {
			$res = array(3,2,1);
		}
		elseif (preg_match($regexp2,$dateForm, $item)) {
			$res = array(3,1,2);
		}
		elseif (preg_match($regexp3,$dateForm, $item)) {
			$res = array(1,2,3);
		}
		else return false;

		// DETERMINE DATE REGEXP
		$regexp = '';
		for ($n=1; $n<6; $n++) {
			switch ($item[$n]) {
				case ''  : $regexp.= '';					break;
				case '%y': 
				case '%Y': $regexp.= '(\d{4}|\d{2})';	break;
				case '%m':
				case '%n':
				case '%d':
				case '%e': $regexp.= '(\d{1,2})';		break;
				case '%b': $cClass = '[\w'.$this->lang['specialchars'].']';
							  $regexp.= '('.$cClass.'{3,15})';
							  break;
				default:   $regexp.= '\W*';	// no letter or digit or the underscore character
			}
		}
		// ADD TIME EXPRESSION
		$regexp = '/'. $regexp .'\D*((\d{1,2})\D+(\d{1,2})[^a-z]*((a|p)\W{0,3}m)?)?/i';

		// GET EACH DATE VALUE
		$timestamp = false;
		if (preg_match($regexp, $date, $split)) {
			$Y = (int)$split[$res[0]];							// YEAR
			$M = $split[$res[1]];								// MONTH
			$D = (int)$split[$res[2]];							// DAY
			$H = (isset($split[4]))? (int)$split[5]:0;	// HOUR
			$i = (isset($split[4]))? (int)$split[6]:0;	// MINUTE

			// AM/PM FORMAT?
			if (isset($split[7])) {
				$H+= (strcasecmp($split[7],'am') === 0)?
					  (($H > 11)? -12:0)		// AM
					  :
					  (($H < 12)?  12:0);	// PM
			}

			// MONTH TO NUMBER
			if (strlen($M) > 2) {
				for ($n=0; $n<12; $n++) {
					if (strncasecmp($M, $this->lang['months'][$n], 3)===0) break;
				}
				$M = ($n < 12)? ++$n : false;
			} else {
				settype($M, 'int');
			}

			// CHECK DATE
			if (checkdate($M, $D, $Y)) {
				if (($mk = mktime($H, $i, 0, $M, $D, $Y)) > 0) $timestamp = $mk;
			}
		}

		// RETURNS TIMESTAMP OR FALSE
		return $timestamp;
	}

	//
	// CONVERT TIMESTAMP TO DATE
	// kh_mod 0.3.0, changed
	function time2date($timestamp=0, $showTime=true) {
		if ((int)$timestamp < 0) return ' - ';

		// TIMESTAMP EMPTY?
		if (empty($timestamp)) $timestamp = time();

		// DATE FORMAT
		$dateForm = ($showTime && !empty($this->timeformat))?
						trim($this->dateformat .', '. $this->timeformat)
						:
						trim($this->dateformat);

		// SIMULATION OF MISSING PARAMETERS
		$search = $replace = array();
		// '%n' AS MONTH  WITHOUT LEADING ZERO
		$search[]  = '%n';
		$replace[] = date('n', $timestamp);
		// '%e' AS DAY WITHOUT LEADING ZERO
		$search[]  = '%e';
		$replace[] = date('j', $timestamp);
		// '%i' AS HOUR WITHOUT LEADING ZERO
		$search[]  = '%i';
		$replace[] = date('g', $timestamp);
		// '%b' AS THREE CHARS OF MONTH (LOCALISED), kh_mod 0.3.1, changed
		$month = (int)date('n', $timestamp) - 1;
		if (!empty($this->lang['months'][$month])) {
			$search[]  = '%b';
			$replace[] = $this->lang['months'][$month];
		}
		// '%r' DISPLAY a.m resp. p.m. STRING
		$search[]  = '%r';
		$replace[] = strtr(date('a', $timestamp), array('am'=>'a.m.','pm'=>'p.m.'));

		// REPLACE
		$dateForm  = str_replace($search,$replace,$dateForm);

		// DATE AND TIME FORMTTED
		return strftime($dateForm, $timestamp);
	}

	//
	// CONVERT A EXIF DATE TO MG2 DATE
	// kh_mod 0.3.0, add
	function exif2date($date='', $nomatch='&ndash;') {
		$regexp = '/(\d{4})\D*(\d{2})\D*(\d{2})\D+(\d{1,2})?\D+(\d{2})?\D+(\d{2})?/';
		return (preg_match($regexp, $date, $d))?
				 $this->time2date(mktime((int)$d[4], (int)$d[5], (int)$d[6], $d[2], $d[3], $d[1]), true)
				 :
				 $nomatch;
	}
}	// END CLASS' mg2db'
?>
