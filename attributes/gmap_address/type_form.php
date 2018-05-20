<?php defined('C5_EXECUTE') or die('Access Denied.');
use Config;?>

<fieldset>
    <legend><?= t('Google Maps API Key') ?></legend>
    <div>
        <?php echo $form->text('apiKey', Config::get('app.api_keys.google.maps')); ?>
    </div>
</fieldset>
