<?php
/**
 * @package     redSHOP
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2008 - 2012 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_COMPONENT_ADMINISTRATOR . DS . 'core' . DS . 'model.php';

class account_shiptoModelaccount_shipto extends RedshopCoreModel
{
    public function __construct()
    {
        parent::__construct();

        $infoid    = JRequest::getInt('infoid');
        $this->_id = $infoid;
    }

    public function &getData()
    {
        if (!$this->_loadData())
        {
            $this->_initData();
        }
        return $this->_data;
    }

    public function _initData()
    {
        if (empty($this->_data))
        {
            $detail                = new stdClass();
            $detail->users_info_id = 0;
            $detail->user_id       = 0;
            $detail->firstname     = null;
            $detail->lastname      = null;
            $detail->company_name  = null;
            $detail->address       = null;
            $detail->state_code    = null;
            $detail->country_code  = null;
            $detail->city          = null;
            $detail->zipcode       = null;
            $detail->phone         = 0;
            $this->_data           = $detail;
            return (boolean)$this->_data;
        }
        return true;
    }

    public function _loadData($users_info_id = 0)
    {
        if ($users_info_id)
        {
            $query = 'SELECT * FROM ' . $this->_table_prefix . 'users_info WHERE users_info_id="' . $users_info_id . '" ';
            $this->_db->setQuery($query);
            $list = $this->_db->loadObject();
            return $list;
        }
        if (empty($this->_data))
        {
            $query = 'SELECT * FROM ' . $this->_table_prefix . 'users_info WHERE users_info_id="' . $this->_id . '" ';
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
            return $this->_data;
        }
        return true;
    }

    public function delete($cid = array())
    {
        if (count($cid))
        {
            $cids  = implode(',', $cid);
            $query = 'DELETE FROM ' . $this->_table_prefix . 'users_info WHERE users_info_id IN ( ' . $cids . ' )';
            $this->_db->setQuery($query);
            if (!$this->_db->query())
            {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }

        return true;
    }

    public function store($post)
    {
        $userhelper = new rsUserhelper();

        $post['user_email'] = $post['email1'] = $post['email'];
        $reduser            = $userhelper->storeRedshopUserShipping($post);

        return $reduser;
    }
}
