<div class='header ui-widget-header'>
    <?php eT("Create dummy tokens"); ?>
</div>
<?php echo CHtml::form(array("admin/tokens/sa/adddummies/surveyid/{$surveyid}/subaction/add"), 'post', array('id'=>'edittoken', 'name'=>'edittoken', 'class'=>'form30')); ?>
    <ul>
        <li><label>ID:</label>
            <?php eT("Auto"); ?>
        </li>
        <li><label for='amount'><?php eT("Number of tokens"); ?>:</label>
            <input type='text' size='20' id='amount' name='amount' value="100" /></li>
        <li><label for='tokenlen'><?php eT("Token length"); ?>:</label>
            <input type='text' size='20' id='tokenlen' name='tokenlen' value="<?php echo $tokenlength; ?>" /></li>
        <li><label for='firstname'><?php eT("First name"); ?>:</label>
            <input type='text' size='30' id='firstname' name='firstname' value="" /></li>
        <li><label for='lastname'><?php eT("Last name"); ?>:</label>
            <input type='text' size='30'  id='lastname' name='lastname' value="" /></li>
        <li><label for='email'><?php eT("Email"); ?>:</label>
            <input type='email' maxlength='320' size='50' id='email' name='email' value="" /></li>
        <li><label for='language'><?php eT("Language"); ?>:</label>
            <?php echo languageDropdownClean($surveyid, Survey::model()->findByPk($surveyid)->language); ?>
        </li>
        <li><label for='usesleft'><?php eT("Uses left:"); ?></label>
            <input type='text' size='20' id='usesleft' name='usesleft' value="1" /></li>
        <li><label for='validfrom'><?php eT("Valid from"); ?>:</label>
            <input type='text' class='popupdatetime' size='20' id='validfrom' name='validfrom' value="<?php
                    if (isset($validfrom))
                    {
                        $datetimeobj = new Date_Time_Converter($validfrom, "Y-m-d H:i:s");
                        echo $datetimeobj->convert($dateformatdetails['phpdate'] . ' H:i');
                    }
                ?>" /> <label for='validuntil'><?php eT('until'); ?></label>
            <input type='text' size='20' id='validuntil' name='validuntil' class='popupdatetime' value="<?php
                    if (isset($validuntil))
                    {
                        $datetimeobj = new Date_Time_Converter($validuntil, "Y-m-d H:i:s");
                        echo $datetimeobj->convert($dateformatdetails['phpdate'] . ' H:i');
                    }
                ?>" /> <span class='annotation'><?php printf(gT('Format: %s'), $dateformatdetails['dateformat'] . ' ' . gT('hh:mm')); ?></span>
        </li>
        <?php
            // now the attribute fieds
            foreach ($aAttributeFields as $attr_name => $attr_description)
            {
            ?>
            <li>
                <label for='<?php echo $attr_name; ?>'><?php echo $attr_description['description'] . ($attr_description['mandatory'] == 'Y' ? '*' : '') ?>:</label>
                <input type='text' size='55' id='<?php echo $attr_name; ?>' name='<?php echo $attr_name; ?>' value='<?php
                        if (isset($$attr_name))
                        {
                            echo htmlspecialchars($$attr_name, ENT_QUOTES, 'UTF-8');
                        }
                    ?>' /></li>
            <?php } ?>
    </ul><p>
        <input type='submit' value='<?php eT("Add dummy tokens"); ?>' />
        <input type='hidden' name='sid' value='$surveyid' /></p>
</form>
