<?php
/**
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App;

use App\Router\AppRouter;
use App\Authentication\GitHub\GitHubUser;
use App\Authentication\User;

use Joomla\Application\AbstractWebApplication;
use Joomla\Controller\ControllerInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Factory;
use Joomla\Github\Github;
use Joomla\Github\Http;
use Joomla\Http\HttpFactory;
use Joomla\Registry\Registry;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Application class
 *
 * @since  1.0
 */
final class App extends AbstractWebApplication
{
	/**
	 * The default theme.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $theme = null;

	/**
	 * A session object.
	 *
	 * @var    Session
	 * @since  1.0
	 * @note   This has been created to avoid a conflict with the $session member var from the parent class.
	 */
	private $newSession = null;

	/**
	 * The database driver object.
	 *
	 * @var    DatabaseDriver
	 * @since  1.0
	 */
	private $database;

	/**
	 * The Language object
	 *
	 * @var    Language
	 * @since  1.0
	 */
	private $language;

	/**
	 * The GitHub object
	 *
	 * @var    Github
	 * @since  1.0
	 */
	private $github;

	/**
	 * The GitHub object
	 *
	 * @var    User
	 * @since  1.0
	 */
	private $user;

	/**
	 * Class constructor.
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		// Run the parent constructor
		parent::__construct();

		// Load the configuration object.
		$this->loadConfiguration();

		// Register the application to Factory
		// @todo Decouple from Factory
		Factory::$application = $this;
		Factory::$config = $this->config;

		$this->theme = $this->config->get('theme.default');

		define('BASE_URL', $this->get('uri.base.full'));
		define('DEFAULT_THEME', BASE_URL . 'themes/' . $this->theme);
		
		error_reporting(constant($this->config->get('system.errorlevel', 0)));
		ini_set('display_errors', $this->config->get('system.errordisplay', 'Off'));
	}

	/**
	 * Method to run the Web application routines.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function doExecute()
	{
		try
		{
			// Instantiate the router
			$router = new AppRouter($this->input, $this);
			$maps = json_decode(file_get_contents(JPATH_CONFIGURATION . '/routes.json'));

			if (!$maps)
			{
				throw new \RuntimeException('Invalid router file.', 500);
			}

			$router->addMaps($maps, true);
			$router->setControllerPrefix('\\App');
			$router->setDefaultController('\\Controller\\DefaultController');

			// Fetch the controller
			/* @type ControllerInterface $controller */
			$controller = $router->getController($this->get('uri.route'));
			$content = $controller->execute();
		}
		catch (\Exception $exception)
		{
			header('HTTP/1.1 500 Internal Server Error', true, 500);

			$content = $exception->getMessage();
		}

		$this->setBody($content);
	}

	/**
	 * Initialize the configuration object.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function loadConfiguration()
	{
		// Set the configuration file path for the application.
		$file = JPATH_CONFIGURATION . '/config.json';

		// Verify the configuration exists and is readable.
		if (!is_readable($file))
		{
			throw new \RuntimeException('Configuration file does not exist or is unreadable.');
		}

		// Load the configuration file into an object.
		$config = json_decode(file_get_contents($file));

		if ($config === null)
		{
			throw new \RuntimeException(sprintf('Unable to parse the configuration file %s.', $file));
		}

		$this->config->loadObject($config);

		return $this;
	}

	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type. Default is message.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   1.0
	 */
	public function enqueueMessage($msg, $type = 'message')
	{
		$this->getSession()->getFlashBag()->add($type, $msg);

		return $this;
	}

	/**
	 * Get a GitHub object.
	 *
	 * @return  Github
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getGitHub()
	{
		if (!$this->github)
		{
			$options = new Registry;

			$token = $this->getSession()->get('gh_oauth_access_token');

			if ($token)
			{
				$options->set('gh.token', $token);
			}
			else
			{
				$options->set('api.username', $this->get('github.username'));
				$options->set('api.password', $this->get('github.password'));
			}

			// GitHub API works best with cURL
			$transport = HttpFactory::getAvailableDriver($options, array('curl'));

			$http = new Http($options, $transport);

			// Instantiate Github
			$this->github = new Github($options, $http);
		}

		return $this->github;
	}

	/**
	 * Get a session object.
	 *
	 * @return  Session
	 *
	 * @since   1.0
	 */
	public function getSession()
	{
		if (is_null($this->newSession))
		{
			$this->newSession = new Session;
			$this->newSession->start();

			// @todo Decouple from Factory
			Factory::$session = $this->newSession;
		}

		return $this->newSession;
	}

	/**
	 * Get a user object.
	 *
	 * @param   integer  $id  The user id or the current user.
	 *
	 * @return  User
	 *
	 * @since   1.0
	 */
	public function getUser($id = 0)
	{
		if ($id)
		{
			return new GitHubUser($id);
		}

		if (is_null($this->user))
		{
			$this->user = ($this->getSession()->get('user')) ? : new GitHubUser;
		}

		return $this->user;
	}

	/**
	 * Login or logout a user.
	 *
	 * @param   User  $user  The user object.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   1.0
	 */
	public function setUser(User $user = null)
	{
		if (is_null($user))
		{
			// Logout
			$this->user = new GitHubUser;
		}
		else
		{
			// Login
			$this->user = $user;
		}

		$this->getSession()->set('user', $this->user);

		return $this;
	}

	/**
	 * Get a database driver object.
	 *
	 * @return  DatabaseDriver
	 *
	 * @since   1.0
	 */
	public function getDatabase()
	{
		if (is_null($this->database))
		{
			$this->database = DatabaseDriver::getInstance(
				array(
					'driver' => $this->get('database.driver'),
					'host' => $this->get('database.host'),
					'user' => $this->get('database.user'),
					'password' => $this->get('database.password'),
					'database' => $this->get('database.name'),
					'prefix' => $this->get('database.prefix')
				)
			);

			// @todo Decouple from Factory
			Factory::$database = $this->database;
		}

		return $this->database;
	}

	/**
	 * Clear the system message queue.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function clearMessageQueue()
	{
		$this->getSession()->getFlashBag()->clear();
	}

	/**
	 * Get the system message queue.
	 *
	 * @return  array  The system message queue.
	 *
	 * @since   1.0
	 */
	public function getMessageQueue()
	{
		return $this->getSession()->getFlashBag()->peekAll();
	}

	/**
	 * Set the system message queue for a given type.
	 *
	 * @param   string  $type     The type of message to set
	 * @param   mixed   $message  Either a single message or an array of messages
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setMessageQueue($type, $message = '')
	{
		$this->getSession()->getFlashBag()->set($type, $message);
	}
}
