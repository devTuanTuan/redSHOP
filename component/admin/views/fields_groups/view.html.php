<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Fields groups view
 *
 * @package     RedSHOP.Backend
 * @subpackage  View
 * @since       __DEPLOY_VERSION__
 */

class RedshopViewFields_groups extends RedshopViewList
{
	/**
	 * Display check-in button or not.
	 *
	 * @var   boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $checkIn = false;

	/**
	 * Method for render 'Published' column
	 *
	 * @param   array   $config  Row config.
	 * @param   int     $index   Row index.
	 * @param   object  $row     Row data.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onRenderColumn($config, $index, $row)
	{
		switch ($config['dataCol'])
		{
			case 'section':
				return (string) RedshopHelperTemplate::getFieldSections($row->section);
		}

		return parent::onRenderColumn($config, $index, $row);
	}
}
