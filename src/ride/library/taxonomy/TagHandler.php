<?php

namespace ride\library\taxonomy;

/**
 * Interface to process tags for a specific taxonomy backend
 */
interface TagHandler {

    /**
     * Converts an array of string tags to tag entries
     * @param array $tags
     * @return array Processed tags
     */
    public function processTags(array $tags);

}
