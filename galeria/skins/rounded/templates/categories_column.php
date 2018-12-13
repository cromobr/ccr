<td class="column" valign="top" width="<?php echo $colswidth;?>%">
<?php
	// NOTE FOR FOLDER NAME
	$foldernote = '&nbsp;<span class="small" title="%1$s '. $mg2->lang['images'] .'">(%1$s)</span>';
	foreach ($column_content as $category) {
		// DISPLAY FOLDER NAME
		printf('<div class="main"><a href="%s" target="%s">%s</a>%s</div>',
			$category['main']['link'],
			$category['main']['target'],
			$category['main']['item'],
			($category['main']['num'] === -1)? '':sprintf($foldernote, $category['main']['num'])
		);
		// DISPLAY ICON
		if (isset($category['main']['icon'])) {
			printf('<div class="icon"><a href="%s" target="%s"><img src="%s" %s border="0" alt="" /></a></div>',
				$category['main']['link'],
				$category['main']['target'],
				$category['main']['icon'],
				$category['main']['size']
			);
		}
		// DISPLAY DESCRIPTION
		if (isset($category['main']['desc'])) {
			printf('<div class="desc">%s</div>', $category['main']['desc']);
		}
		// DISPLAY SUB FOLDERS
		foreach ($category['sub'] as $subcat) {
			printf('<div class="desc"><a href="%s" target="%s">%s</a></div>',
				$subcat['link'],
				$subcat['target'],
				$subcat['item']
			);
		}
	}
?>
</td>
