<?php
	printf('<div class="pagenav">%s</div>', $mg2->pagenavigation($folderID, $numPages, $currentPage));
	if ($folderID == 1)
		echo '
			<div style="text-align: center;margin:12px">
				<a href="admin.php"><img src="skins/'.$mg2->activeskin.'/images/key.gif" width="13" height="7" alt="" border="0" /></a>
			</div>
		';
?>
