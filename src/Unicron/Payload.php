<?php

namespace Unicron;

/**
 * Wrapper class for a payload that ReactPHP needs to read in
 */
class Payload
{
    /**
     * Chunks of the payload
     * @var array
     */
    protected $chunks = array();

    /**
     * Adds a chunk onto the set
     *
     * @param string $chunk
     */
    public function addChunk($chunk)
    {
        $this->chunks[] = $chunk;
    }

    /**
     * Returns the completed paypload all put together
     *
     * @return string
     */
    public function getPayload()
    {
        return implode('', $this->chunks);
    }
}
