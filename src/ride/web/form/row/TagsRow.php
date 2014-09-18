<?php

namespace ride\web\form\row;

use ride\library\validation\factory\ValidationFactory;

use ride\web\form\row\AutoCompleteStringRow;
use ride\web\taxonomy\TagHandler;

/**
 * Tags row
 */
class TagsRow extends AutoCompleteStringRow {

    /**
     * Name of the tag handler option
     * @var string
     */
    const OPTION_HANDLER = 'handler';

    /**
     * Instance of the tag handler
     * @var \ride\web\taxonomy\TagHandler
     */
    protected $tagHandler;

    /**
     * Constructs a new form row
     * @param string $name Name of the row
     * @param array $options Extra options for the row or type implementation
     * @return null
     */
    public function __construct($name, array $options) {
        parent::__construct($name, $options);

        $handler = $this->getOption(self::OPTION_HANDLER);
        if ($handler) {
            $this->setTagHandler($handler);
        }
    }

    /**
     * Sets a tag handler to this row
     * @param \ride\library\form\row\TagHandler $tagHandler
     * @return null
     */
    public function setTagHandler(TagHandler $tagHandler) {
        $this->tagHandler = $tagHandler;
    }

    /**
     * Processes the request and updates the data of this row
     * @param array $values Submitted values
     * @return null
     */
    public function processData(array $values) {
        $isChanged = false;

        if (isset($values[$this->name . '-list'])) {
            // values from the tag manager
            $this->data = explode(',', $values[$this->name . '-list']);

            $isChanged = true;
        }

        if (isset($values[$this->name])) {
            // value not added to tags just yet, we take it
            if ($isChanged) {
                $this->data[] = $values[$this->name];
            } else {
                $this->data = array($values[$this->name]);
            }

            $isChanged = true;
        }

        if ($isChanged && $this->tagHandler) {
            // value has changed, process the tags
            $this->data = $this->tagHandler->processTags($this->data);
        }
    }

    /**
     * Creates the widget for this row
     * @param string $name
     * @param mixed $default
     * @param array $attributes
     * @return \ride\library\form\widget\Widget
     */
    protected function createWidget($name, $default, array $attributes) {
        if (isset($attributes['required'])) {
            unset($attributes['required']);
        }

        return parent::createWidget($name, $default, $attributes);
    }

    /**
     * Gets all the javascript files which are needed for this row
     * @return array|null
     */
    public function getJavascripts() {
        $javascripts = parent::getJavascripts();

        if ($this->tagHandler) {
            $javascripts[] = 'js/tagmanager.js';
        }

        return $javascripts;
    }

    /**
     * Gets all the inline javascripts which are needed for this row
     * @return array|null
    */
    public function getInlineJavascripts() {
        $javascripts = parent::getInlineJavascripts();

        if (!$this->tagHandler) {
            return $javascripts;
        }

        $value = $this->widget->getValue();

        $prefilled = array();
        if (is_array($value)) {
            foreach ($value as $tag) {
                $prefilled[] = (string) $tag;
            }
        } elseif ($value) {
            $prefilled = array($value);
        }

        $this->widget->setValue(null);

        $script = '$("#' . $this->widget->getId() . '").addClass("tagmanager").tagsManager({
            prefilled: ' . json_encode($prefilled) . ',
            hiddenTagListName: "' . $this->widget->getName() .  '-list",
            delimiters: [13, 44] // enter, comma
        });';

        $javascripts[] = $script;

        return $javascripts;
    }

    /**
     * Gets all the stylesheets which are needed for this row
     * @return array|null
     */
    public function getStyles() {
        return array('css/tagmanager.css');
    }

}
