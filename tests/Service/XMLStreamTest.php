<?php

declare(strict_types=1);

use App\Service\XMLStream;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class XMLStreamTest extends TestCase
{
    private $o_xml;

    protected function setUp(): void
    {
        $this->o_xml = new XMLStream(__DIR__.'/coffee_feed_trimmed.xml');
    }

    public function testXmlAutoConfiguring()
    {
        $this->assertTrue($this->o_xml->isReady());
        $this->assertEquals('item', $this->o_xml->get2ndXMLElement());
        $o_xml = new XMLStream(__DIR__.'/text_file.txt');
        $this->assertEquals(false, $o_xml->isReady());
    }

    public function testXmlFetching()
    {
        $cnt = 0;
        foreach ($this->o_xml->streamXml() as $xml) {
            ++$cnt;
        }
        $this->assertEquals(12, $cnt);
    }
}
