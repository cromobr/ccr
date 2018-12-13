<?php
if (!empty($_SESSION[GALLERY_ID]['folderpwd'])) {
	printf('<div class="credits"><a href="%s?action=logout">%s</a></div>'."\n",
					$mg2->getGalleryLink(),
					$mg2->lang['logout']
	);
} elseif (!empty($_SESSION[GALLERY_ID]['adminmode'])) {
	printf('<div class="credits"><a href="%s?user=1">%s: %s</a></div>'."\n",
		$mg2->getGalleryLink(),
		$mg2->lang['logoff'],
		$mg2->lang['adminmode']
	);
} elseif (!empty($logoutmsg)) {
	printf('<div class="credits">%s</div>'."\n", $mg2->lang['logoutok']);
}
?>
