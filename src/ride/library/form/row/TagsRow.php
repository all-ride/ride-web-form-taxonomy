<?php

namespace ride\library\form\row;

use ride\library\taxonomy\TagHandler;
use ride\library\validation\factory\ValidationFactory;

/**
 * Tags row
 */
class TagsRow extends StringRow implements HtmlRow {

    /**
     * Name of the tag handler option
     * @var string
     */
    const OPTION_HANDLER = 'handler';

    /**
     * Name of the auto complete URL option
     * @var string
     */
    const OPTION_AUTO_COMPLETE_URL = 'autocomplete.url';

    /**
     * Name of the auto complete URL option
     * @var string
     */
    const OPTION_AUTO_COMPLETE_MINIMUM = 'autocomplete.minimum';

    /**
     * Instance of the tag handler
     * @var \ride\web\taxonomy\TagHandler
     */
    protected $tagHandler;

    /**
     * URL for auto completion
     * @var string
     */
    protected $autoCompleteUrl;

    /**
     * Minimum number of characters for auto completion
     * @var integer
     */
    protected $autoCompleteMinimum;

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

        $this->setAutoComplete($this->getOption(self::OPTION_AUTO_COMPLETE_URL), $this->getOption(self::OPTION_AUTO_COMPLETE_MINIMUM, 2));
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
     * Sets a URL for auto completion
     * @param string $url URL to fetch the results from. Use %term% placeholder
     * to reserve a place for the term filter
     * @param integer $minimum Minimum number of characters before perform auto
     * completion
     * @return null
     */
    public function setAutoComplete($url, $minimum = 2) {
        $this->autoCompleteUrl = $url;
        $this->autoCompleteMinimum = $minimum;
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
     * Gets all the javascript files which are needed for this row
     * @return array|null
     */
    public function getJavascripts() {
        $javascripts = array();

        if ($this->autoCompleteUrl) {
            $javascripts[] = 'js/jquery-ui.js';
        }

        $javascripts[] = 'js/tagmanager.js';

        return $javascripts;
    }

    /**
     * Gets all the inline javascripts which are needed for this row
     * @return array|null
    */
    public function getInlineJavascripts() {
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

        $token = ucfirst(substr(md5(microtime()), 0, 7));

        $script = 'var tagInput' . $token .' = $("#' . $this->widget->getId() . '");';
        $script .= 'tagInput' . $token . '.addClass("tagmanager");';
        $script .= 'tagInput' . $token . '.tagsManager({
            prefilled: ' . json_encode($prefilled) . ',
            hiddenTagListName: "' . $this->widget->getName() .  '-list",
            delimiters: [13, 44] // enter, comma
        });';

        if ($this->autoCompleteUrl) {
            $script .= 'tagInput' . $token . '.autocomplete({
                minLength: ' . $this->autoCompleteMinimum . ',
                source: function (request, response) {
                    var url = "' . $this->autoCompleteUrl . '";
                    $.ajax({
                        url: url.replace("%term%", request.term),
                        dataType: "json",
                        success: function (data) {
                            response($.map(data, function(item) {
                                return {
                                    label: item,
                                    value: item
                                }
                            }));
                        }
                    });
                },
            });';
        }

        return array($script);
    }

    /**
     * Gets all the stylesheets which are needed for this row
     * @return array|null
     */
    public function getStyles() {
        return array('css/tagmanager.css');
    }

}
