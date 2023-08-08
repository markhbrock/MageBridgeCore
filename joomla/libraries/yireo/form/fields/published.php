<?php
/*
 * Joomla! field
 *
 * @author Yireo (info@yireo.com)
 * @package Yireo Library
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('JPATH_BASE') or die();

// FIXME: JLoader::import() fails here
include_once JPATH_LIBRARIES . '/joomla/form/fields/radio.php';

/*
 * Form Field-class for showing a yes/no field
 */

class YireoFormFieldPublished extends \Joomla\CMS\Form\Field\RadioField
{
    /*
     * Form field type
     */
    public $type = 'Published';

    /**
     * @param SimpleXMLElement $element
     * @param mixed            $value
     * @param null             $group
     *
     * @return bool
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $rt = parent::setup($element, $value, $group);
        $this->specificSetup();

        return $rt;
    }

    public static function getFieldInput($value)
    {
        $field = new self();
        $field->setValue($value);
        return $field->toString();
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    protected function specificSetup()
    {
        $this->element['label'] = 'JPUBLISHED';
        $this->element['required'] = 1;
        $this->required = 1;
    }

    public function toString()
    {
        $this->fieldName = 'published';
        $this->name = 'published';
        $this->specificSetup();

        return $this->getInput();
    }

    /*
     * Method to construct the HTML of this element
     *
     * @return string
     */
    protected function getInput()
    {
        $classes = [
            'radio',
            'btn-group',
            'btn-group-yesno', ];

        if (in_array($this->fieldname, ['published', 'enabled', 'state'])) {
            $classes[] = 'jpublished';
        }

        $this->class = implode(' ', $classes);

        return parent::getInput();
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = [
            HTMLHelper::_('select.option', '0', Text::_('JUNPUBLISHED')),
            HTMLHelper::_('select.option', '1', Text::_('JPUBLISHED')),];

        return $options;
    }
}
