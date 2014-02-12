<?php
/**
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\Model;

use Joomla\Factory;
use Joomla\Filter\InputFilter;
use Joomla\Registry\Registry;
use Joomla\String\String;
use App\Table\NewsTable;
use App\Model\DefaultModel;

/**
 * Model to get data for the news articles
 *
 * @since  1.0
 */
class NewsModel extends DefaultModel
{
	/**
	 * Retrieve a single news item
	 *
	 * @return  object  News item
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException
	 */
	public function getItem()
	{
		$input = Factory::$application->input;
		$id    = $input->getUint('id');
		$alias = $input->get('id');

		if (!$id && !$alias)
		{
			throw new \UnexpectedValueException('No news identifier provided.');
		}

		$query = $this->db->getQuery(true)
			->select('a.*')
			->from($this->db->quoteName('#__news','a'));

		if ($id)
		{
			$query->where($this->db->quoteName('a.news_id') . ' = ' . (int) $id);
		}
		elseif ($alias)
		{
			$query->where($this->db->quoteName('a.alias') . ' = ' . $this->db->quote($alias));
		}

		return $this->db->setQuery($query)->loadObject();
	}

	/**
	 * Retrieve all news items
	 *
	 * @return  object  Container with news items
	 *
	 * @since   1.0
	 */
	public function getItems()
	{
		$query = $this->db->getQuery(true)
			->select('a.*')
			->from($this->db->quoteName('#__news','a'));

		return $this->db->setQuery($query)->loadObjectList();
	}

	/**
	 * Save the item.
	 *
	 * @param   array  $src  The source.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function save(array $src)
	{
		$filter = new InputFilter;

		$data = array();

		$data['news_id'] = $filter->clean($src['news_id'], 'uint');

		$data['title']          = $filter->clean($src['title'], 'string');
		$data['raw_body']       = $filter->clean($src['raw_body'], 'string');
		$data['formatted_body'] = Factory::$application->getGithub()->markdown->render($data['raw_body'], 'markdown');

		$table = new NewsTable($this->db);

		$table->load($data['id'])->save($data);

		return $this;
	}
}
