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

class attributeprices_detailController extends RedshopCoreController
{
    public function __construct($default = array())
    {
        parent::__construct($default);
        $this->registerTask('add', 'edit');
    }

    public function edit()
    {
        $this->input->set('view', 'attributeprices_detail');
        $this->input->set('layout', 'default');
        $this->input->set('hidemainmenu', 1);

        parent::display();
    }

    public function save()
    {
        $post       = $this->input->get('post');
        $option     = $this->input->get('option');
        $section_id = $this->input->get('section_id');
        $section    = $this->input->get('section');

        $post['product_currency']    = CURRENCY_CODE;
        $post['cdate']               = time();
        $post['discount_start_date'] = strtotime($post ['discount_start_date']);

        if ($post['discount_end_date'])
        {
            $post ['discount_end_date'] = strtotime($post['discount_end_date']) + (23 * 59 * 59);
        }

        $cid               = $this->input->post->get('cid', array(0), 'array');
        $post ['price_id'] = $cid [0];

        $model = $this->getModel('attributeprices_detail');

        if ($model->store($post))
        {
            $msg = JText::_('COM_REDSHOP_PRICE_DETAIL_SAVED');
        }
        else
        {
            $msg = JText::_('COM_REDSHOP_ERROR_SAVING_PRICE_DETAIL');
        }

        $this->setRedirect('index.php?tmpl=component&option=' . $option . '&view=attributeprices&section=' . $section . '&section_id=' . $section_id, $msg);
    }

    public function remove()
    {
        $option     = $this->input->get('option');
        $section_id = $this->input->get('section_id');
        $section    = $this->input->get('section');
        $cid        = $this->input->post->get('cid', array(0), 'array');

        if (!is_array($cid) || count($cid) < 1)
        {
            JError::raiseError(500, JText::_('COM_REDSHOP_SELECT_AN_ITEM_TO_DELETE'));
        }

        $model = $this->getModel('attributeprices_detail');

        if (!$model->delete($cid))
        {
            echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
        }

        $msg = JText::_('COM_REDSHOP_ATTRIBUTE_PRICE_DETAIL_DELETED_SUCCESSFULLY');
        $this->setRedirect('index.php?tmpl=component&option=' . $option . '&view=attributeprices&section=' . $section . '&section_id=' . $section_id, $msg);
    }

    public function cancel()
    {
        $option     = $this->input->get('option');
        $section_id = $this->input->get('section_id');

        $msg = JText::_('COM_REDSHOP_PRICE_DETAIL_EDITING_CANCELLED');
        $this->setRedirect('index.php?option=' . $option . '&view=attributeprices&section_id=' . $section_id, $msg);
    }
}
