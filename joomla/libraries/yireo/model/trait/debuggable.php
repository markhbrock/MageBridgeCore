<?php

/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory;

/**
 * Yireo Model Trait: Identifiable - allows models to have an ID
 *
 * @package Yireo
 */
trait YireoModelTraitDebuggable
{
    /**
     * @return bool
     */
    protected function allowDebug()
    {
        // Enable debugging
        if ($this->params->get('debug', 0) == 1) {
            return true;
        }

        if ($this->getConfig('debug')) {
            return true;
        }

        return false;
    }

    /**
     * Method to get a debug-message of the latest query
     *
     * @return string
     */
    public function getDbDebug()
    {
        $db = Factory::getDbo();

        return '<pre>' . str_replace('#__', $db->getPrefix(), $db->getQuery()) . '</pre>';
    }
}
