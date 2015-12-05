<tr>
    <td colspan="6" style="background-color: #567081; height: 2px">
    </td>
</tr>
<tr>
    <td><?php echo FlattenText($quotalisting['name']);?></td>
    <td>
        <?php if ($quotalisting['active'] == 1)
            {
                echo '<font color="#48B150">'.gT("Active").'</font>';
            } else {
                echo '<font color="#B73838">'.gT("Not Active").'</font>';
            }
        ?>
    </td>
    <td>
        <?php if ($quotalisting['action'] == 1) {
                eT("Terminate survey");
            } elseif ($quotalisting['action'] == 2) {
                eT("Terminate survey with warning");
        } ?>
    </td>
    <td <?php echo $highlight;?>><?php echo is_null($completed) ? gT("N/A"): $completed ;?></td>
    <td><?php echo $quotalisting['qlimit'];?></td>
    <td style="padding: 3px;">
        <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'quotas','update')) { ?>
            <?php echo CHtml::form(array("admin/quotas/sa/editquota/surveyid/{$iSurveyId}"), 'post'); ?>
                <input name="submit" type="submit" class="submit" value="<?php eT("Edit");?>" />
                <input type="hidden" name="sid" value="<?php echo $iSurveyId;?>" />
                <input type="hidden" name="action" value="quotas" />
                <input type="hidden" name="quota_id" value="<?php echo $quotalisting['id'];?>" />
                <input type="hidden" name="subaction" value="quota_editquota" />
            </form>
        <?php } ?>
        <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'quotas','delete')) { ?>
            <?php echo CHtml::form(array("admin/quotas/sa/delquota/surveyid/{$iSurveyId}"), 'post'); ?>
                <input name="submit" type="submit" class="submit" value="<?php eT("Remove");?>" />
                <input type="hidden" name="sid" value="<?php echo $iSurveyId;?>" />
                <input type="hidden" name="action" value="quotas" />
                <input type="hidden" name="quota_id" value="<?php echo $quotalisting['id'];?>" />
                <input type="hidden" name="subaction" value="quota_delquota" />
            </form>
        <?php } ?>
        <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'quotas','update')) {
            echo CHtml::link(gT("Validate"),array('admin/validate','sa'=>'quota','sid'=>$surveyid,'quota'=>$quotalisting['id']),array('class'=>'btn btn-link','title'=>sprintf(gT("Validation of quota %s"),$quotalisting['name']),"target"=>"dialog"));
        } ?>
    </td></tr>

<tr class="evenrow">
    <td>&nbsp;</td>
    <td><strong><?php eT("Questions");?></strong></td>
    <td><strong><?php eT("Answers");?></strong></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td style="padding: 3px;">
        <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'quotas','update')) { ?>
            <?php echo CHtml::form(array("admin/quotas/sa/new_answer/surveyid/{$iSurveyId}"), 'post'); ?>
                <input name="submit" type="submit" class="quota_new" value="<?php eT("Add answer");?>" />
                <input type="hidden" name="sid" value="<?php echo $iSurveyId;?>" />
                <input type="hidden" name="action" value="quotas" />
                <input type="hidden" name="quota_id" value="<?php echo $quotalisting['id'];?>" />
                <input type="hidden" name="subaction" value="new_answer" />
            </form>
            <?php } ?>
    </td>
</tr>
