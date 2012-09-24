<?php
/**
 * @package     redSHOP
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2008 - 2012 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'helpers' . DS . 'extra_field.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'helpers' . DS . 'order.php');

class addquotation_detailVIEWaddquotation_detail extends JViewLegacy
{
    function display($tpl = null)
    {
        $option          = JRequest::getVar('option');
        $extra_field     = new extra_field();
        $order_functions = new order_functions();
        $document        = JFactory::getDocument();
        $document->setTitle(JText::_('COM_REDSHOP_QUOTATION_MANAGEMENT'));

        $document->addScript('components/' . $option . '/assets/js/json.js');
        $document->addScript('components/' . $option . '/assets/js/validation.js');
        $document->addScript('components/' . $option . '/assets/js/order.js');
        $document->addScript('components/' . $option . '/assets/js/common.js');
        $document->addScript('components/' . $option . '/assets/js/select_sort.js');
        $document->addStyleSheet('components/' . $option . '/assets/css/search.css');
        $document->addScript('components/' . $option . '/assets/js/search.js');
        $session          = JFactory::getSession();
        $uri              = JFactory::getURI();
        $lists            = array();
        $model            = $this->getModel();
        $detail           = $this->get('data');
        $Redconfiguration = new Redconfiguration();

        $user_id = JRequest::getVar('user_id', 0);
        if ($user_id != 0)
        {
            $billing = $order_functions->getBillingAddress($user_id);
        }
        else
        {
            $billing = $model->setBilling();
        }

        $detail          = is_object($detail) ? $detail : new stdClass;
        $detail->user_id = $user_id;

        $session->set('offlineuser_id', $user_id);

        $userop             = array();
        $userop[0]          = new stdClass;
        $userop[0]->user_id = 0;
        $userop[0]->text    = JText::_('COM_REDSHOP_SELECT');
        $userlists          = $model->getUserData(0, "BT");
        $userlist           = array_merge($userop, $userlists);
        $lists['userlist']  = JHTML::_('select.genericlist', $userlist, 'user_id', 'class="inputbox" onchange="showquotationUserDetail();" ', 'user_id', 'text', $user_id);

        JToolBarHelper::title(JText::_('COM_REDSHOP_QUOTATION_MANAGEMENT') . ': <small><small>[ ' . JText::_('COM_REDSHOP_NEW') . ' ]</small></small>', 'redshop_order48');

        JToolBarHelper::save();
        JToolBarHelper::custom('send', 'send.png', 'send.png', JText::_('COM_REDSHOP_SEND'), false);
        JToolBarHelper::cancel();

        // PRODUCT/ATTRIBUTE STOCK ROOM QUANTITY CHECKING IS IMPLEMENTED

        $countryarray                  = $Redconfiguration->getCountryList((array)$billing);
        $billing->country_code         = $countryarray['country_code'];
        $lists['country_code']         = $countryarray['country_dropdown'];
        $statearray                    = $Redconfiguration->getStateList((array)$billing);
        $lists['state_code']           = $statearray['state_dropdown'];
        $lists['quotation_extrafield'] = $extra_field->list_all_field(16, $billing->users_info_id);

        $this->assignRef('lists', $lists);
        $this->assignRef('detail', $detail);
        $this->assignRef('billing', $billing);
        $this->assignRef('userlist', $userlists);
        $this->request_url = $uri->toString();

        parent::display($tpl);
    }
}

