<?php
/*
 * Joomla! Yireo Library
 *
 * @author Yireo (info@yireo.com)
 * @package YireoLib
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 * @version 0.6.0
 */

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include libraries
require_once dirname(dirname(__FILE__)) . '/loader.php';

/**
 * Yireo Install Helper
 */
class YireoHelperInstall
{
    /**
     * @param array $files
     */
    public static function remove($files = [])
    {
        if (empty($files)) {
            $files = YireoHelper::getData('obsolete_files');
        }

        if (!empty($files)) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    if (is_file($file)) {
                        File::delete($file);
                    }

                    if (is_dir($file)) {
                        Folder::delete($file);
                    }
                }
            }
        }
    }

    /**
     * @param $url
     * @param $label
     *
     * @return bool
     * @throws Exception
     */
    public static function installExtension($url, $label)
    {
        // System variables
        $app = Factory::getApplication();

        // Download the package-file
        $package_file = self::downloadPackage($url);

        // Simple check for the result
        if ($package_file == false) {
            throw new Exception(Text::sprintf('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_FILE_EMPTY', $url));
        }

        // Check if the downloaded file exists
        $tmp_path = $app->get('tmp_path');
        $package_path = $tmp_path . '/' . $package_file;

        if (!is_file($package_path)) {
            throw new Exception(Text::sprintf('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_FILE_NOT_EXIST', $package_path));
        }

        // Check if the file is readable
        if (!is_readable($package_path)) {
            throw new Exception(Text::sprintf('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_FILE_NOT_READABLE', $package_path));
        }

        // Now we assume this is an archive, so let's unpack it
        $package = InstallerHelper::unpack($package_path);

        if ($package == false) {
            throw new Exception(Text::sprintf('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_NO_ARCHIVE', $package['name']));
        }

        // Call the actual installer to install the package
        $installer = Installer::getInstance();

        if ($installer->install($package['dir']) == false) {
            throw new Exception(Text::sprintf('LIB_YIREO_HELPER_INSTALL_EXTENSION_FAIL', $package['name']));
        }

        // Get the name of downloaded package
        if (!is_file($package['packagefile'])) {
            $config = Factory::getConfig();
            $package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
        }

        // Clean up the installation
        @InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
        $app->enqueueMessage(Text::sprintf('LIB_YIREO_HELPER_INSTALL_EXTENSION_SUCCESS', $label), 'notice');

        // Clean the Joomla! plugins cache
        $options = ['defaultgroup' => 'com_plugins', 'cachebase' => JPATH_ADMINISTRATOR . '/cache'];
        $cache = Cache::getInstance('callback', $options);
        $cache->clean();

        return true;
    }

    /*
     * Download a specific package using the MageBridge Proxy (CURL-based)
     *
     * @param string $url
     * @param string $file
     *
     * @return string
     */
    public static function downloadPackage($url, $file = null)
    {
        // System variables
        $app = Factory::getApplication();
        $config = Factory::getConfig();

        // Use fopen() instead
        if (ini_get('allow_url_fopen') == 1) {
            return InstallerHelper::downloadPackage($url, $file);
        }

        // Set the target path if not given
        if (empty($file)) {
            $file = $config->get('tmp_path') . '/' . InstallerHelper::getFilenameFromURL($url);
        } else {
            $file = $config->get('tmp_path') . '/' . basename($file);
        }

        // Open the remote server socket for reading
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FRESH_CONNECT => false,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_BUFFERSIZE => 8192,
        ]);

        $data = curl_exec($ch);
        curl_close($ch);

        if (empty($data)) {
            Factory::getApplication()->enqueueMessage(Text::_('LIB_YIREO_HELPER_INSTALL_REMOTE_DOWNLOAD_FAILED') . ', ' . curl_error($ch), 'warning');
            return false;
        }

        // Write received data to file
        File::write($file, $data);

        // Return the name of the downloaded package
        return basename($file);
    }

    public static function hasLibraryInstalled($library)
    {
        if (is_dir(JPATH_SITE . '/libraries/' . $library)) {
            $query = 'SELECT `name` FROM `#__extensions` WHERE `type`="library" AND `element`="' . $library . '"';
            $db = Factory::getDBO();
            $db->setQuery($query);

            return (bool) $db->loadObject();
        }

        return false;
    }

    public static function hasPluginInstalled($plugin, $group)
    {
        if (file_exists(JPATH_SITE . '/plugins/' . $group . '/' . $plugin . '/' . $plugin . '.xml')) {
            $query = 'SELECT `name` FROM `#__extensions` WHERE `type`="plugin" AND `element`="' . $plugin . '" AND `folder`="' . $group . '"';
            $db = Factory::getDBO();
            $db->setQuery($query);

            return (bool) $db->loadObject();
        }

        return false;
    }

    public static function hasPluginEnabled($plugin, $group)
    {
        $query = 'SELECT `enabled` FROM `#__extensions` WHERE `type`="plugin" AND `element`="' . $plugin . '" AND `folder`="' . $group . '"';
        $db = Factory::getDBO();
        $db->setQuery($query);

        return (bool) $db->loadResult();
    }

    public static function enablePlugin($plugin, $group, $label)
    {
        if (self::hasPluginInstalled($plugin, $group) == false) {
            return false;
        } elseif (self::hasPluginEnabled($plugin, $group) == true) {
            return true;
        }

        $query = 'UPDATE `#__extensions` SET `enabled`="1" WHERE `type`="plugin" AND `element`="' . $plugin . '" AND `folder`="' . $group . '"';
        $db = Factory::getDBO();
        $db->setQuery($query);

        try {
            $db->execute();
            Factory::getApplication()->enqueueMessage(Text::sprintf('LIB_YIREO_HELPER_INSTALL_ENABLE_PLUGIN_SUCCESS', $label), 'notice');
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('LIB_YIREO_HELPER_INSTALL_ENABLE_PLUGIN_FAIL', $label), 'warning');
        }

        // Clean the Joomla! plugins cache
        $options = ['defaultgroup' => 'com_plugins', 'cachebase' => JPATH_ADMINISTRATOR . '/cache'];
        $cache = Cache::getInstance('callback', $options);
        $cache->clean();

        return true;
    }

    public static function autoInstallLibrary($library, $url, $label)
    {
        // If the library is already installed, exit
        if (self::hasLibraryInstalled($library)) {
            return true;
        }

        // Otherwise first, try to install the library
        if (self::installExtension($url, $label) == false) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('LIB_YIREO_HELPER_INSTALL_MISSING', $label), 'warning');
        }
    }

    public static function autoInstallEnablePlugin($plugin, $group, $url, $label)
    {
        // If the plugin is already installed, enable it
        if (self::hasPluginInstalled($plugin, $group)) {
            self::enablePlugin($plugin, $group, $label);

            // Otherwise first, try to install the plugin
        } else {
            if (self::installExtension($url, $label)) {
                self::enablePlugin($plugin, $group, $label);
            } else {
                Factory::getApplication()->enqueueMessage(Text::sprintf('LIB_YIREO_HELPER_INSTALL_MISSING', $label), 'warning');
            }
        }
    }
}
