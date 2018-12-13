<table class="table_actions" cellpadding="0" cellspacing="0">
<form name="commentform" action="<?php echo ADMIN_INDEX;?>" method="post">
<input type="hidden" name="action" value="admincomments" />
<input type="hidden" name="editID" value="<?php echo $editID;?>" />
<input type="hidden" name="displayed" value="<?php echo $numComments;?>" />
<tr>
  <td class="headline" width="30">&nbsp;</td>
  <td class="headline" width="120" align="center"><?php echo $this->lang['posted'];?></td>
  <td class="headline" width="150"><?php echo $this->lang['from'];?></td>
  <td class="headline"><?php echo $this->lang['comment'];?></td>
  <td class="headline" width="40" align="center"><?php echo $this->lang['edit'];?></td>
  <td class="headline" width="40" align="center"><?php echo $this->lang['delete'];?></td>
</tr>
<?php
$num = 0;
foreach ($commentItems as $comment) {
	// NEW COMMENT LOCKED?
	if ((int)$comment[7] === 0) {
		echo '<tr bgcolor="#EEDDBB" title="'.$this->lang['newcommentlocked'].', #'.$comment[0].'">';
	}
	// COMMENT LOCKED?
	elseif ((int)$comment[7] < 1) {
		echo '<tr bgcolor="#FFCFCF" title="'.$this->lang['commentlocked'].', #'.$comment[0].'">';
	}
	// COMMENT UPDATED?
	elseif ($updatedComment === (int)$comment[0]) {
		echo '<tr bgcolor="#DBFFF0" title="'.$this->lang['commentupdated'].', #'.$comment[0].'">';
	}
	// NO MARKED
	else {
		echo '<tr>';
	}

	// DATE AND TIME
	$commentDate = $this->time2date($comment[4], false);
	$commentTime = (empty($this->timeformat))?
						''
						:
						$this->time2date($comment[4]);

	// REDUCE NAME TO MAX 32 CHARS
	$from = sprintf('<span style="white-space:nowrap;">%s</span>', $this->mb_shorten($comment[1], 32));

	// REDUCE COMMENT TO 90 CHARS
	$suffix = sprintf('<a href="%s?iID=%d&editComment=%d">...</a>',
					 ADMIN_INDEX,
					 $editID,
					 $comment[0]
				 );
	$body   = preg_replace('/<br[^>]*>/','&#182; ', $comment[3]);
	$body	  = strip_tags($body);
	$body   = $this->mb_shorten($body, 90, $suffix);
?>
	<td class="td_div" width="30"><input type="checkbox" name="comment<?php echo $num++;?>" value="<?php echo $comment[0];?>" /></td>
	<td class="td_files" align="center"><span title="<?php echo $commentTime;?>"><?php echo $commentDate;?></span></td>
	<td class="td_files"><a href="mailto:<?php echo $comment[2];?>" title="<?php echo $comment[2];?>"><?php echo $from;?></a></td>
	<td class="td_files"><?php echo $body;?></td>
	<td class="td_div"><a href="<?php echo ADMIN_INDEX;?>?iID=<?php echo $editID;?>&editComment=<?php echo $comment[0];?>" title="<?php echo $this->lang['edit'];?>">
		<img src="<?php echo ADMIN_IMAGES;?>edit.gif" width="24" height="24" alt="<?php echo $this->lang['edit'];?>" title="<?php echo $this->lang['edit'];?>" border="0" /></a>
	</td>
	<td class="td_div"><a href="<?php echo ADMIN_INDEX;?>?iID=<?php echo $editID;?>&askDelComment=<?php echo $comment[0];?>" title="<?php echo $this->lang['delete'];?>">
		<img src="<?php echo ADMIN_IMAGES;?>delete.gif" width="24" height="24" alt="<?php echo $this->lang['delete'];?>" border="0" /></a>
	</td>
</tr>
<?php } ?>
<tr>
	<td class="td_div" width="30">
		<img src="<?php echo ADMIN_IMAGES;?>checkbox_on.gif" width="13" height="13" alt="<? echo $this->lang['checkall'];?>" title="<? echo $this->lang['checkall'];?>" onclick="checkAll(<?php echo $numComments;?>,'comm')" />
		<img src="<?php echo ADMIN_IMAGES;?>checkbox_off.gif" width="13" height="13" alt="<? echo $this->lang['uncheckall'];?>" title="<? echo $this->lang['uncheckall'];?>" onclick="uncheckAll(<?php echo $numComments;?>,'comm')" />
	</td>
	<td class="td_files" colspan="5">
<?php
	// LOCK COMMENT BUTTON
	printf('<input type="submit" name="lock" value="%s" class="adminbutton" alt="%2$s" title="%2$s" onclick="return confirmSubmit(%3$d,\'comm\',\'lock\')" />'."\n",
		$this->lang['lock'],
		$this->lang['ok'],
		$numComments
	);
	// UNLOCK COMMENT BUTTON
	printf('<input type="submit" name="unlock" value="%s" class="adminbutton" alt="%2$s" title="%2$s" onclick="return confirmSubmit(%3$d,\'comm\',\'unlock\')" />'."\n",
		$this->lang['unlock'],
		$this->lang['ok'],
		$numComments
	);
	// DELETE COMMENT BUTTON
	printf('<input type="submit" name="delete" value="%s" class="adminbutton" alt="%2$s" title="%2$s" onclick="return confirmSubmit(%3$d,\'comm\',\'delete\')" />'."\n",
		$this->lang['buttondelete'],
		$this->lang['ok'],
		$numComments
	);
?>
	</td>
</tr>
</form>
</table>