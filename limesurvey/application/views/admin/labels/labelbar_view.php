<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php eT("Label Set"); ?>:</strong> <?php echo flattenText($row['label_name']); ?>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <img src='<?php echo $sImageURL; ?>blank.gif' width='40' height='16' alt='' />
            <img src='<?php echo $sImageURL; ?>separator.gif' alt='' />
            <?php if (Permission::model()->hasGlobalPermission('labelsets','update')) { ?>
            <a href='<?php echo $this->createUrl("admin/labels/sa/editlabelset/lid/".$lid); ?>'>
                <img src='<?php echo $sImageURL; ?>edit.png' alt='<?php eT("Edit label set"); ?>'  /></a>
            <?php }; ?>
            <?php if (Permission::model()->hasGlobalPermission('labelsets','delete')) { ?>
            <a href='#' data-action='deletelabelset' data-url='<?php echo $this->createUrl("admin/labels/sa/process"); ?>' data-confirm='<?php eT('Do you really want to delete this label set?'); ?>' >
                <img src='<?php echo $sImageURL; ?>delete.png'  alt='<?php eT("Delete label set"); ?>' /></a>
            <?php }; ?>
            <img src='<?php echo $sImageURL; ?>separator.gif'  alt='' />
            <?php if (Permission::model()->hasGlobalPermission('labelsets','export')) { ?>
            <a href='<?php echo $this->createUrl("admin/export/sa/dumplabel/lid/$lid");?>'>
                <img src='<?php echo $sImageURL; ?>dumplabel.png' alt='<?php eT("Export this label set"); ?>' /></a>
            <?php }; ?>
        </div>
        <div class='menubar-right'>
            <input type='image' src='<?php echo $sImageURL; ?>close.png' title='<?php eT("Close Window"); ?>' href="<?php echo $this->createUrl("admin/labels/sa/view"); ?>" />
        </div>
    </div>
</div>
<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>
