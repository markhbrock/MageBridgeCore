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

use Joomla\CMS\Language\Text;

// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<?php if (!empty($this->block)) { ?>
<div id="magebridge-content">
	<?php echo $this->block; ?>
</div>
<div style="clear:both"></div>
<?php } else { ?>
<?php echo Text::_($this->getOfflineMessage()); ?>
<?php } ?>
