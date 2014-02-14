<?php
/**
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\Controller;

use Joomla\Controller\AbstractController;

use App\View\DefaultHtmlView;

/**
 * Default Controller class for the application
 *
 * @since  1.0
 */
class DefaultController extends AbstractController
{
	/**
	 * The default view for the app
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $defaultView = 'dashboard';

	/**
	 * Execute the controller.
	 *
	 * This is a generic method to execute and render a view and is not suitable for tasks.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		// Get the input
		$input = $this->getInput();

		$task = $input->get('task', 'view');

		// Get some data from the request
		$vName   = $input->getWord('view', $this->defaultView);
		$vFormat = $input->getWord('format', 'html');

		//TODO
		if (is_null($input->get('layout')))
		{
			if ($task == 'view' && $input->get('id') == null)
			{
				$input->set('layout', 'index');
			}
			elseif ($task == 'view')
			{
				$input->set('layout', 'view');
			}
			elseif ($task != null)
			{
				$this->$task();
			}
		}

		$lName = $input->get('layout');

		$input->set('view', $vName);

		$base = '\\App';


		$defaultvClass = $base . '\\View\\Default' . ucfirst($vFormat) . 'View';
		$vClass = $base . '\\View\\' . ucfirst($vName) . '\\' . ucfirst($vName) . ucfirst($vFormat) . 'View';
		$mClass = $base . '\\Model\\' . ucfirst($vName) . 'Model';

		// If a model doesn't exist for our view, revert to the default model
		if (!class_exists($mClass))
		{
			$mClass = $base . '\\Model\\DefaultModel';

			// If there still isn't a class, panic.
			if (!class_exists($mClass))
			{
				throw new \RuntimeException(sprintf('No model found for view %s', $vName));
			}
		}

		// Make sure the view class exists, otherwise revert to the default
		if (!class_exists($vClass))
		{
			$vClass = $defaultvClass;

			// If there still isn't a class, panic.
			if (!class_exists($vClass))
			{
				throw new \RuntimeException(sprintf('Class %s not found', $vClass));
			}
		}

		// Register the templates paths for the view
		$paths = array();

		$path = JPATH_TEMPLATES . '/' . $vName . '/';

		if (is_dir($path))
		{
			$paths[] = $path;
		}

		/* @type DefaultHtmlView $view */
		$view = new $vClass(new $mClass, $paths);
		if ('html' == $vFormat)
		{
			$view->setLayout($vName . '.' . $lName);
		}

		try
		{
			// Render our view.
			return $view->render();
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException(sprintf('Error: ' . $e->getMessage()));
		}

		return;
	}
}
