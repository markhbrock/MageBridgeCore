<?php
/*
 * Joomla! Yireo Library
 *
 * @author Yireo (http://www.yireo.com/)
 * @package YireoLib
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.6.0
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted access');
?>
<form method="post" name="adminForm" id="adminForm">
    <div class="row-fluid">
        <?php echo $this->loadTemplate('cpanel'); ?>
        <?php echo $this->loadTemplate('logo'); ?>
        <?php echo $this->loadTemplate('details'); ?>
    </div>

    <input type="hidden" name="option" value="<?php echo $this->getConfig('option'); ?>" />
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>