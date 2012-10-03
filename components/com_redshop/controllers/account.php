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

/**
 * accountController
 *
 * @package    Joomla.Site
 * @subpackage com_redshop
 *
 * Description N/A
 */
class accountController extends RedshopCoreController
{
    /**
     * Method to edit created Tag
     *
     */
    public function editTag()
    {
        $option  = $this->input->get('option');
        $item_id = $this->input->get('Itemid');
        $post    = $this->input->getArray($_POST);

        $model = $this->getModel('account');

        if ($model->editTag($post))
        {
            $this->app->enqueueMessage(JText::_('COM_REDSHOP_TAG_EDITED_SUCCESSFULLY'));
        }
        else
        {
            $this->app->enqueueMessage(JText::_('COM_REDSHOP_ERROR_EDITING_TAG'));
        }

        $this->setRedirect('index.php?option=' . $option . '&view=account&layout=mytags&Itemid=' . $item_id);
    }

    /**
     * Method to send created wishlist
     *
     */
    public function sendWishlist()
    {
        $post = $this->input->getArray($_POST);

        $emailto    = $post['emailto'];
        $sender     = $post['sender'];
        $email      = $post['email'];
        $subject    = $post['subject'];
        $item_id    = $post['Itemid'];
        $wishlis_id = $post['wishlist_id'];
        $model      = $this->getModel('account');

        if ($emailto == "")
        {
            $msg = JText::_('COM_REDSHOP_PLEASE_ENTER_EMAIL_TO');
        }
        else if ($sender == "")
        {
            $msg = JText::_('COM_REDSHOP_PLEASE_ENTER_SENDER_NAME');
        }
        else if ($email == "")
        {
            $msg = JText::_('COM_REDSHOP_PLEASE_ENTER_SENDER_EMAIL');
        }
        else if ($subject == "")
        {
            $msg = JText::_('COM_REDSHOP_PLEASE_ENTER_SUBJECT');
        }
        else if ($model->sendWishlist($post))
        {
            $msg = JText::_('COM_REDSHOP_SEND_SUCCESSFULLY');
        }
        else
        {
            $msg = JText::_('COM_REDSHOP_ERROR_SENDING');
        }

        $this->setRedirect('index.php?option=com_redshop&view=account&layout=mywishlist&mail=0&window=1&tmpl=component&wishlist_id=' . $wishlis_id . '&Itemid' . $item_id, $msg);
    }

    /*
      *  Method to subscribe newsletter
      */
    public function newsletterSubscribe()
    {
        $option  = $this->input->get('option');
        $item_id = $this->input->get('Itemid');

        $userhelper = new rsUserhelper();
        $userhelper->newsletterSubscribe(0, array(), 1);

        $msg = JText::_('COM_REDSHOP_SUBSCRIBE_SUCCESS');
        $this->setRedirect("index.php?option=" . $option . "&view=account&Itemid=" . $item_id, $msg);
    }

    /*
      *  Method to unsubscribe newsletter
      */
    public function newsletterUnsubscribe()
    {
        $user    = JFactory::getUser();
        $option  = $this->input->get('option');
        $item_id = $this->input->get('Itemid');

        $userhelper = new rsUserhelper();
        $userhelper->newsletterUnsubscribe($user->email);
        $msg = JText::_('COM_REDSHOP_CANCLE_SUBSCRIPTION');

        $this->setRedirect("index.php?option=" . $option . "&view=account&Itemid=" . $item_id, $msg);
    }
}
