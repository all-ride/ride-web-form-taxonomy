<?php

namespace ride\web\form\row;

use ride\library\http\Request;
use ride\library\validation\factory\ValidationFactory;

use ride\web\form\row\AutoCompleteStringRow;
use ride\web\form\widget\TagsWidget;
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
     * Instance of the request to retrieve the base URL
     * @var \ride\library\http\Request
     */
    protected $request;

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
     * Sets the request to retrieve the base URL
     * @param \ride\library\http\Request $request
     * @return null
     */
    public function setRequest(Request $request) {
        $this->request = $request;
    }

    /**
     * Processes the request and updates the data of this row
     * @param array $values Submitted values
     * @return null
     */
    public function processData(array $values) {
        $isChanged = false;

        if (isset($values[$this->name]) && is_string($values[$this->name]) && $values[$this->name]) {
            $this->data = explode(',', $values[$this->name]);
        } else {
            $this->data = array();
        }

        if ($this->tagHandler && $this->data !== null) {
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

        if (isset($attributes['class'])) {
            $attributes['class'] .= ' js-tags';
        } else {
            $attributes['class'] = 'js-tags';
        }

        $widget = parent::createWidget($name, $default, $attributes);

        return new TagsWidget($this->type, $name, $default, $widget->getAttributes());
    }

}
