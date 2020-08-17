<?php
/**
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\Controller;

use App\Model\DashboardModel;

/**
 * Dashboard Controller class for the Application.
 *
 * @since  1.0
 */
class DashboardController extends DefaultController
{
    /**
     * Task to update the database configuration and install sample data.
     *
     * @return void
     *
     * @since   1.0
     *
     * @throws \RuntimeException
     */
    public function updateDatabase()
    {
        try {
            $dashboardModel = new DashboardModel($this->getInput(), $this->getContainer()->get('db'));
            $dashboardModel->updateDatabase($this->getContainer()->get('config'));

            $this->getInput()->set('success', true);

            $app = $this->getApplication();
            $app->enqueueMessage("Sweet! You've setup your database successfully. Check out the <a href=\"news\">Sample Page</a>", 'success');
            $app->redirect($app->get('uri.base.path').'dashboard?success=true');
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Error: '.$e->getMessage()));
        }
    }
}
