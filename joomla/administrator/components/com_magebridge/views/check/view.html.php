<?php

/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML View class
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewCheck extends YireoCommonView
{
    /**
     * @var bool
     */
    protected $loadToolbar = false;

    /**
     * List of all checks
     */
    public $checks = [];

    /**
     * Display method
     *
     * @param string $tpl
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->setMenu();

        $input = Factory::getApplication()->input;

        if ($input->getCmd('layout') == 'browser') {
            $this->displayBrowser($tpl);

            return;
        }

        if ($input->getCmd('layout') == 'product') {
            $this->displayProduct($tpl);

            return;
        }

        if ($input->getCmd('layout') == 'result') {
            $this->displayResult($tpl);

            return;
        }

        $this->displayDefault($tpl);

        return;
    }

    /**
     * Display method
     *
     * @param string $tpl
     */
    public function displayDefault($tpl)
    {
        // Initialize common elements
        MageBridgeViewHelper::initialize('Check');

        // Load libraries
        HTMLHelper::_('behavior.tooltip');
        ToolbarHelper::custom('refresh', 'refresh', null, 'Refresh', false);

        $this->checks = $this->get('checks');

        parent::display($tpl);
    }

    /**
     * Display method
     *
     * @param string $tpl
     */
    public function displayProduct($tpl)
    {
        // Load the form if it's there
        $this->getModel()
            ->setFormName('check_product');

        $this->_viewParent = 'form';
        $this->form        = $this->get('Form');

        // Initialize common elements
        MageBridgeViewHelper::initialize('PRODUCT_RELATION_TEST');

        ToolbarHelper::custom('check_product', 'refresh', null, 'Run', false);

        parent::display('product');
    }

    /**
     * Display method
     *
     * @param string $tpl
     */
    public function displayBrowser($tpl)
    {
        // Initialize common elements
        MageBridgeViewHelper::initialize('Internal Browse Test');

        ToolbarHelper::custom('refresh', 'refresh', null, 'Browse', false);

        $this->url  = MageBridgeModelConfig::load('url') . 'magebridge.php';
        $this->host = MageBridgeModelConfig::load('host');

        parent::display('browser');
    }

    /**
     * Display method
     *
     * @param string $tpl
     */
    public function displayResult($tpl)
    {
        // Fetch configuration data
        $url  = MageBridgeModelConfig::load('url') . 'magebridge.php';
        $host = MageBridgeModelConfig::load('host');

        // Do basic resolving on the host if it is not an IP-address
        if (preg_match('/^([0-9\.]+)$/', $host) == false) {
            $host = preg_replace('/\:[0-9]+$/', '', $host);
            if (gethostbyname($host) == $host) {
                die('ERROR: Failed to resolve hostname "' . $host . '" in DNS');
            }
        }

        // Try to open a socket to port 80
        if (fsockopen($host, 80, $errno, $errmsg, 5) == false) {
            die('ERROR: Failed to open a connection to host "' . $host . '" on port 80. Perhaps a firewall is in the way?');
        }

        // Fetch content through the proxy
        $responses = [];

        // Fetch various responses
        $responses[] = $this->fetchContent('Basic bridge connection succeeded', $url, ['mbtest' => 1]);
        $responses[] = $this->fetchContent('API authentication succeeded', $url, ['mbauthtest' => 1]);

        echo implode('<br/>', $responses);
        exit;
    }

    /**
     * @param $label
     * @param $url
     * @param $params
     *
     * @return string
     */
    protected function fetchContent($label, $url, $params)
    {
        // Initialize the proxy
        $proxy = MageBridgeModelProxy::getInstance();
        $proxy->setAllowRedirects(false);
        $content = $proxy->getRemote($url, $params, 'post');

        // Detect proxy errors
        $proxy_error = $proxy->getProxyError();

        if (!empty($proxy_error)) {
            die('ERROR: Proxy error: ' . $proxy_error);
        }

        // Detect the HTTP status
        $http_status = $proxy->getHttpStatus();

        if ($http_status != 200) {
            die('ERROR: Encountered a HTTP Status ' . $http_status);
        }

        // Parse the content
        if (empty($content)) {
            die('ERROR: Empty content');
        }

        // Detect HTML-page
        if (preg_match('/\<\/html\>$/', $content)) {
            die('ERROR: Data contains HTML not JSON');
        }

        $data = json_decode($content, true);

        if (empty($data)) {
            die('ERROR: Failed to decode JSON');
        }

        if (!array_key_exists('meta', $data)) {
            die('ERROR: JSON response contains unknown data: ' . var_export($data, true));
        }

        return 'SUCCESS: ' . $label;
    }
}
