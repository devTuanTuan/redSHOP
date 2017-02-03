<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

echo RedshopLayoutHelper::render(
	'config.config',
	array(
		'title' => JText::_('COM_REDSHOP_USE_STOCKROOM_LBL'),
		'desc'  => JText::_('COM_REDSHOP_TOOLTIP_USE_STOCKROOM_LBL'),
		'field' => $this->lists['use_stockroom']
	)
);
echo RedshopLayoutHelper::render(
	'config.config',
	array(
		'title' => JText::_('COM_REDSHOP_USE_BLANK_AS_INFINITE_LBL'),
		'desc'  => JText::_('COM_REDSHOP_TOOLTIP_USE_BLANK_AS_INFINITE_LBL'),
		'field' => $this->lists['use_blank_as_infinite']
	)
);
echo RedshopLayoutHelper::render(
	'config.config',
	array(
		'title' => JText::_('COM_REDSHOP_DEDAULT_STOCKROOM_LBL'),
		'desc'  => JText::_('COM_REDSHOP_TOOLTIP_DEDAULT_STOCKROOM'),
		'field' => $this->lists['default_stockroom']
	)
);
?>
<div class="row">
    <div class="form-group">
        <label class="col-md-4 hasPopover" data-content="<?php echo JText::_('COM_REDSHOP_TOOLTIP_DEFAULT_STOCKAMOUNT_IMAGE_THUMB_WIDTH_HEIGHT_LBL'); ?>">
			<?php echo JText::_('COM_REDSHOP_DEFAULT_STOCKAMOUNT_IMAGE_THUMB_WIDTH_HEIGHT_LBL'); ?>
        </label>
        <div class="col-md-8">
            <div class="row">
                <div class="col-sm-6">
                    <input type="number" name="default_stockamount_thumb_width" id="default_stockamount_thumb_width" class="form-control"
                           value="<?php echo $this->config->get('DEFAULT_STOCKAMOUNT_THUMB_WIDTH'); ?>"/>
                </div>
                <div class="col-sm-6">
                    <input type="number" name="default_stockamount_thumb_height" id="default_stockamount_thumb_height" class="form-control"
                           value="<?php echo $this->config->get('DEFAULT_STOCKAMOUNT_THUMB_HEIGHT'); ?>"/>
                </div>
            </div>
        </div>
    </div>
</div>
<hr />
<?php
echo RedshopLayoutHelper::render(
	'config.config',
	array(
		'title' => JText::_('COM_REDSHOP_USE_PRODUCT_OUTOFSTOCK_IMAGE_LBL'),
		'desc'  => JText::_('COM_REDSHOP_TOOLTIP_USE_PRODUCT_OUTOFSTOCK_IMAGE'),
		'field' => $this->lists['use_product_outofstock_image']
	)
);
echo RedshopLayoutHelper::render(
	'config.config',
	array(
		'title' => JText::_('COM_REDSHOP_ENABLE_STOCKROOM_NOTIFICATION'),
		'desc'  => JText::_('COM_REDSHOP_ENABLE_STOCKROOM_NOTIFICATION'),
		'field' => $this->lists['enable_stockroom_notification']
	)
);
echo RedshopLayoutHelper::render(
	'config.config',
	array(
		'title' => JText::_('COM_REDSHOP_DEFAULT_STOCKROOM_BELOW_AMOUNT_NUMBER_LBL'),
		'desc'  => JText::_('COM_REDSHOP_DEFAULT_STOCKROOM_BELOW_AMOUNT_NUMBER_LBL'),
		'line'  => false,
		'field' => '<input type="number" name="default_stockroom_below_amount_number"
            value="' . $this->config->get('DEFAULT_STOCKROOM_BELOW_AMOUNT_NUMBER') . '"/>'
	)
);
