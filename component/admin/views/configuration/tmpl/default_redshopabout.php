<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

?>

<fieldset class="adminform">
    <div class="row">
        <div class="col-sm-6">
            <fieldset class="adminform">
				<?php echo $this->loadTemplate('system_information') ?>
            </fieldset>
        </div>
        <div class="col-sm-6">
            <fieldset class="adminform">
				<?php echo $this->loadTemplate('redshop_modules'); ?>
            </fieldset>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <fieldset class="adminform">
				<?php echo $this->loadTemplate('redshop_shipping') ?>
            </fieldset>
        </div>
        <div class="col-sm-6">
            <fieldset class="adminform">
				<?php echo $this->loadTemplate('redshop_plugins'); ?>
            </fieldset>
        </div>
    </div>
</fieldset>
