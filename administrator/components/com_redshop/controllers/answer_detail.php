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

class answer_detailController extends RedshopCoreController
{
    public function __construct($default = array())
    {
        parent::__construct($default);
        $this->registerTask('add', 'edit');
    }

    public function edit()
    {
        $this->input->set('view', 'answer_detail');
        $this->input->set('layout', 'default');
        $this->input->set('hidemainmenu', 1);

        parent::display();
    }

    public function save($send = 0)
    {
        $post      = $this->input->get('post');
        $question  = $this->input->post->getString('question', '');
        $option    = $this->input->getString('option', '');
        $cid       = $this->input->post->get('cid', array(0), 'array');
        $parent_id = $this->input->get('parent_id');

        $post["question"]    = $question;
        $post['question_id'] = $cid [0];

        $model = $this->getModel('answer_detail');

        if ($post['question_id'] == 0)
        {
            $post['question_date'] = time();
        }

        $row = $model->store($post);

        if ($row)
        {
            $msg = JText::_('COM_REDSHOP_ANSWER_DETAIL_SAVED');
        }

        else
        {
            $msg = JText::_('COM_REDSHOP_ERROR_SAVING_ANSWER_DETAIL');
        }

        if ($send == 1)
        {
            $model->sendMailForAskQuestion($row->question_id);
        }

        $this->setRedirect('index.php?option=' . $option . '&view=answer&parent_id=' . $parent_id, $msg);
    }

    public function send()
    {
        $this->save(1);
    }

    public function remove()
    {
        $parent_id = $this->input->get('parent_id');
        $option    = $this->input->getString('option', '');
        $cid       = $this->input->post->get('cid', array(0), 'array');

        if (!is_array($cid) || count($cid) < 1)
        {
            JError::raiseError(500, JText::_('COM_REDSHOP_SELECT_AN_ITEM_TO_DELETE'));
        }

        $model = $this->getModel('answer_detail');

        if (!$model->delete($cid))
        {
            echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
        }

        $msg = JText::_('COM_REDSHOP_ANSWER_DETAIL_DELETED_SUCCESSFULLY');
        $this->setRedirect('index.php?option=' . $option . '&view=answer&parent_id=' . $parent_id, $msg);
    }

    public function cancel()
    {
        $parent_id = $this->input->get('parent_id');
        $option    = $this->input->getString('option', '');

        $msg = JText::_('COM_REDSHOP_ANSWER_DETAIL_EDITING_CANCELLED');
        $this->setRedirect('index.php?option=' . $option . '&view=answer&parent_id=' . $parent_id, $msg);
    }

    public function publish()
    {
        $option    = $this->input->getString('option', '');
        $parent_id = $this->input->get('parent_id');
        $cid       = $this->input->post->get('cid', array(0), 'array');

        if (!is_array($cid) || count($cid) < 1)
        {
            JError::raiseError(500, JText::_('COM_REDSHOP_SELECT_AN_ITEM_TO_PUBLISH'));
        }

        $model = $this->getModel('answer_detail');

        if (!$model->publish($cid, 1))
        {
            echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
        }

        $msg = JText::_('COM_REDSHOP_ANSWER_DETAIL_PUBLISHED_SUCCESSFULLY');
        $this->setRedirect('index.php?option=' . $option . '&view=answer&parent_id=' . $parent_id, $msg);
    }

    public function unpublish()
    {
        $option    = $this->input->get('option');
        $parent_id = $this->input->get('parent_id');
        $cid       = $this->input->post->get('cid', array(0), 'array');

        if (!is_array($cid) || count($cid) < 1)
        {
            JError::raiseError(500, JText::_('COM_REDSHOP_SELECT_AN_ITEM_TO_UNPUBLISH'));
        }

        $model = $this->getModel('answer_detail');

        if (!$model->publish($cid, 0))
        {
            echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
        }

        $msg = JText::_('COM_REDSHOP_ANSWER_DETAIL_UNPUBLISHED_SUCCESSFULLY');
        $this->setRedirect('index.php?option=' . $option . '&view=answer&parent_id=' . $parent_id, $msg);
    }

    /**
     * logic for orderup
     *
     * @access public
     * @return void
     */
    public function orderup()
    {
        $parent_id = $this->input->get('parent_id');
        $option    = $this->input->get('option');

        $model = $this->getModel('answer_detail');
        $model->orderup();
        $msg = JText::_('COM_REDSHOP_NEW_ORDERING_SAVED');
        $this->setRedirect('index.php?option=' . $option . '&view=answer&parent_id=' . $parent_id, $msg);
    }

    /**
     * logic for orderdown
     *
     * @access public
     * @return void
     */
    public function orderdown()
    {
        $parent_id = $this->input->get('parent_id');
        $option    = $this->input->get('option');

        $model = $this->getModel('answer_detail');
        $model->orderdown();

        $msg = JText::_('COM_REDSHOP_NEW_ORDERING_SAVED');
        $this->setRedirect('index.php?option=' . $option . '&view=answer&parent_id=' . $parent_id, $msg);
    }

    /**
     * logic for save an order
     *
     * @access public
     * @return void
     */
    public function saveorder()
    {
        $parent_id = $this->input->get('parent_id');
        $option    = $this->input->get('option');
        $cid       = $this->input->post->get('cid', array(0), 'array');
        $order     = $this->input->post->get('order', array(), 'array');

        JArrayHelper::toInteger($cid);
        JArrayHelper::toInteger($order);

        $model = $this->getModel('answer_detail');
        $model->saveorder($cid, $order);

        $msg = JText::_('COM_REDSHOP_ORDERING_SAVED');
        $this->setRedirect('index.php?option=' . $option . '&view=answer&parent_id=' . $parent_id, $msg);
    }
}
