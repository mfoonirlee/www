<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php echo sprintf(gT("Welcome to %s!"), 'LimeSurvey'); ?></div>
    <br />
    <p><?php

    eT("Some piece-of-cake steps to create your very own first survey:"); ?></p><br />
    <ol>
        <li><?php echo sprintf(gT('Create a new survey clicking on the %s icon in the upper right.'), "<img src='" . Yii::app()->getConfig('adminimageurl') . "add_20.png' name='ShowHelp' title='' alt='" . gT("Add survey") . "'/>"); ?></li>
        <li><?php eT('Create a new question group inside your survey.'); ?></li>
        <li><?php eT('Create one or more questions inside the new question group.'); ?></li>
        <li><?php echo sprintf(gT('Done. Test your survey using the %s icon.'), "<img src='" . Yii::app()->getConfig('adminimageurl') . "do_20.png' name='ShowHelp' title='' alt='" . gT("Test survey") . "'/>"); ?></li>
    </ol>
    <br />
</div>
