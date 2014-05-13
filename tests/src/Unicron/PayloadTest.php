<?php

namespace UnicronTest;

use Unicron\Payload;

class PayloadTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateValidPayload()
    {
        $payload = new Payload();
        $payload->addChunk('Chunk1');
        $payload->addChunk('Chunk2');

        $this->assertEquals('Chunk1Chunk2', $payload->getPayload());
    }
}
