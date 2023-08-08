<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML View class
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewHome extends YireoViewHome
{
    /**
     * @var string[]
     */
    protected $icons;

    /**
     * @var array
     */
    protected $urls;

    /**
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        $icons = [];
        $icons[] = $this->icon('config', 'COM_MAGEBRIDGE_VIEW_CONFIG', 'config.png');
        $icons[] = $this->icon('stores', 'COM_MAGEBRIDGE_VIEW_STORES', 'store.png');
        $icons[] = $this->icon('products', 'COM_MAGEBRIDGE_VIEW_PRODUCTS', 'product.png');
        $icons[] = $this->icon('users', 'COM_MAGEBRIDGE_VIEW_USERS', 'user.png');
        $icons[] = $this->icon('check', 'COM_MAGEBRIDGE_VIEW_CHECK', 'cpanel.png');
        $icons[] = $this->icon('logs', 'COM_MAGEBRIDGE_VIEW_LOGS', 'info.png');
        $icons[] = $this->icon('cache', 'COM_MAGEBRIDGE_CLEAN_CACHE', 'trash.png');
        $icons[] = $this->icon('magento', 'COM_MAGEBRIDGE_MAGENTO_BACKEND', 'magento.png', null, '_blank');
        $this->icons = $icons;

        $urls = [];
        $urls['twitter'] ='http://twitter.com/yireo';
        $urls['facebook'] ='http://www.facebook.com/yireo';
        $this->urls = $urls;

        parent::display($tpl);
    }
}
