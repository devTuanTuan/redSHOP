<?php
/**
 * @package     redSHOP
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2008 - 2012 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

class Tableusercart_item extends JTable
{
    public $cart_item_id = 0;

    public $cart_idx = 0;

    public $cart_id = 0;

    public $product_id = 0;

    public $product_quantity = 0;

    public $product_wrapper_id = 0;

    public $product_subscription_id = 0;

    public $giftcard_id = 0;

    public function __construct(& $db)
    {
        $this->_table_prefix = '#__redshop_';

        parent::__construct($this->_table_prefix . 'usercart_item', 'cart_item_id', $db);
    }
}
