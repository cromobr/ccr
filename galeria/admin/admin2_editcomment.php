<?php
	// NEW COMMENT LOCKED?
	if ((int)$comment['lock'] === 0) {
		$marker = ' bgcolor="#EEDDBB" title="'.$this->mg2->lang['newcommentlocked'].', #'.$commentID.'"';
	}
	// COMMENT LOCKED?
	elseif ((int)$comment['lock'] < 1) {
		$marker = ' bgcolor="#FFCFCF" title="'.$this->mg2->lang['commentlocked'].', #'.$commentID.'"';
	}
	// NO MARKED
	else {
		$marker = '';
	}
?>
<table class="table_files" cellpadding="0" cellspacing="0">
<tr<?php echo $marker;?>>
	<td class="td_files">&nbsp;
		<?php printf('%s: %s : <a href="'. ADMIN_INDEX .'?editID=%d">%s</a> : %d %s',
					$this->mg2->lang['navigation'],
					$this->mg2->adminnavigation($folderID),
					$itemID,
					$filename,
					$numComments,
					$numComments === 1 ? $this->mg2->lang['comment']:$this->mg2->lang['comments']
				);
		?> 
	</td>
</tr>
</table>
<table class="table_actions" cellpadding="0" cellspacing="0">
<form action="<?php echo ADMIN_INDEX;?>" method="post">
<tr><td  class="headline" colspan="5"><?php echo $this->mg2->lang['editcomment'];?><td></tr>
<tr valign="top">
	<td rowspan="4" class="td_actions" width="160" align="center">
		<div style="margin-top:6em"><h3><?php echo $this->mg2->lang['edit'];?></h3></div>
	</td>
	<td rowspan="4" class="td_actions_bottom" width="5">&nbsp;</td>
	<td class="td_actions_right" width="120" style="padding-top:6pt; padding-bottom:6pt">
		<?php echo $this->mg2->lang['posted'];?>
	</td>
	<td class="td_actions_noborder" style="padding-top:6pt; padding-bottom:6pt">
		<?php
			printf(((int)$comment['edit'] > 0)?
				'&nbsp;%s&nbsp;&nbsp;(%s %d, %s %s)':'&nbsp;%s&nbsp;&nbsp;(%s %d)',
				$comment['date'],
				$this->mg2->lang['changes'],
				$comment['edit'],
				$this->mg2->lang['lastchange'],
				$comment['last']
			);
		?> 
	</td>
	<td rowspan="4" class="td_actions">
	<!--
		<div style="margin: 5px 0 0 2px">
			<input type="radio" name="access" id="unlock" value="1" style="vertical-align:middle;" />
			<label for="unlock" style="vertical-align:middle;"><?php echo $this->mg2->lang['unlock'];?></label>
		</div>
		<div style="margin: 5px 0 0 2px">
			<input type="radio" name="access" id="lock" value="-1" style="vertical-align:middle;" />
			<label for="lock" style="vertical-align:middle;"><?php echo $this->mg2->lang['lock'];?></label>
		</div>
	-->
	<td>
</tr>
<tr>
	<td class="td_actions_right" width="120"><?php echo $this->mg2->lang['name'];?></td>
	<td class="td_actions_noborder">
		<input type="text" name="name" size="45" value="<?php echo $comment['name'];?>" class="admintext" />
	</td>
</tr>
<tr>
	<td class="td_actions_right"><?php echo $this->mg2->lang['email'];?></td>
	<td class="td_actions_noborder">
		<input type="text" name="email" size="45" value="<?php echo $comment['email'];?>" class="admintext" />
	</td>
</tr>
<tr>
	<td class="td_actions"><?php echo $this->mg2->lang['comment'];?></td>
	<td class="td_actions_bottom"><table class="wysiwyg_editor" width="554"><tr><td>
		<textarea id="editor" name="body" cols="78" rows="10" class="admindropdown" style="padding:2px;width:550px;height:200px;"><?php echo $comment['body'];?></textarea>
	</td></tr></table></td>
</tr>
<tr>
	<td class="td_actions" colspan="5" align="center">
		<input type="hidden" name="submit" value="1" />
		<input type="hidden" name="editID" value="<?php echo $itemID;?>" />
		<input type="hidden" name="updateComment" value="<?php echo $commentID;?>" />
<?php
		// CANCEL BUTTON
		printf("\n".'<a href="%s?editID=%d"><img src="%scancel.gif" width="24" height="24" alt="%4$s" title="%4$s" class="adminpicbutton" /></a>',
			ADMIN_INDEX,
			$itemID,
			ADMIN_IMAGES,
			$this->mg2->lang['cancel']
		);
		// OK BUTTON
		printf("\n".'<input type="image" src="%sok.gif" alt="%2$s" title="%2$s" class="adminpicbutton" />',
			ADMIN_IMAGES,
			$this->mg2->lang['ok']
		);
?> 
	</td>
</tr>
</form>
</table>