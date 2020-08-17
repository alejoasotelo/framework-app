<?php
/**
 * @copyright  Copyright (C) 2012 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\View\Dashboard;

use App\View\DefaultHtmlView;

/**
 * Dashboard HTML view class for the application.
 *
 * @since  1.0
 */
class DashboardHtmlView extends DefaultHtmlView
{
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
        $this->renderer->set('success', $this->app->input->get('success', false));
        $this->renderer->set('logo', DEFAULT_THEME.'/images/logo.png');
        $this->renderer->set('config', $this->app->getContainer()->get('config'));

        return parent::render();
    }
}
