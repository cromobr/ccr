<div  align="<?php $mg2->output('direction');?>"><div>
<?php
	printf('<div class="introtext"%s>%s</div>',
		(int)$mg2->introwidth ? ' style="width:'.$mg2->introwidth.'"':'',
		$mg2->string_empty($mg2->introtext) ? '&nbsp;':$mg2->introtext
	);
	printf('<div class="pagenav">%s</div>',
		$mg2->pagenavigation($folderID, $numPages, $currentPage)
	);
?>
<table class="thumbnails" cellspacing="0" cellpadding="0" width="<?php $mg2->output('tablewidth');?>">
<tr>
