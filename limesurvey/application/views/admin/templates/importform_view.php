<div class='header ui-widget-header'><?php eT("Uploaded template file") ?></div>
<?php echo CHtml::form(array('admin/templates/sa/upload'), 'post', array('id'=>'importtemplate', 'name'=>'importtemplate', 'enctype'=>'multipart/form-data', 'onsubmit'=>'return validatefilename(this,"'.gT('Please select a file to import!', 'js').'");')); ?>

    <input type='hidden' name='lid' value='$lid' />
    <input type='hidden' name='action' value='templateupload' />
    <ul>
        <li>
            <label for='the_file'><?php eT("Select template ZIP file:") ?></label>
            <input id='the_file' name='the_file' type="file" />
        </li>
        <li>
            <label>&nbsp;</label>
        <?php if (!function_exists("zip_open")) {?>
            <?php eT("zip library not supported by PHP, Import ZIP Disabled", "js") ?>
        <?php } else {?>
            <input type='button' value='<?php eT("Import template ZIP archive") ?>' onclick='if (validatefilename(this.form,"<?php eT('Please select a file to import!', 'js') ?>")) { this.form.submit();}' />
        <?php }?>
        </li>
    </ul>
</form>
