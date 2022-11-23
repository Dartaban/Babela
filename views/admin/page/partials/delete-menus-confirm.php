<?php
$pageTitle = __('Delete menus translation');

if (!$isPartial):
    echo head(array('title' => $pageTitle));
endif;
?>
<div title="<?php echo $pageTitle; ?>">
    <h2><?php echo __('Are you sure?'); ?></h2>
    <p><?php echo __("All the translation will be lost"); ?></p>
    <?php echo $form; ?>
</div>
<?php
if (!$isPartial):
    echo foot();
endif;
?>