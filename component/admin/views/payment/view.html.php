<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.view');

class paymentViewpayment extends JView
{
	function __construct($config = array())
	{
		parent::__construct($config);
	}

	function display($tpl = null)
	{
		global $mainframe, $context;

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('COM_REDSHOP_PAYMENTS'));

		JToolBarHelper::title(JText::_('COM_REDSHOP_PAYMENT_MANAGEMENT'), 'redshop_payment48');

		JToolBarHelper::addNewX();
		JToolBarHelper::editListX();
		//JToolBarHelper::customX( 'copy', 'copy.png', 'copy_f2.png', 'Copy', true );
		JToolBarHelper::deleteList();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();


		$uri =& JFactory::getURI();
		$context = 'payment';
		$filter_order = $mainframe->getUserStateFromRequest($context . 'filter_order', 'filter_order', 'ordering');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', '');

		$payment_section = $mainframe->getUserStateFromRequest($context . 'payment_section', 'payment_section', 0);

		$lists['order'] = $filter_order;
		$lists['order_Dir'] = $filter_order_Dir;
		$payments = & $this->get('Data');
		$total = & $this->get('Total');
		$pagination = & $this->get('Pagination');

		$this->assignRef('lists', $lists);
		$this->assignRef('payments', $payments);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('request_url', $uri->toString());
		parent::display($tpl);
	}
}
?>
