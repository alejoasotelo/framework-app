<?php
/**
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\Controller;

use Joomla\Controller\AbstractController;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\Container;

use App\View\DefaultHtmlView;

/**
 * Default Controller class for the application
 *
 * @since  1.0
 */
class DefaultController extends AbstractController implements ContainerAwareInterface
{
	/**
	 * DI Container
	 *
	 * @var    Container
	 * @since  1.0
	 */
	private $container;

	/**
	 * The default view for the app
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $defaultView = 'dashboard';

	public function add()
	{
		//~ if (!$this->getApplication()->getUser()->id)
		//~ {
			//~ $this->getApplication()->enqueueMessage('Must login first', 'error');
			//~ $this->getApplication()->redirect($this->getApplication()->get('uri.base.path') . 'news');
		//~ }

		$this->getInput()->set('layout', 'edit');
	}
	
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
		$mClass = $this->getModelName();


		// Make sure the view class exists, otherwise revert to the default
		$vClass = class_exists($vClass) ? $vClass : $defaultvClass;
			
		// If there still isn't a class, panic.
		if (!class_exists($vClass))
		{
			throw new \RuntimeException(sprintf('Class %s not found', $vClass));
		}

		// Register the templates paths for the view
		$paths = array();

		$path = JPATH_TEMPLATES . '/' . $vName . '/';

		if (is_dir($path))
		{
			$paths[] = $path;
		}

		$view = new $vClass($this->getApplication(), new $mClass($this->getInput(), $this->getContainer()->get('db')), $paths);

		if ($vFormat != 'html') {
			return $view->render();
		}

		$view->setLayout($vName . '.' . $lName);

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

	public function edit()
	{
		$this->getInput()->set('layout', 'edit');
	}

	public function save()
	{
		$mClass = $this->getModelName();
		$model = new $mClass($this->getInput(), $this->getContainer()->get('db'));
		if (!$model->save()) {
			$this->getApplication()->enqueueMessage('Could not save', 'error');
			$this->getApplication()->redirect($this->getApplication()->get('uri.base.path') . 'news/add');
		}
		
		$this->getApplication()->enqueueMessage('Saved Successfully', 'info');
		$this->getApplication()->redirect($this->getApplication()->get('uri.base.path') . 'news');
	}
		
	/**
	 * Get the DI container.
	 *
	 * @return  Container
	 *
	 * @since   1.0
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Set the DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   1.0
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;

		return $this;
	}
	
	public function getModelName() {
		$vName   = $this->getInput()->getWord('view', $this->defaultView);

		$base = '\\App';
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
		
		return $mClass;		
	}
}
