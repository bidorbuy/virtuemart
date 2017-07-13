<?php

/**
 * Copyright (c) 2014, 2015, 2016 Bidorbuy http://www.bidorbuy.co.za
 * This software is the proprietary information of Bidorbuy.
 *
 * All Rights Reserved.
 * Modification, redistribution and use in source and binary forms, with or without modification
 * are not permitted without prior written approval by the copyright holder.
 *
 * Vendor: EXTREME IDEA LLC http://www.extreme-idea.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use com\extremeidea\bidorbuy\storeintegrator\core as bobsi;

JHtml::_('behavior.tooltip');

$jver = new JVersion();

//Feature 3910
$input = new JInput();
$baa = $input->get('baa', '', 'get');

$formData = (array)$this->params;
$formData = array_shift($formData);

/* Feature 3909*/
$warnings = array_merge(
    bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getWarnings(),
    bobsi\StaticHolder::getWarnings()->getBusinessWarnings()
);

foreach ($warnings as $warning) {
    JFactory::getApplication()->enqueueMessage($warning, 'error');
}
/*      3909        */

?>

<div id="logo" class="fltlft">
    <img src="<?php echo JRoute::_('components/com_bidorbuystoreintegrator/assets/images/bidorbuy.png'); ?>">
</div>

<div id="bobsi-adv" class="fltrt">
    <!-- BEGIN ADVERTPRO CODE BLOCK -->
    <script type="text/javascript">
        document.write('<scr' + 'ipt src="http://nope.bidorbuy.co.za/servlet/view/banner/javascript/zone?zid=153&pid=0&random=' + Math.floor(89999999 * Math.random() + 10000000) + '&millis=' + new Date().getTime() + '&referrer=' + encodeURIComponent(document.location) + '" type="text/javascript"></scr' + 'ipt>');
    </script>
    <!-- END ADVERTPRO CODE BLOCK -->
</div>

<form action="<?php echo JRoute::_('index.php?option=com_bidorbuystoreintegrator'); ?>" method="post" name="adminForm" id="bob-settings">
    <div class="width-50 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('COM_BIDORBUYSTOREINTEGRATOR_CONFIG_EXPORT_CONFIGS_TITLE'); ?></legend>
            <ul class="adminformlist">

                <!------------------------------Feature 3751-------------------------------------------------------->
                <?php
                $fieldset_tpl = $this->form->getFieldset('COM_BIDORBUYSTOREINTEGRATOR_CONFIG_EXPORT_CONFIGS');
                ?>
                <li>
                    <?php
                    // File name
                    $fs = $fieldset_tpl['jform_filename'];
                    echo $fs->label;
                    echo $fs->input;
                    ?>
                </li>
                <li>
                    <?php
                    $fs = $fieldset_tpl['jform_compressLibrary'];
                    echo $fs->label;
                    echo $fs->input;
                    ?>
                </li>
                <li>
                    <?php
                    $fs = $fieldset_tpl['jform_defaultStockQuantity'];
                    echo $fs->label;
                    echo $fs->input;
                    ?>
                </li>
                <li>
                    <?php
                    $fs = $fieldset_tpl['jform_categorySlug'];
                    echo $fs->label;
                    echo $fs->input;
                    ?>
                </li>
            </ul>
        </fieldset>
    </div>


    <div class="width-50 fltrt">
        <fieldset class="panelform">
            <legend><?php echo JText::_('COM_BIDORBUYSTOREINTEGRATOR_CONFIG_EXPORT_CRITERIA_TITLE'); ?></legend>
            <ul class="adminformlist">
                <?php foreach ($this->form->getFieldset('COM_BIDORBUYSTOREINTEGRATOR_CONFIG_EXPORT_CRITERIA') as $field): ?>
                    <li><?php
                        if ($field->name == 'jform[' . bobsi\Settings::nameExcludeCategories . '][]') {
                            echo $field->input;
                        } else {
                            echo $field->label;
                            echo $field->input;
                        }?></li>
                <?php endforeach; ?>
            </ul>
        </fieldset>
    </div>
    <input type="hidden" name="<?php echo bobsi\Settings::nameTokenDownload; ?>" value="<?php echo $this->params->getTokenDownload(); ?>">
    <input type="hidden" name="<?php echo bobsi\Settings::nameTokenExport; ?>" value="<?php echo $this->params->getTokenExport(); ?>">

    <div>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
    <!-------------------------------------------Feature 3751 ------------------------------------------------------------->
    <div class="width-100 fltlft">
        <fieldset id="debug" class="panelform">
            <legend>Debug</legend>

            <?php if($baa == 1): ?>

            <h4> <span>Basic Access Authentication</span></h4>
            <span style="font-size: 12px">(if necessary)</span>
            <h4><span style="color: red">
                    Do not enter username or password of ecommerce platform, please read carefully about this kind of authentication!
                </span>
            </h4>
            <ul class="adminformlist">
                <li>
                    <?php
                    $fs = $fieldset_tpl['jform_username'];
                    echo $fs->label;
                    echo $fs->input;
                    ?>
                </li>
                <li>
                    <?php
                    $fs = $fieldset_tpl['jform_password'];
                    echo $fs->label;
                    echo $fs->input;
                    ?>
                </li>
            </ul>

            <?php else: ?>
                <input type="hidden" name="jform[<?= bobsi\Settings::nameUsername; ?>]"  value="<?= $formData[bobsi\Settings::nameUsername]; ?>">
                <input type="hidden" name="jform[<?= bobsi\Settings::namePassword; ?>]"  value="<?= $formData[bobsi\Settings::namePassword]; ?>">
            <?php endif;?>

            <ul class="adminformlist">

                <li>
                    <label><h4>Logs</h4></label>
                </li>
                <li>
                    <?php
                    $fs = $fieldset_tpl['jform_enableNotificationAddresses'];
                    echo $fs->label;
                    echo $fs->input;
                    ?>
                </li>
                <li>
                    <?php
                    $fs = $fieldset_tpl['jform_enableEmailNotifications'];
                    echo $fs->label;
                    echo $fs->input;
                    ?>
                </li>
                <li>
                    <?php
                    $fs = $fieldset_tpl['jform_loggingLevel'];
                    echo $fs->label;
                    echo $fs->input;
                    ?>
                </li>
            </ul>
        </fieldset>
    </div>
</form>
<!-- Logs section ---------------------->
<div id="logs">
    <?php echo $this->bidorbuyStoreIntegrator->getLogsHtml();  ?>
</div>


<form id="links">
    <div class="width-100 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('COM_BIDORBUYSTOREINTEGRATOR_CONFIG_EXPORT_LINKS'); ?></legend>
            <ul>
                <li>
                    <label for="export" class="">Export</label>
                    <input class="tokenurl" id="export" type="text" readonly="readonly" value="<?php echo JURI::root() . 'index.php?option=com_bidorbuystoreintegrator&task=export&t=' . $this->params->getTokenExport(); ?>" />
                </li>
                <li>
                    <label for="download" class="">Download</label>
                    <input class="tokenurl" id="download" type="text" readonly="readonly" value="<?php echo JURI::root() . 'index.php?option=com_bidorbuystoreintegrator&task=download&t=' . $this->params->getTokenDownload(); ?>" />
                </li>
            </ul>

                <ul>
                    <li>
                        <label for="resetaudit" class=""><?php echo JText::_('COM_BIDORBUYSTOREINTEGRATOR_CONFIG_RESETAUDIT_URL'); ?></label>
                        <input class="tokenurl" id="resetaudit" type="text" readonly="readonly" value="<?php
                        echo JURI::root() . 'index.php?option=com_bidorbuystoreintegrator&task=' . bobsi\Settings::nameActionResetExportTables . '&t=' . $this->params->getTokenDownload();
                        ?>" />
                    </li>
                </ul>
                <div class="bobsi_resetaudit_desc"><?php echo JText::_('COM_BIDORBUYSTOREINTEGRATOR_CONFIG_RESETAUDIT_DESC'); ?></div>

        </fieldset>
    </div>
</form>

<div class="width-100 fltlft">
    <fieldset class="panelform">
        <legend>Version</legend>
        <a href="<?php echo JURI::root() . 'index.php?option=com_bidorbuystoreintegrator&task=showVersion&t=' .
            $this->params->getTokenDownload() ?>" target="_blank">@See PHP information</a><br><br>
        Joomla! <?php echo $jver->RELEASE . '.' . $jver->DEV_LEVEL ; ?>,
        <?php echo bobsi\Version::getLivePluginVersion(); ?>
    </fieldset>
</div>