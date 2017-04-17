<?php
/**
 * @package     RedSHOP
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 *  PlgRedshop_ProductProduct_AltText installer class.
 *
 * @package  Redshopb.Plugin
 * @since    1.0
 */
class PlgRedshop_ProductProduct_AltText extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * onAfterProductSave
	 * 
	 * @param   object  &$product  data
	 * @param   bool    &$altText  new or exist
	 * 
	 * @return  void
	 */
	public function onChangeMainProductImageAlternateText(&$product, &$altText)
	{
		$altText = $this->params->get('product_image_alt_text', '');

		if (isset($product->product_name))
		{
			$altText = str_replace('{product_name}', $product->product_name, $altText);
		}

		if (isset($product->category_name))
		{
			$altText = str_replace('{category_name}', $product->category_name, $altText);
		}
		else
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->qn('category_name'))
				->from($db->qn('#__redshop_category'))
				->where($db->qn('category_id') . ' = ' . $product->category_id);

			$cat = $db->setQuery($query)->loadObject();

			if (is_object($cat))
			{
				$altText = str_replace('{category_name}', $cat->category_name, $altText);
			}
		}
	}
}