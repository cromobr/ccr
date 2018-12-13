<?php
///////////////////////////////////////
//		CATEGORIES VIEW		 //
///////////////////////////////////////

	// CALCULATE CATEGORY COLS AND ROWS
	$categorycols =  min($imagecols, $cfolders);
	$categoryrows = ceil($cfolders/$categorycols);

	// DISPLAY CATEGORY HEADER
	$mg2->dispwidth  =  '90%';		// display overall width
	$mg2->tablewidth = '100%';		// display item thumbnails
	$mg2->direction  = 'center';	// left || center || right
	$mg2->category	  = ($mg2->all_folders[$folderID][2])?
							 $mg2->br2line($mg2->all_folders[$folderID][2])
							 :
							 $mg2->lang['gallery'];
	include_once('skins/'.$mg2->activeskin.'/templates/categories_begin.php');

	// COUNT PICTURES INCL. ALL SUB FOLDERS
	$subfolders = $mg2->getValidSubfolders($folderID);		// get all sub folders
	$n_pictures	= $mg2->countFolderPictures($subfolders);	// count all pictures

	// DISPLAY SUB FOLDERS IN CATEGORIES
	$colswidth = floor(100/$categorycols);
	for ($col=0; $col < $categorycols; $col++) {

		// CREATE CATEGORY COLUMN
		$column_content = array();	
		for ($row=0; $row < $categoryrows; $row++) {
			$i = $row * $categorycols + $col; if (!isset($folders[$i])) continue;
			// $i = $col * $categorycols + $row;

			// GET MAIN CATEGORY
			$subfID = (int)$folders[$i][0];
			if (isset($folders[$i][11]) && (int)$folders[$i][11] === 1) {	// folder type: link?
				$num	  = -1;
				$link	  = $mg2->cleanLink($folders[$i][9]);
				$target = $mg2->cleanTarget($folders[$i][10]);
			} else {
				$num	  = $n_pictures[$subfID];
				$link	  = $mg2->getGalleryLink(array('fID'=>$subfID));
				$target = '_self';
			}
			$item = ($folders[$i][2])? $folders[$i][2] : '#ID '. $subfID;
			$maincat = array(
							'num'		=> $num,	 	// num of images
							'link'	=> $link,	// link to folder
							'target'	=> $target,	// target for link
							'item'	=> $item		// folder name
						  );
			// FOLDER ICON
			if ($mg2->foldersetting & 2048) {
				$maincat['icon'] = $mg2->getFolderIcon($subfID);
				$maincat['size'] = sprintf('height="%d" width="%d"', $mg2->height, $mg2->width);
			}
			// FOLDER DESCRIPTION
			$desc_display = $mg2->foldersetting >> 13 & 3;
			if ($desc_display && !$mg2->string_empty($folders[$i][3])) {
				// FORCE JUSTIFY
				if		 ($desc_display === 3)
					$maincat['desc'] = $mg2->setTextAlign($folders[$i][3], 'justify');
				// FORCE ALIGN LEFT
				elseif ($desc_display === 2)
					$maincat['desc'] = $mg2->setTextAlign($folders[$i][3], 'left');
				// ORIGINAL ALIGN
				else
					$maincat['desc'] = &$folders[$i][3];
			}

			// GET SUB CATEGORIES
			$subcats	= array();
			if ($mg2->foldersetting & 32768) {
				$subfolders = $mg2->select($subfID, $mg2->all_folders, 1, $mg2->getFolderSortMode($subfID));
				for ($j=0; $j < count($subfolders); $j++) {
					if (isset($folders[$i][11]) && (int)$folders[$i][11] === 1) { // folder type: link?
						$link	  = $mg2->cleanlink($subfolders[$j][9]);
						$target = $mg2->cleanTarget($folders[$i][10]);
					} else {
						$link	  = $mg2->getGalleryLink(array('fID'=>$subfolders[$j][0]));
						$target = '_self';
					}
					$subcats[] = array (
										'link'	=> $link,
										'target'	=> $target,
										'item'	=> ($subfolders[$j][2])? $mg2->br2line($subfolders[$j][2])
																					 :
																					 '#ID '. (int)$subfolders[$j][0]
									 );
				}
			}
			$column_content[] = array('main'=>$maincat, 'sub'=>$subcats);
		}

		// OUTPUT COLUMN CONTENT
		if ($col + 1 === $categorycols) $colswidth += 100 % $categorycols;
		include('skins/'.$mg2->activeskin.'/templates/categories_column.php');
	}

	// END CATEGORY TABLE
	echo '
				</tr></table>
			</td>
		</tr></table>
		</div></div>
	';

	// FOLDER CONTENT NO IMAGES?
	if (($last - $first) < 1) return true;
?>
	<div align="<?php $mg2->output('direction');?>">
	<div style="width:<?php $mg2->output('dispwidth');?>" align="left">
	<table class="thumbnails" cellspacing="0" cellpadding="0" width="<?php $mg2->output('tablewidth');?>">
	<tr>
<?php

	// START INDEX COLUMNS
	$col_idx = 0;

// ***************************** Ab hier unverändert zu 'mg2_thumbnails.php' ***************************** //

	// IS THERE THE CLICK OR/AND COMMENT COUNTER ACTIVE?
	if ($mg2->foldersetting & 384) $Counter = new MG2Counter($mg2->sqldatabase);

	// DISPLAY THUMBS
	$commentsCounted = false;
	$mg2->tooltip	  = '';
	for ($i=$first; $i < $last; $i++) {
		$imageID		 = (int)$images[$i][0];
		$mg2->link	 = $mg2->getGalleryLink(array('iID'=>$imageID));
		$mg2->title	 = ($mg2->foldersetting & 32)? trim($images[$i][2]):'';

		// GET CONTENT TYPE
		$contentType = $mg2->getContentType($imageID);

		// GET MEDIA ICON
		list($thumbID,
			  $mg2->thumb_file,
			  $mg2->thumb_width,
			  $mg2->thumb_height) = $mg2->getFileIcon($imageID, $contentType, true);

		// GET NUMBER OF CLICKS
		if ($mg2->foldersetting & 128)
			$numberClicks = $Counter->getNumClicks($imageID);

		// GET NUMBER OF COMMENTS
		if ($mg2->foldersetting & 256)
		if (($numberComments = $Counter->getNumComments($imageID)) === -1) {

			// COUNT NUMBER OF COMMENTS
			$Comments = $mg2->getInstance('MG2Comments');
			if (($numberComments = $mg2->$Comments->getNumComments($imageID)) !== false) {
				$Counter->setDatabase($imageID, -1, $numberComments, false);
				$commentsCounted = true;
			}
			else {
				$numberComments = 0;
			}
		}

		// USE HEADLINE FOR 'alt' AND 'title' ATTRIBUT
		if ($mg2->foldersetting & 512)
			$mg2->tooltip = htmlspecialchars(($mg2->layoutsetting & 8)?	// filename as headline?
								 $mg2->getFilename($imageID)
								 :
								 strip_tags($mg2->br2line($images[$i][2])));

		// DISPLAY IMAGE TITLE UNDER THUMBNAIL
		if ($mg2->title) {
			// RELATIVE TITLE LIMIT?
			$titlelimit = ($skin_titlelimit < 0)?
							  abs(round(($mg2->thumbMaxWidth/$skin_titlelimit),0))
							  :
							  (int)$skin_titlelimit;
			// SHORT TITLE
			$mg2->title = '<div>'. $mg2->mb_shorten($mg2->title, $titlelimit) .'</div>';
		}
		// DISPLAY FILE NAME UNDER THUMBNAIL, kh_mod 0.1.0, add, 0.3.1 changed
		if ($mg2->foldersetting & 64)	{
			$mg2->title.= '<div>'. $mg2->getFilename($imageID) .'</div>';
		}

		// DISTANCE BETWEEN IMAGE AND IMAGE TITEL
		$mg2->distance = max($mg2->thumbMaxHeight + 20 - $mg2->thumb_height, 1);

		// MARK NEW IMAGE
		$mg2->new = ((time() - (int)$images[$i][4]) < ($mg2->marknew * 84600))?
						true
						:
						false;

		// DISPLAY IMAGE THUMB
		include('skins/'.$mg2->activeskin.'/templates/thumbnail.php');

		// NEW TABLE ROW?
		$col_idx = ($i % $imagecols) + 1;
		if ($col_idx === $imagecols && ($i+1) < $last) echo '</tr><tr>';
	}

	// COMMENTS COUNTED THEN UPDATE COUNTER ENTRIES
	if ($commentsCounted) $Counter->updateRecords();

	// IF MORE THEN ONE ROW AND LAST ROW INCOMPLET
	if (($last - $first) > $imagecols && $imagecols > $col_idx) {
		echo str_repeat("<td>&nbsp;</td>\n", $imagecols - $col_idx);
	}

	// END THUMBNAIL TABLE
	echo '
		</tr></table>
		</div></div>
	';
?>