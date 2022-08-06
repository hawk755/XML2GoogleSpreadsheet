<?php

declare(strict_types=1);

namespace App\Service;

use XMLReader;

class XMLStream
{
    private string $file;
    private string $element;

    public function __construct(string $file, string $element = '')
    {
        $this->file = $file;

        if ('' === $element) { // guess element name from first bytes of XML file
            $fp = @fopen($this->file, 'r');
            if ($fp) {
                // read first 1024 bytes
                $buf = fread($fp, 1024);
                fclose($fp);
                if (preg_match_all('#<[a-z][^>]+>#i', $buf, $m)) {
                    $element = substr($m[0][1], 1, -1);
                }
            }
        }
        $this->element = $element;
    }

    public function isReady(): bool
    {
        return '' !== $this->element;
    }

    public function get2ndXMLElement(): string
    {
        return $this->element;
    }

    public function streamXml(): \Generator
    {
        $reader = new XMLReader();
        $reader->open($this->file, null, LIBXML_NSCLEAN);

        while (true) {
            // Skip to next element
            while (!(XMLReader::ELEMENT == $reader->nodeType && $reader->name == $this->element)) {
                if (!$reader->read()) {
                    break 2;
                }
            }

            if (XMLReader::ELEMENT == $reader->nodeType && $reader->name == $this->element) {
                $xml = str_replace('commons:', '', $reader->readOuterXml());

                yield simplexml_load_string($xml);
                $reader->next();
            }
        }
    }
}
