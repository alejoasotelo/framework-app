<?php
/**
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\Model;

use Joomla\Input\Input;
use Joomla\Model\AbstractDatabaseModel;
use Joomla\Database\DatabaseDriver;

/**
 * Default model for the application.
 *
 * @since  1.0
 */
class DefaultModel extends AbstractDatabaseModel
{
	/**
	 * Model name
	 *
	 * @var    Container
	 * @since  1.0
	 */
	public $model = 'default';

	/**
	 * Table object
	 *
	 * @var    Container
	 * @since  1.0
	 */
	public $table;

	/**
	 * Input object
	 *
	 * @var    Input
	 * @since  1.0
	 */
	protected $input;

	/**
	 * Instantiate the model.
	 *
	 * @param   Input           $input  Input object.
	 * @param   DatabaseDriver  $db     The database adapter.
	 * @param   Registry        $state  The model state.
	 *
	 * @since   1.0
	 */
	public function __construct(Input $input, DatabaseDriver $db, Registry $state = null)
	{
		parent::__construct($db);

		$this->input	= $input;
		$this->db		= $db;
		$this->table	= '\\App\Table\\' . ucfirst($this->model) . 'Table';
	}
	
	public function save() {
		$ignore_fields = array ('_rawRoute','task');
		if (!class_exists($this->table)) {
			return $this;
		}
		$table = new $this->table($this->db);
		$table->save($this->input->getArray(), $ignore_fields);
		return $this;
	}	

	public function load($keys = null, $reset = true) {
		if (!class_exists($this->table)) {
			return $this;
		}
		$table = new $this->table($this->db);
		$table->load($keys);
		
	}	
}
