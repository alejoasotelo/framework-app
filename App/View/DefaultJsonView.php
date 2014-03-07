<?php
/**
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\View;

use App\App;

use Joomla\Model\ModelInterface;
use Joomla\View\AbstractView;

/**
 * Default view class for the application
 *
 * @since  1.0
 */
class DefaultJsonView extends AbstractView
{
	/**
	 * Application object
	 *
	 * @var    App
	 * @since  1.0
	 */
	protected $app;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   App             $app             The application object.
	 * @param   ModelInterface  $model           The model object.
	 * @param   string|array    $templatesPaths  The templates paths.
	 *
	 * @throws  \RuntimeException
	 * @since   1.0
	 */
	public function __construct(App $app, ModelInterface $model, $templatesPaths = '')
	{
		parent::__construct($model);

		$this->app = $app;
	}

	/**
	 * Magic toString method that is a proxy for the render method.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @see     ViewInterface::escape()
	 * @since   1.0
	 */
	public function escape($output)
	{
		// Escape the output.
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

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
		$task = $this->app->input->get('task');
		$callback = $this->app->input->get('callback', '');

		switch ($task)
		{
			case 'view':
			case 'edit':
				$op = $this->model->getItem();
				break;

			default:
				$total = $this->model->getTotal();
				$op = $this->model->getItems();
				$op['total']= $total;
				break;
		}
		
		$json = json_encode($op);
		if ($callback) {
			echo $callback . "({$json})";
		} else {
			echo $json;
		}
	}
}
