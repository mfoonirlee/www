<tr class="evenrow">
	<td align="center">&nbsp;</td>
	<td align="center"><?php echo @$question_answers[$quota_questions['code']]['Title'];?></td>
	<td align="center"><?php echo @$question_answers[$quota_questions['code']]['Display'];?></td>
	<td align="center">&nbsp;</td>
	<td align="center">&nbsp;</td>
	<td style="padding: 3px;" align="center">
    <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'quotas','update')) { ?>
        <?php echo CHtml::form(array("admin/quotas/sa/delans/surveyid/{$iSurveyId}"), 'post'); ?>
			<input name="submit" type="submit" class="submit" value="<?php eT("Remove");?>" />
			<input type="hidden" name="sid" value="<?php echo $iSurveyId;?>" />
			<input type="hidden" name="action" value="quotas" />
			<input type="hidden" name="quota_member_id" value="<?php echo $quota_questions['id'];?>" />
			<input type="hidden" name="quota_qid" value="<?php echo $quota_questions['qid'];?>" />
			<input type="hidden" name="quota_anscode" value="<?php echo $quota_questions['code'];?>" />
			<input type="hidden" name="subaction" value="quota_delans" />
        <?php } ?>
		</form>
	</td>
</tr>
