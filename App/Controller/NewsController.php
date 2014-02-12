<?php
/**
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\Controller;

use App\App;
use App\Model\NewsModel;

use Joomla\Filter\InputFilter;

/**
 * News Controller class for the Application
 *
 * @since  1.0
 */
class NewsController extends DefaultController
{
	/**
	 * The default view for the app
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $defaultView = 'news';

	/**
	 * Edit task for news items
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function edit()
	{
		if (!$this->getApplication()->getUser()->id)
		{
			$this->getApplication()->enqueueMessage('Must login first', 'error');
			$this->getApplication()->redirect($this->getApplication()->get('uri.base.path') . 'news');
		}

		$this->getInput()->set('layout', 'edit');
	}

	/**
	 * Add task for news items
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function add()
	{
		if (!$this->getApplication()->getUser()->id)
		{
			$this->getApplication()->enqueueMessage('Must login first', 'error');
			$this->getApplication()->redirect($this->getApplication()->get('uri.base.path') . 'news');
		}

		$this->getInput()->set('layout', 'edit');
	}
	
	/**
	 * Preview input text in Markdown format
	 *
	 * @return  void  Calls exit()
	 *
	 * @since   1.0
	 */
	public function preview()
	{
		$response = new \stdClass;

		$response->data    = new \stdClass;
		$response->error   = '';
		$response->message = '';

		ob_start();

		try
		{
			// Only registered users are able to use the preview using their credentials.
			if (!$this->getApplication()->getUser()->id)
			{
				throw new \Exception('not auth..');
			}

			$text = $this->getInput()->get('text', '', 'raw');

			if (!$text)
			{
				throw new \Exception('Nothing to preview...');
			}

			$response->data = $this->getApplication()->getGitHub()->markdown->render($text, 'markdown');
		}
		catch (\Exception $e)
		{
			$response->error = $e->getMessage();
		}

		$errors = ob_get_clean();

		if ($errors)
		{
			$response->error .= $errors;
		}

		header('Content-type: application/json');

		echo json_encode($response);

		exit;
	}

	/**
	 * Saves news items
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function save()
	{
		/* @type App $app */
		$app = $this->getApplication();

		if (!$app->getUser()->id)
		{
			$app->enqueueMessage('Must login first', 'error');
			$app->redirect($this->getApplication()->get('uri.base.path') . 'news');
		}

		$src = $this->getInput()->get('item', array(), 'array');

		$filter = new InputFilter;
		$id     = $filter->clean($src['news_id'], 'uint');

		try
		{
			$model = new NewsModel;
			$model->save($src);

			$app->enqueueMessage('Item successfully saved!', 'success');
			$app->redirect($app->get('uri.base.path') . 'news/view/' . $id);
		}
		catch (\Exception $e)
		{
			$app->enqueueMessage('Save failed - ' . $e->getMessage(), 'error');
			$app->redirect($app->get('uri.base.path') . 'news/view/' . $id);
		}
	}
}
