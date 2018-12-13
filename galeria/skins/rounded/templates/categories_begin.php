<?php
	printf('<div align="%s"><div style="width:%s">',
		$mg2->direction,
		$mg2->dispwidth
	);
	printf('<div class="introtext">%s</div>',
		$mg2->string_empty($mg2->introtext) ? '&nbsp;':$mg2->introtext
	);
	printf('<div class="pagenav">%s</div>',
		$mg2->pagenavigation($folderID, $numPages, $currentPage)
	);
?>
<table class="category" cellspacing="0" cellpadding="0">
<tr>
	<td class="head" colspan="<?php echo $categorycols;?>"><?php $mg2->output('category');?></td>
</tr><tr>
	<td class="content">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
