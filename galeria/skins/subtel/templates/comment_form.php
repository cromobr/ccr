<?php
// HIDE COMMENT FORM?
$addCommLink = $mg2->getGalleryLink(array('iID'=>$imageID, 'showform'=>1, 'rl'=>mt_rand(100,999)));
if (($mg2->commentsets & 16) && $hideCommForm) {
	echo '
		<div class="comment-headline">
			<a href="'. $addCommLink .'#addcomment">'. $mg2->lang['addcomment'] .'</a>
		</div>
	';
}
// SHOW COMMENT FORM
else {
	$mg2->jsformvalid();	// JavaScript validation functions
	echo '
	<a name="addcomment" />
	<form name="commentform" action="'. $mg2->getGalleryLink() .'" method="post" onsubmit="return validateCompleteForm(this,\'error\')">
	<input type="hidden" name="showform" value="1" />
	<input type="hidden" name="action" value="addcomment" />
	<input type="hidden" name="iID" value="'. $imageID .'" />
	<input type="hidden" name="verify" value="'. $verify .'" />
	<table cellspacing="0" class="table-comments" align="center">
	<tr>
		<td class="comment-headline">
			'. $mg2->lang['addcomment'] .'
		</td>
	</tr>
	<tr>
		<td>
			<div>'. $mg2->lang['comment'] .'</div>
			<div><textarea cols="65" rows="3" name="body" id="body" class="comment-textfield">'. $workon[3] .'</textarea></div>
		</td>
	</tr>
	<tr>
		<td>
			<div>'. $mg2->lang['name'] .'</div>
			<div><input type="text" size="70" maxlength="90" name="name" id="name" value="'. $workon[1] .'" class="comment-textfield" /></div>
		</td>
	</tr>
	<tr>
		<td>
			<div>'. $mg2->lang['email'] .'</div>
			<div><input type="text" size="70" maxlength="90" name="email" id="email" value="'. $workon[2] .'" class="comment-textfield" /></div>
		</td>
	</tr>
	';

	// VIEW IMAGE CAPTCHA
	if ($mg2->commentsets & 256) {
		echo '
			<tr>
				<td>
					<div>'. $mg2->lang['captcha'] .'</div>
					<div><input type="text" name="captcha" id="captcha" value="" class="comment-textfield" style="width:'.($captcha_width-8).'px" /></div>
				</td>
			</tr>
			<tr>
				<td>'.
					// PARAMETER FROM 'settings.php'
					$mg2->displayCaptcha($captcha_width,
												$captcha_height,
												$captcha_font_size,
												$captcha_font_wave
					)
				.'</td>
			</tr>
		';
	}

	// SUBMIT-BUTTON
	echo '
	<tr>
		<td>
			<input type="submit" name="submit" value="'. $mg2->lang['addcomment'] .'" class="comment-button" />
		</td>
	</tr>
	</table>
	</form>
	';
}
?>
