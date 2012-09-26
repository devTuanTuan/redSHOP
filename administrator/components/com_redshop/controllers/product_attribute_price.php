<?php
/**
 * @package     redSHOP
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2008 - 2012 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_COMPONENT_ADMINISTRATOR . DS . 'core' . DS . 'controller.php';

class product_attribute_priceController extends RedshopCoreController
{
    public function cancel()
    {
        $this->setRedirect('index.php');
    }

    public function listing()
    {
        $this->input->set('layout', 'listing');
        $this->input->set('hidemainmenu', 1);
        parent::display();
    }

    public function saveprice()
    {
        $db = JFactory::getDBO();

        $section_id           = $this->input->get('section_id');
        $section              = $this->input->get('section');
        $cid                  = $this->input->get('cid');
        $shopper_group_id     = $this->input->post->get('shopper_group_id', array(), 'array');
        $price                = $this->input->post->get('price', array(), 'array');
        $price_quantity_start = $this->input->post->get('price_quantity_start', array(), 'array');
        $price_quantity_end   = $this->input->post->get('price_quantity_end', array(), 'array');
        $price_id             = $this->input->post->get('price_id', array(), 'array');

        for ($i = 0; $i < count($price); $i++)
        {
            $sql = "SELECT count(*) FROM  #__redshop_product_attribute_price  WHERE section_id='" . $section_id . "' AND section= '" . $section . "' AND price_id = '" . $price_id[$i] . "' AND shopper_group_id = '" . $shopper_group_id[$i] . "' ";
            $db->setQuery($sql);

            if ($db->loadResult())
            {

                $query = 'SELECT price_id FROM #__redshop_product_attribute_price WHERE shopper_group_id = "' . $shopper_group_id[$i] . '" AND section_id="' . $section_id . '" AND section= "' . $section . '" AND price_quantity_end >= ' . $price_quantity_start[$i] . ' AND price_quantity_start <=' . $price_quantity_start[$i];
                $db->setQuery($query);
                $xid = intval($db->loadResult());
                if ($xid && $xid != intval($price_id[$i]))
                {
                    echo $xid;

                    $this->_error = JText::sprintf('WARNNAMETRYAGAIN', JText::_('COM_REDSHOP_PRICE_ALREADY_EXISTS'));
                    //return false;
                }
                if ($price[$i] != '')
                {

                    $sql = "UPDATE #__redshop_product_attribute_price  SET product_price='" . $price[$i] . "' ," . " price_quantity_start = '" . $price_quantity_start[$i] . "', price_quantity_end = '" . $price_quantity_end[$i] . "' " . " WHERE section_id='" . $section_id . "' AND section= '" . $section . "' AND price_id = '" . $price_id[$i] . "' AND shopper_group_id = '" . $shopper_group_id[$i] . "' ";
                }
                else
                {

                    $sql = "DELETE FROM  #__redshop_product_attribute_price   WHERE section_id='" . $section_id . "' AND section= '" . $section . "' AND price_id = '" . $price_id[$i] . "' AND shopper_group_id = '" . $shopper_group_id[$i] . "' ";
                }
            }
            elseif ($price[$i] != '')
            {

                $sql = "INSERT INTO  #__redshop_product_attribute_price  SET product_price='" . $price[$i] . "', price_quantity_start = '" . $price_quantity_start[$i] . "' , price_quantity_end = '" . $price_quantity_end[$i] . "' , section_id='" . $section_id . "' , section = '" . $section . "' , shopper_group_id = '" . $shopper_group_id[$i] . "' ";
            }

            $db->setQuery($sql);
            $db->Query();
        }

        $link = "index.php?tmpl=component&option=com_redshop&view=product_attribute_price&section_id=" . $section_id . "&cid=" . $cid . "&section=" . $section;
        $this->setRedirect($link);
    }
}
