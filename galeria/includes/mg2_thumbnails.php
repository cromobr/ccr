<?php
//////////////////////////////////////
//		THUMBNAIL VIEW		//
//////////////////////////////////////

	// DISPLAY THUMBNAIL HEADER
	$mg2->direction  = 'center';	// left || center || right
	$mg2->tablewidth = '1';			// center and 90% means 'justify'
	include_once('skins/'.$mg2->activeskin.'/templates/viewthumbs_begin.php');

	// START INDEX COLUMNS
	$col_idx = 0;

	// DISPLAY FOLDERS
	$upto = min($cfolders, $last);
	for ($i=$first; $i < $upto; $i++) {
		if (isset($folders[$i][11]) && (int)$folders[$i][11] === 1) {	// folder type: link?
			$mg2->link	 = $mg2->cleanLink($folders[$i][9]);
			$mg2->target = $mg2->cleanTarget($folders[$i][10]);
		} else {
			$mg2->link	 = $mg2->getGalleryLink(array('fID'=>$folders[$i][0]));
			$mg2->target = '_self';
		}
		$mg2->thumbfile  = $mg2->getFolderIcon($folders[$i][0]);
		$mg2->foldername = ($folders[$i][2])? $folders[$i][2]:'&nbsp;';

		// DISTANCE BETWEEN FOLDER ICON AND FOLDER NAME
		$mg2->distance = max($mg2->thumbMaxHeight + 20 - $mg2->height, 1);

		// MARK NEW FOLDER
		$mg2->new = ((time() - (int)$folders[$i][4]) < ($mg2->marknew * 84600))?
						true
						:
						false;

		// DISPLAY SUB FOLDER THUMB
		include('skins/'.$mg2->activeskin.'/templates/subfolder.php');

		// NEW TABLE ROW?
		$col_idx = ($i % $imagecols) + 1;
		if ($col_idx === $imagecols && ($i+1) < $last) echo '</tr><tr>';
	}

	// THERE ARE IMAGES AND CLICK OR/AND COMMENT COUNTER ACTIVE?
	if (($mg2->foldersetting & 384) && $last > $i) {
		$Counter = new MG2Counter($mg2->sqldatabase);
	}

	// CALCULATE THUMB START AND END INDEX
	$first = $i    - $cfolders;
	$upto  = $last - $cfolders;

	// DISPLAY THUMBS
	$commentsCounted = false;
	$mg2->tooltip	  = '';
	for ($i=$first; $i < $upto; $i++) {
		$imageID		 = (int)$images[$i][0];
		$mg2->link	 = $mg2->getGalleryLink(array('iID'=>$imageID));
		$mg2->title	 = ($mg2->foldersetting & 32)? trim($images[$i][2]):'';

		// GET MEDIUM ICON
		list(	$thumbID,
				$mg2->thumb_file,
				$mg2->thumb_width,
				$mg2->thumb_height ) = $mg2->getFileIcon($imageID, null, true);

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
			// USE FILE NAME AS HEADLINE?
			$mg2->tooltip = htmlspecialchars(($mg2->layoutsetting & 8)?
								 $mg2->getFilename($imageID)
								 :
								 strip_tags($mg2->br2line($images[$i][2])));

		// DISPLAY IMAGE TITLE UNDER THUMBNAIL, kh_mod 0.3.0, add
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
		$col_idx = (($i+$cfolders) % $imagecols) + 1;
		if ($col_idx === $imagecols && ($i+1) < $upto) echo '</tr><tr>';
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