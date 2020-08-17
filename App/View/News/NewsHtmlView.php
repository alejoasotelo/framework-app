<?php
/**
 * @copyright  Copyright (C) 2012 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\View\News;

use App\Model\NewsModel;
use App\View\DefaultHtmlView;

/**
 * News HTML view class for the application.
 *
 * @since  1.0
 */
class NewsHtmlView extends DefaultHtmlView
{
    /**
     * The model object.
     *
     * @var NewsModel
     *
     * @since  1.0
     */
    protected $model;

    /**
     * Method to render the view.
     *
     * @return string the rendered view
     *
     * @since   1.0
     *
     * @throws \RuntimeException
     */
    public function render()
    {
        switch ($this->getLayout()) {
            case 'news.view':
            case 'news.edit':
                // Get the input
                if ($this->app->input->get('task') != 'add') {
                    $item = $this->model->getItem();
                    $this->renderer->set('item', $item);
                }

                break;

            case 'news.add':
                $this->setLayout('news.edit');

                break;

            default:
                $items = $this->model->getItems();
                $this->renderer->set('items', $items);

                if (count($items) >= 1) {
                    $this->enqueueMessage("You've setup your database! Below are dynamic results.", 'primary');
                } else {
                    $this->enqueueMessage('Here you see a sample page layout. Ideally this would pull articles from the database.', 'info');
                }

                break;
        }

        return parent::render();
    }
}
