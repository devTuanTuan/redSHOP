<?php
/**
 * @package     RedSHOP.Library
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Class Redshop Helper Product
 *
 * @since  1.5
 */
class RedshopHelperProduct
{
	/**
	 * Product info
	 *
	 * @var  array
	 */
	protected static $products = array();

	/**
	 * All product data
	 *
	 * @var  array
	 */
	protected static $allProducts = array();

	/**
	 * Get all product information
	 * Warning: This method is loading all the products from DB. Which can resulting
	 * 			into memory issue. Use with caution.
	 * 			It is aimed to use in CLI version or for webservices.
	 *
	 * @return  array  Product Information array
	 */
	public static function getList()
	{
		if (empty(self::$allProducts))
		{
			$db    = JFactory::getDbo();
			$query = self::getMainProductQuery();
			$query->select(
						array(
							'p.product_name as text',
							'p.product_id as value'
						)
					);

			$db->setQuery($query);

			self::$allProducts = $db->loadObjectList('product_id');
		}

		return self::$allProducts;
	}

	/**
	 * Get product information
	 *
	 * @param   int  $productId  Product id
	 * @param   int  $userId     User id
	 *
	 * @return mixed
	 */
	public static function getProductById($productId, $userId = 0)
	{
		if (!$userId)
		{
			$user = JFactory::getUser();
			$userId = $user->id;
		}

		$key = $productId . '.' . $userId;

		if (!array_key_exists($key, self::$products))
		{
			// Check if data is already loaded while getting list
			if (array_key_exists($productId, self::$allProducts))
			{
				self::$products[$key] = self::$allProducts[$productId];
			}

			// Otheriwise load product info
			else
			{
				$db = JFactory::getDbo();
				$query = self::getMainProductQuery(false, $userId);

				// Select product
				$query->where($db->qn('p.product_id') . ' = ' . (int) $productId);

				$db->setQuery($query);
				self::$products[$key] = $db->loadObject();
			}

			if (self::$products[$key])
			{
				self::setProductRelates(array($key => self::$products[$key]), $userId);
			}
		}

		return self::$products[$key];
	}

	/**
	 * Get Main Product Query
	 *
	 * @param   bool|JDatabaseQuery  $query   Get query or false
	 * @param   int                  $userId  User id
	 *
	 * @return JDatabaseQuery
	 */
	public static function getMainProductQuery($query = false, $userId = 0)
	{
		$userHelper = rsUserHelper::getInstance();
		$shopperGroupId = $userHelper->getShopperGroup($userId);
		$db = JFactory::getDbo();

		if (!$query)
		{
			$query = $db->getQuery(true);
		}

		$query->select(array('p.*', 'p.product_id'))
			->from($db->qn('#__redshop_product', 'p'));

		// Require condition
		$query->group('p.product_id');

		// Select price
		$query->select(
			array(
				'pp.price_id', $db->qn('pp.product_price', 'price_product_price'),
				$db->qn('pp.product_currency', 'price_product_currency'), $db->qn('pp.discount_price', 'price_discount_price'),
				$db->qn('pp.discount_start_date', 'price_discount_start_date'), $db->qn('pp.discount_end_date', 'price_discount_end_date')
			)
		)
			->leftJoin(
				$db->qn('#__redshop_product_price', 'pp')
				. ' ON p.product_id = pp.product_id AND ((pp.price_quantity_start <= 1 AND pp.price_quantity_end >= 1)'
				. ' OR (pp.price_quantity_start = 0 AND pp.price_quantity_end = 0)) AND pp.shopper_group_id = ' . (int) $shopperGroupId
			)
			->order('pp.price_quantity_start ASC');

		// Select category
		$query->select(array('pc.category_id'))
			->leftJoin($db->qn('#__redshop_product_category_xref', 'pc') . ' ON pc.product_id = p.product_id');

		// Getting cat_in_sefurl as main category id if it available
		$query->leftJoin($db->qn('#__redshop_product_category_xref', 'pc3') . ' ON pc3.product_id = p.product_id AND pc3.category_id = p.cat_in_sefurl')
			->leftJoin($db->qn('#__redshop_category', 'c3') . ' ON pc3.category_id = c3.category_id AND c3.published = 1');

		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(DISTINCT c2.category_id ORDER BY c2.category_id ASC SEPARATOR ' . $db->q(',') . ')')
			->from($db->qn('#__redshop_category', 'c2'))
			->leftJoin($db->qn('#__redshop_product_category_xref', 'pc2') . ' ON c2.category_id = pc2.category_id')
			->where('p.product_id = pc2.product_id')
			->where('((p.cat_in_sefurl != ' . $db->q('') . ' AND p.cat_in_sefurl != pc2.category_id) OR p.cat_in_sefurl = ' . $db->q('') . ')')
			->where('c2.published = 1');

		// In first position set main category id
		$query->select('CONCAT_WS(' . $db->q(',') . ', c3.category_id, (' . $subQuery . ')) AS categories');

		// Select media
		$query->select(array('media.media_alternate_text', 'media.media_id'))
			->leftJoin(
				$db->qn('#__redshop_media', 'media')
				. ' ON media.section_id = p.product_id AND media.media_section = ' . $db->q('product')
				. ' AND media.media_type = ' . $db->q('images') . ' AND media.media_name = p.product_full_image'
			);

		// Select ratings
		$subQuery = $db->getQuery(true)
			->select('COUNT(pr1.rating_id)')
			->from($db->qn('#__redshop_product_rating', 'pr1'))
			->where('pr1.product_id = p.product_id')
			->where('pr1.published = 1');

		$query->select('(' . $subQuery . ') AS count_rating');

		$subQuery = $db->getQuery(true)
			->select('SUM(pr2.user_rating)')
			->from($db->qn('#__redshop_product_rating', 'pr2'))
			->where('pr2.product_id = p.product_id')
			->where('pr2.published = 1');

		$query->select('(' . $subQuery . ') AS sum_rating');

		// Count Accessories
		$subQuery = $db->getQuery(true)
			->select('COUNT(pa.accessory_id)')
			->from($db->qn('#__redshop_product_accessory', 'pa'))
			->leftJoin($db->qn('#__redshop_product', 'parent_product') . ' ON parent_product.product_id = pa.child_product_id')
			->where('pa.product_id = p.product_id')
			->where('parent_product.published = 1');

		$query->select('(' . $subQuery . ') AS total_accessories');

		// Count child products
		$subQuery = $db->getQuery(true)
			->select('COUNT(child.product_id) AS count_child_products, child.product_parent_id')
			->from($db->qn('#__redshop_product', 'child'))
			->where('child.product_parent_id > 0')
			->where('child.published = 1')
			->group('child.product_parent_id');

		$query->select('child_product_table.count_child_products')
			->leftJoin('(' . $subQuery . ') AS child_product_table ON child_product_table.product_parent_id = p.product_id');

		// Sum quantity
		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$subQuery = $db->getQuery(true)
				->select('SUM(psx.quantity)')
				->from($db->qn('#__redshop_product_stockroom_xref', 'psx'))
				->where('psx.product_id = p.product_id')
				->where('psx.quantity >= 0')
				->where('psx.stockroom_id > 0');

			$query->select('(' . $subQuery . ') AS sum_quanity');
		}

		return $query;
	}

	/**
	 * Set product relates
	 *
	 * @param   array  $products  Products
	 * @param   int    $userId    User id
	 *
	 * @return  void
	 */
	public static function setProductRelates($products, $userId = 0)
	{
		if (!$userId)
		{
			$user = JFactory::getUser();
			$userId = $user->id;
		}

		$keys = array();

		foreach ((array) $products  as $product)
		{
			if (isset($product->product_id))
			{
				$keys[] = $product->product_id;
				self::$products[$product->product_id . '.' . $userId]->attributes = array();
				self::$products[$product->product_id . '.' . $userId]->extraFields = array();
				self::$products[$product->product_id . '.' . $userId]->categories = explode(',', $product->categories);
			}
		}

		if (count($keys) > 0)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select(
					array(
						'a.attribute_id AS value', 'a.attribute_name AS text', 'a.*',
						'ast.attribute_set_name', 'ast.published AS attribute_set_published'
					)
				)
				->from($db->qn('#__redshop_product_attribute', 'a'))
				->leftJoin($db->qn('#__redshop_attribute_set', 'ast') . ' ON ast.attribute_set_id = a.attribute_set_id')
				->where('a.attribute_name != ' . $db->q(''))
				->where('a.attribute_published = 1')
				->where('a.product_id IN (' . implode(',', $keys) . ')')
				->order('a.ordering ASC');
			$db->setQuery($query);

			if ($results = $db->loadObjectList())
			{
				foreach ($results as $result)
				{
					self::$products[$result->product_id . '.' . $userId]->attributes[$result->attribute_id] = $result;
					self::$products[$result->product_id . '.' . $userId]->attributes[$result->attribute_id]->properties = array();
				}

				$query->clear()
					->select(
						array('ap.property_id AS value', 'ap.property_name AS text', 'ap.*', 'a.attribute_name', 'a.attribute_id', 'a.product_id', 'a.attribute_set_id')
					)
					->from($db->qn('#__redshop_product_attribute_property', 'ap'))
					->leftJoin($db->qn('#__redshop_product_attribute', 'a') . ' ON a.attribute_id = ap.attribute_id')
					->where('a.product_id IN (' . implode(',', $keys) . ')')
					->where('ap.property_published = 1')
					->where('a.attribute_published = 1')
					->where('a.attribute_name != ' . $db->q(''))
					->order('ap.ordering ASC');
				$db->setQuery($query);

				if ($results = $db->loadObjectList())
				{
					foreach ($results as $result)
					{
						self::$products[$result->product_id . '.' . $userId]->attributes[$result->attribute_id]->properties[$result->property_id] = $result;
					}
				}
			}

			$query = $db->getQuery(true)
				->select('fd.*, f.field_title')
				->from($db->qn('#__redshop_fields_data', 'fd'))
				->leftJoin($db->qn('#__redshop_fields', 'f') . ' ON fd.fieldid = f.field_id')
				->where('fd.itemid IN (' . implode(',', $keys) . ')')
				->where('fd.section = 1');

			if ($results = $db->setQuery($query)->loadObjectList())
			{
				foreach ($results as $result)
				{
					self::$products[$result->itemid . '.' . $userId]->extraFields[$result->fieldid] = $result;
				}
			}
		}
	}

	/**
	 * Set product array
	 *
	 * @param   array  $products  Array product/s values
	 *
	 * @return void
	 */
	public static function setProduct($products)
	{
		self::$products = $products + self::$products;
		self::setProductRelates($products);
	}

	/**
	 * Replace Accessory Data
	 *
	 * @param   int     $productId  Product id
	 * @param   array   $accessory  Accessory list
	 * @param   int     $userId     User id
	 * @param   string  $uniqueId   Unique id
	 *
	 * @return mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function replaceAccessoryData($productId = 0, $accessory = array(), $userId = 0, $uniqueId = "")
	{
		$redconfig = Redconfiguration::getInstance();
		$producthelper = productHelper::getInstance();
		$totalAccessory = count($accessory);
		$accessorylist = "";

		if ($totalAccessory > 0)
		{
			$accessorylist .= "<tr><th>" . JText::_('COM_REDSHOP_ACCESSORY_PRODUCT') . "</th></tr>";

			for ($a = 0, $an = count($accessory); $a < $an; $a++)
			{
				$ac_id = $accessory [$a]->child_product_id;
				$c_p_data = Redshop::product((int) $ac_id);

				$accessory_name = $redconfig->maxchar($accessory [$a]->product_name, Redshop::getConfig()->get('ACCESSORY_PRODUCT_TITLE_MAX_CHARS'), Redshop::getConfig()->get('ACCESSORY_PRODUCT_TITLE_END_SUFFIX'));

				// Get accessory final price with VAT rules
				$accessorypricelist = $producthelper->getAccessoryPrice($productId, $accessory[$a]->newaccessory_price, $accessory[$a]->accessory_main_price);
				$accessory_price = $accessorypricelist[0];

				$accessoryprice_withoutvat = $producthelper->getAccessoryPrice(
					$productId, $accessory[$a]->newaccessory_price,
					$accessory[$a]->accessory_main_price, 1
				);
				$accessory_price_withoutvat = $accessoryprice_withoutvat[0];
				$accessory_price_vat = $accessory_price - $accessory_price_withoutvat;

				$commonid = $productId . '_' . $accessory [$a]->accessory_id . $uniqueId;

				// Accessory attribute  Start
				$attributes_set = array();

				if ($c_p_data->attribute_set_id > 0)
				{
					$attributes_set = $producthelper->getProductAttribute(0, $c_p_data->attribute_set_id);
				}

				$attributes = $producthelper->getProductAttribute($ac_id);
				$attributes = array_merge($attributes, $attributes_set);

				$accessory_checkbox = "<input onClick='calculateOfflineTotalPrice(\"" . $uniqueId . "\");' type='checkbox' name='accessory_id_"
					. $productId . $uniqueId . "[]' totalattributs='" . count($attributes) . "' accessoryprice='"
					. $accessory_price . "' accessorypricevat='" . $accessory_price_vat . "' id='accessory_id_"
					. $commonid . "' value='" . $accessory [$a]->accessory_id . "' />";

				$accessorylist .= "<tr><td>" . $accessory_checkbox . "&nbsp;" . $accessory_name . ' : '
					. $producthelper->getProductFormattedPrice($accessory_price) . "</td></tr>";

				$accessorylist .= $this->replaceAttributeData($productId, $accessory [$a]->accessory_id, $attributes, $userId, $uniqueid);
			}
		}

		return $accessorylist;
	}

	/**
	 * Replace Attribute Data
	 *
	 * @param   int     $productId    Product id
	 * @param   int     $accessoryId  Accessory id
	 * @param   array   $attributes   Attribute list
	 * @param   int     $userId       User id
	 * @param   string  $uniqueId     Unique id
	 *
	 * @return mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function replaceAttributeData($productId = 0, $accessoryId = 0, $attributes = array(), $userId = 0, $uniqueId = "")
	{
		$producthelper = productHelper::getInstance();
		$attributeList = "";
		$product       = Redshop::product((int) $productId);

		if ($accessoryId != 0)
		{
			$prefix = $uniqueId . "acc_";
		}
		else
		{
			$prefix = $uniqueId . "prd_";
		}

		JText::script('COM_REDSHOP_ATTRIBUTE_IS_REQUIRED');

		for ($a = 0, $an = count($attributes); $a < $an; $a++)
		{
			$property = $producthelper->getAttibuteProperty(0, $attributes[$a]->attribute_id);

			if ($attributes[$a]->text != "" && count($property) > 0)
			{
				$commonId = $prefix . $productId . '_' . $accessoryId . '_' . $attributes[$a]->attribute_id;
				$hiddenattid = 'attribute_id_' . $prefix . $productId . '_' . $accessoryId;
				$propertyid = 'property_id_' . $commonId;

				for ($i = 0, $in = count($property); $i < $in; $i++)
				{
					$attributes_property_vat = 0;

					if ($property [$i]->property_price > 0)
					{
						$propertyOprand = $property[$i]->oprand;

						$propertyPrice = $producthelper->getProductFormattedPrice($property[$i]->property_price);

						// Get product vat to include.
						$attributes_property_vat = $producthelper->getProducttax($productId, $property [$i]->property_price, $userId);
						$property[$i]->property_price += $attributes_property_vat;

						$propertyPriceWithVat = $producthelper->getProductFormattedPrice($property[$i]->property_price);

						$property[$i]->text = urldecode($property[$i]->property_name) . " ($propertyOprand $propertyPrice excl. vat / $propertyPriceWithVat)";
					}
					else
					{
						$property[$i]->text = urldecode($property[$i]->property_name);
					}

					$attributeList .= '<input type="hidden" id="' . $propertyid . '_oprand' . $property [$i]->value . '" value="' . $property [$i]->oprand . '" />';
					$attributeList .= '<input type="hidden" id="' . $propertyid . '_protax' . $property [$i]->value . '" value="'
						. $attributes_property_vat . '" />';
					$attributeList .= '<input type="hidden" id="' . $propertyid . '_proprice' . $property [$i]->value . '" value="'
						. $property [$i]->property_price . '" />';
				}

				$tmp_array = array();
				$tmp_array[0] = new stdClass;
				$tmp_array[0]->value = 0;
				$tmp_array[0]->text = JText::_('COM_REDSHOP_SELECT') . " " . urldecode($attributes[$a]->text);

				$new_property = array_merge($tmp_array, $property);
				$chklist = "";

				if ($attributes[$a]->allow_multiple_selection)
				{
					for ($chk = 0; $chk < count($property); $chk++)
					{
						if ($attributes[$a]->attribute_required == 1)
						{
							$required = "required='" . $attributes[$a]->attribute_required . "'";
						}
						else
						{
							$required = "";
						}

						$chklist .= "<br /><input type='checkbox' value='" . $property[$chk]->value . "' name='"
							. $propertyid . "[]' id='" . $propertyid . "' class='inputbox' attribute_name='"
							. $attributes[$a]->attribute_name . "' required='" . $attributes[$a]->attribute_required
							. "' onchange='javascript:changeOfflinePropertyDropdown(\"" . $productId . "\",\"" . $accessoryId
							. "\",\"" . $attributes[$a]->attribute_id . "\",\"" . $uniqueId . "\");'  />&nbsp;" . $property[$chk]->text;
					}
				}
				else
				{
					$chklist = JHTML::_('select.genericlist', $new_property, $propertyid . '[]', 'id="' . $propertyid
						. '"  class="inputbox" size="1" attribute_name="' . $attributes[$a]->attribute_name . '" required="'
						. $attributes[$a]->attribute_required . '" onchange="javascript:changeOfflinePropertyDropdown(\''
						. $productId . '\',\'' . $accessoryId . '\',\'' . $attributes[$a]->attribute_id . '\',\'' . $uniqueId
						. '\');" ', 'value', 'text', '');
				}

				$lists ['property_id'] = $chklist;

				$attributeList .= "<input type='hidden' name='" . $hiddenattid . "[]' value='" . $attributes [$a]->value . "' />";

				if ($attributes[$a]->attribute_required > 0)
				{
					$pos = Redshop::getConfig()->get('ASTERISK_POSITION') > 0 ? urldecode($attributes [$a]->text)
						. "<span id='asterisk_right'> * " : "<span id='asterisk_left'>* </span>"
						. urldecode($attributes[$a]->text);
					$attrTitle = $pos;
				}
				else
				{
					$attrTitle = urldecode($attributes[$a]->text);
				}

				$attributeList .= "<tr><td>" . $attrTitle . " : " . $lists ['property_id'] . "</td></tr>";
				$attributeList .= "<tr><td><div id='property_responce" . $commonId . "' style='display:none;'></td></tr>";
			}
		}

		return $attributeList;
	}

	/**
	 * Replace Accessory Data
	 *
	 * @param   int     $productId  Product id
	 * @param   int     $userId     User id
	 * @param   string  $uniqueId   Unique id
	 *
	 * @return mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function replaceWrapperData($productId = 0, $userId = 0, $uniqueId = "")
	{
		$producthelper = productHelper::getInstance();
		$wrapperList = "";

		$wrapper = $producthelper->getWrapper($productId, 0, 1);

		if (count($wrapper) > 0)
		{
			$wArray = array();
			$wArray[0] = new stdClass;
			$wArray[0]->wrapper_id = 0;
			$wArray[0]->wrapper_name = JText::_('COM_REDSHOP_SELECT');
			$commonId = $productId . $uniqueId;

			for ($i = 0, $in = count($wrapper); $i < $in; $i++)
			{
				$wrapperVat = 0;

				if ($wrapper[$i]->wrapper_price > 0)
				{
					$wrapperVat = $producthelper->getProducttax($productId, $wrapper[$i]->wrapper_price, $userId);
				}

				$wrapper[$i]->wrapper_price += $wrapperVat;
				$wrapper [$i]->wrapper_name = $wrapper [$i]->wrapper_name . " ("
					. $producthelper->getProductFormattedPrice($wrapper[$i]->wrapper_price) . ")";
				$wrapperList .= "<input type='hidden' id='wprice_" . $commonId . "_"
					. $wrapper [$i]->wrapper_id . "' value='" . $wrapper[$i]->wrapper_price . "' />";
				$wrapperList .= "<input type='hidden' id='wprice_tax_" . $commonId . "_"
					. $wrapper [$i]->wrapper_id . "' value='" . $wrapperVat . "' />";
			}

			$wrapper = array_merge($wArray, $wrapper);
			$lists ['wrapper_id'] = JHTML::_(
				'select.genericlist',
				$wrapper, 'wrapper_id_' . $commonId . '[]',
				'id="wrapper_id_' . $commonId . '" class="inputbox" onchange="calculateOfflineTotalPrice(\'' . $uniqueId . '\');" ',
				'wrapper_id', 'wrapper_name',
				0
			);

			$wrapperList .= "<tr><td>" . JText::_('COM_REDSHOP_WRAPPER') . " : " . $lists ['wrapper_id'] . "</td></tr>";
		}

		return $wrapperList;
	}

	/**
	 * Get product item info
	 *
	 * @param   int     $productId        Product id
	 * @param   int     $quantity         Product quantity
	 * @param   string  $uniqueId         Unique id
	 * @param   int     $userId           User id
	 * @param   int     $newProductPrice  New product price
	 *
	 * @return mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getProductItemInfo($productId = 0, $quantity = 1, $uniqueId = "", $userId = 0, $newProductPrice = 0)
	{
		$productHelper = productHelper::getInstance();

		$wrapperList = "";
		$accessoryList = "";
		$attributeList = "";
		$productUserField = "";
		$productPrice = 0;
		$productPriceExclVat = 0;
		$productTax = 0;

		if ($productId)
		{
			$productInfo = Redshop::product((int) $productId);

			if ($newProductPrice != 0)
			{
				$productPriceExclVat = $newProductPrice;
				$productTax = $productHelper->getProductTax($productId, $newProductPrice, $userId);
			}

			else
			{
				$productArr = $productHelper->getProductNetPrice($productId, $userId, $quantity);
				$productPriceExclVat = $productArr['productPrice'];
				$productTax = $productArr['productVat'];

				// Attribute start
				$attributesSet = array();

				if ($productInfo->attribute_set_id > 0)
				{
					$attributesSet = $productHelper->getProductAttribute(0, $productInfo->attribute_set_id, 0, 1);
				}

				$attributes = $productHelper->getProductAttribute($productId);
				$attributes = array_merge($attributes, $attributesSet);
				$attributeList = $this->replaceAttributeData($productId, 0, $attributes, $userId, $uniqueId);

				// Accessory start
				$accessory = $productHelper->getProductAccessory(0, $productId);
				$accessoryList = $this->replaceAccessoryData($productId, $accessory, $userId, $uniqueId);

				// Wrapper selection box generate
				$wrapperList = $this->replaceWrapperData($productId, $userId, $uniqueId);
				$productUserField = $this->replaceUserfield($productId, $productInfo->product_template, $uniqueId);
			}
		}

		$productPrice = $productPriceExclVat + $productTax;
		$total_price = $productPrice * $quantity;
		$totalTax = $productTax * $quantity;

		$displayrespoce = "";
		$displayrespoce .= "<div id='product_price_excl_vat'>" . $productPriceExclVat . "</div>";
		$displayrespoce .= "<div id='product_tax'>" . $productTax . "</div>";
		$displayrespoce .= "<div id='product_price'>" . $productPrice . "</div>";
		$displayrespoce .= "<div id='total_price'>" . $total_price . "</div>";
		$displayrespoce .= "<div id='total_tax'>" . $totalTax . "</div>";
		$displayrespoce .= "<div id='attblock'><table>" . $attributeList . "</table></div>";
		$displayrespoce .= "<div id='productuserfield'><table>" . $productUserField . "</table></div>";
		$displayrespoce .= "<div id='accessoryblock'><table>" . $accessoryList . "</table></div>";
		$displayrespoce .= "<div id='noteblock'>" . $wrapperList . "</div>";

		return $displayrespoce;
	}

	/**
	 * Replace Shipping method
	 *
	 * @param   array  $d                 Data
	 * @param   int    $shippUsersInfoId  Shipping User info id
	 * @param   int    $shippingRateId    Shipping rate id
	 *
	 * @return mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function replaceShippingMethod($d = array(), $shippUsersInfoId = 0, $shippingRateId = 0)
	{
		$productHelper = productHelper::getInstance();
		$orderFunctions = order_functions::getInstance();

		if ($shippUsersInfoId > 0)
		{
			$language = JFactory::getLanguage();
			$shippingMethod = $orderFunctions->getShippingMethodInfo();

			JPluginHelper::importPlugin('redshop_shipping');
			$dispatcher = JDispatcher::getInstance();
			$shippingRate = $dispatcher->trigger('onListRates', array(&$d));

			$rateArr = array();
			$r = 0;

			for ($s = 0, $sn = count($shippingMethod); $s < $sn; $s++)
			{
				if (isset($shippingRate[$s]) === false)
				{
					continue;
				}

				$rate = $shippingRate[$s];
				$extension = 'plg_redshop_shipping_' . strtolower($shippingMethod[$s]->element);
				$language->load($extension, JPATH_ADMINISTRATOR);
				$rs = $shippingMethod[$s];

				if (count($rate) > 0)
				{
					for ($i = 0, $in = count($rate); $i < $in; $i++)
					{
						$displayrate = ($rate[$i]->rate > 0) ? " (" . $productHelper->getProductFormattedPrice($rate[$i]->rate) . " )" : "";
						$rateArr[$r] = new stdClass;
						$rateArr[$r]->text = JText::_($rs->name) . " - " . $rate[$i]->text . $displayrate;
						$rateArr[$r]->value = $rate[$i]->value;
						$r++;
					}
				}
			}

			if (count($rateArr) > 0)
			{
				if (!$shippingRateId)
				{
					$shippingRateId = $rateArr[0]->value;
				}

				$displayRespoce = JHTML::_('select.genericlist', $rateArr, 'shipping_rate_id', 'class="inputbox" onchange="calculateOfflineShipping();" ', 'value', 'text', $shippingRateId);
			}

			else
			{
				$displayRespoce = JText::_('COM_REDSHOP_NO_SHIPPING_METHODS_TO_DISPLAY');
			}
		}

		else
		{
			$displayRespoce = '<div class="shipnotice">' . JText::_('COM_REDSHOP_FILL_SHIPPING_ADDRESS') . '</div>';
		}

		return $displayRespoce;
	}

	/**
	 * Redesign product item
	 *
	 * @param   array  $post  Data
	 *
	 * @return array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function redesignProductItem($post = array())
	{
		$orderItem = array();
		$i = -1;

		foreach ($post as $key => $value)
		{
			if (!strcmp("product", substr($key, 0, 7)) && strlen($key) < 10)
			{
				$i++;

				if (!isset($orderItem[$i]))
				{
					$orderItem[$i] = new stdClass;
				}

				$orderItem[$i]->product_id = $value;
			}

			if (!strcmp("attribute_dataproduct", substr($key, 0, 21)))
			{
				$orderItem[$i]->attribute_data = $value;
			}

			if (!strcmp("property_dataproduct", substr($key, 0, 20)))
			{
				$orderItem[$i]->property_data = $value;
			}

			if (!strcmp("subproperty_dataproduct", substr($key, 0, 23)))
			{
				$orderItem[$i]->subproperty_data = $value;
			}

			if (!strcmp("accessory_dataproduct", substr($key, 0, 21)))
			{
				$orderItem[$i]->accessory_data = $value;
			}

			if (!strcmp("acc_attribute_dataproduct", substr($key, 0, 25)))
			{
				$orderItem[$i]->acc_attribute_data = $value;
			}

			if (!strcmp("acc_property_dataproduct", substr($key, 0, 24)))
			{
				$orderItem[$i]->acc_property_data = $value;
			}

			if (!strcmp("acc_subproperty_dataproduct", substr($key, 0, 27)))
			{
				$orderItem[$i]->acc_subproperty_data = $value;
			}

			if (!strcmp("extrafieldId", substr($key, 0, 12)))
			{
				$orderItem[$i]->extrafieldId = $value;
			}

			if (!strcmp("extrafieldname", substr($key, 0, 14)))
			{
				$orderItem[$i]->extrafieldname = $value;
			}

			if (!strcmp("wrapper_dataproduct", substr($key, 0, 19)))
			{
				$orderItem[$i]->wrapper_data = $value;
			}

			if (!strcmp("quantityproduct", substr($key, 0, 15)))
			{
				$orderItem[$i]->quantity = $value;
			}

			if (!strcmp("prdexclpriceproduct", substr($key, 0, 19)))
			{
				$orderItem[$i]->prdexclprice = $value;
			}

			if (!strcmp("taxpriceproduct", substr($key, 0, 15)))
			{
				$orderItem[$i]->taxprice = $value;
			}

			if (!strcmp("productpriceproduct", substr($key, 0, 19)))
			{
				$orderItem[$i]->productprice = $value;
			}

			if (!strcmp("requiedAttributeproduct", substr($key, 0, 23)))
			{
				$orderItem[$i]->requiedAttributeproduct = $value;
			}
		}

		return $orderItem;
	}

	/**
	 * Replace User Field
	 *
	 * @param   int     $productId   Product id
	 * @param   int     $templateId  Template id
	 * @param   string  $uniqueId    Unique id
	 *
	 * @return mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function replaceUserfield($productId = 0, $templateId = 0, $uniqueId = "")
	{
		$productHelper = productHelper::getInstance();
		$redTemplate = Redtemplate::getInstance();
		$extraField = extra_field::getInstance();
		$templateDesc = $redTemplate->getTemplate("product", $templateId);
		$returnArr = $productHelper->getProductUserfieldFromTemplate($templateDesc[0]->template_desc);

		$commonId = $productId . $uniqueId;
		$productUserFields = "<table>";

		for ($ui = 0; $ui < count($returnArr[1]); $ui++)
		{
			$resultArr = $extraField->list_all_user_fields($returnArr[1][$ui], 12, "", $commonId);
			$hiddenArr = $extraField->list_all_user_fields($returnArr[1][$ui], 12, "hidden", $commonId);

			if ($resultArr[0] != "")
			{
				$productUserFields .= "<tr><td>" . $resultArr[0] . "</td><td>" . $resultArr[1] . $hiddenArr[1] . "</td></tr>";
			}
		}

		$productUserFields .= "</table>";

		return $productUserFields;
	}

	/**
	 * Insert Product user field
	 *
	 * @param   int     $fieldId      Field id
	 * @param   int     $orderItemId  Order item id
	 * @param   int     $sectionId    Section id
	 * @param   string  $value        Unique id
	 *
	 * @return boolen
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function admin_insertProdcutUserfield($fieldId = 0, $orderItemId = 0, $sectionId = 12, $value = '')
	{
		$db = JFactory::getDbo();
		$columns = array('fieldid', 'data_txt', 'itemid', 'section');
		$values = array($db->q((int) $fieldId), $db->q($value), $db->q((int) $orderItemId), $db->q((int) $sectionId));

		$query = $db->getQuery(true)
			->insert($db->qn('#__redshop_fields_data'))
			->columns($db->qn($columns))
			->values(implode(',', $values));

		return $db->setQuery($query)->execute();
	}

	/**
	 * Get product by sort list
	 *
	 * @return array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getProductrBySortedList()
	{
		$productData = array();
		$productData[0] = new stdClass;
		$productData[0]->value = "0";
		$productData[0]->text = JText::_('COM_REDSHOP_SELECT_PUBLISHED');

		$productData[1] = new stdClass;
		$productData[1]->value = "p.published";
		$productData[1]->text = JText::_('COM_REDSHOP_PRODUCT_PUBLISHED');

		$productData[2] = new stdClass;
		$productData[2]->value = "p.unpublished";
		$productData[2]->text = JText::_('COM_REDSHOP_PRODUCT_UNPUBLISHED');

		$productData[3] = new stdClass;
		$productData[3]->value = "p.product_on_sale";
		$productData[3]->text = JText::_('COM_REDSHOP_PRODUCT_ON_SALE');

		$productData[4] = new stdClass;
		$productData[4]->value = "p.product_not_on_sale";
		$productData[4]->text = JText::_('COM_REDSHOP_PRODUCT_NOT_ON_SALE');

		$productData[5] = new stdClass;
		$productData[5]->value = "p.product_special";
		$productData[5]->text = JText::_('COM_REDSHOP_PRODUCT_SPECIAL');

		$productData[6] = new stdClass;
		$productData[6]->value = "p.expired";
		$productData[6]->text = JText::_('COM_REDSHOP_PRODUCT_EXPIRED');

		$productData[7] = new stdClass;
		$productData[7]->value = "p.not_for_sale";
		$productData[7]->text = JText::_('COM_REDSHOP_PRODUCT_NOT_FOR_SALE');

		$productData[8] = new stdClass;
		$productData[8]->value = "p.sold_out";
		$productData[8]->text = JText::_('COM_REDSHOP_PRODUCT_SOLD_OUT');

		return $productData;
	}
}
