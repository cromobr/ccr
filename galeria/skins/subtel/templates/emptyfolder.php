<div align="center">
	<br />
<?php
	if (!$mg2->string_empty($mg2->introtext))
		printf('<div class="introtext"%s>%s</div>',
			(int)$mg2->introwidth ? ' style="width:'.$mg2->introwidth.'"':'',
			$mg2->introtext
		);
?> 
	<br />
	<div style="white-space:nowrap;font-weight:bold;">
		<?php echo $mg2->lang['folderempty'];?>
	</div>
</div>
