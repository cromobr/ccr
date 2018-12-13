<?php
// VIEW IMAGE COMMENTS
echo '
	<table cellspacing="0" class="table-comments" align="center">
	<tr>
		<td class="comment-headline">
			'.	$mg2->lang['comments'] .' ('. $numberComments .')
		</td>
	</tr>
';
foreach ($mediumComments as $comment) {
	$postdate = $mg2->time2date($comment[4],true);
	$postname = ($mg2->commentsets & 4)?
					'<a href="mailto:'. $comment[2] .'">'. $comment[1] .'</a>'
					:
					'<i>'. $comment[1] .'</i>';
	echo '
		<tr>
			<td class="comment-aboveline">
				'. $postdate .' '. $mg2->lang['by'] .' '. $postname .'
			</td>
		</tr>
		<tr><td class="comment-belowline">'. $comment[3] .'</td></tr>
	';
}
echo '</table>';
?>
