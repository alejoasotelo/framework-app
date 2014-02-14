<?php
/**
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\View;

use App\Authentication\GitHub\GitHubLoginHelper;
use App\View\Renderer\TwigExtension;

use Joomla\Factory;
use Joomla\Model\ModelInterface;
use Joomla\View\AbstractView;
use Joomla\View\Renderer\RendererInterface;


/**
 * Default JSON view class for the application
 *
 * @since  1.0
 */
class DefaultJsonView extends AbstractView
{
	/**
	 * The model object.
	 *
	 * @var    NewsModel
	 * @since  1.0
	 */
	protected $model;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function render()
	{
		$task = Factory::$application->input->get('task');

		switch ($task)
		{
			case 'view':
			case 'edit':
				$op = $this->model->getItem();
				break;

			default:
				$op = $this->model->getItems();
				break;
		}

		echo json_encode($op);
	}
}
