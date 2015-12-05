<div class="<?php echo $classMbTitle; ?>"><?php echo $title; ?></div>
<div class="messagebox">
    <div class="<?php echo $classMsg; ?>"><?php echo $message; ?></div>
    <br />
    <?php echo $extra; ?>
    <br />
    <?php echo CHtml::form($url, 'post'); ?>
    
        <input type="submit" value="<?php echo $urlText; ?>" />
        <?php
        if (!empty($hiddenVars))
        {
            foreach ($hiddenVars as $key => $value)
            {
?>
                <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
<?php
            }
        }
        ?>
    </form>
</div>
