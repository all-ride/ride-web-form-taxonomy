<?php

namespace ride\web\form\widget;

use ride\library\form\widget\GenericWidget;

/**
 * Widget for a tags row
 */
class TagsWidget extends GenericWidget {

    /**
     * Sets the value for this widget
     * @param mixed $value Value to set
     * @param string $part Name of the part
     * @return null
     */
    public function setValue($value, $part = null) {
        if (is_array($value)) {
            if ($value) {
                $value = implode(',', $value);
            } else {
                $value = null;
            }
        }

        parent::setValue($value, $part);
    }

}
