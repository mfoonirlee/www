<script src="<?php echo Yii::app()->getConfig('adminscripts') . "userControl.js" ?>" type="text/javascript"></script>
<div class='header ui-widget-header'>
    <strong>
        <?php
        eT("Global participant settings");
        ?>
    </strong>
</div>
<div id='tabs'>
    <ul>
        <li>
            <a href='#usercontrol'><?php
        eT("User control");
        ?></a>
        </li>
    </ul>
    <div id='usercontrol-1'>
        <?php
        if (Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $attribute = array('class' => 'form44');
            echo CHtml::beginForm($this->createUrl('/admin/participants/sa/storeUserControlValues'), 'post', $attribute);
            $options = array('Y' => gT('Yes'), 'N' => gT('No'));
            ?>
            <ul>
                <li>
                    <label for='userideditable' id='userideditable'>
                        <?php eT('User ID editable:'); ?>
                    </label>
                    <?php echo CHtml::dropDownList('userideditable', $userideditable, $options); ?>
                </li>
            </ul>
            <p>
                <?php
                echo CHtml::submitButton('submit', array('value' => gT('Save')));
                ?>
            </p>
            <?php
            echo CHtml::endForm();
        }
        else
        {
            echo "<div class='messagebox ui-corner-all'>" . gT("You don't have sufficient permissions.") . "</div>";
        }
        ?>
    </div>
</div>
