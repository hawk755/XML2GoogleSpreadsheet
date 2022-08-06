<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;

class XML2GoogleSpreadsheet
{
    private string $tmp_f = '';
    private int $CHUNK_SIZE = 500; // how many records to push to a Google Spreadsheet at a time

    public function __construct(private Downloader $downloader, private GoogleSpreadsheetPusher $googlePusher, private LoggerInterface $logger)
    {
    }

    public function setChunkSize(int $maxRowsAtATime)
    {
        $this->CHUNK_SIZE = $maxRowsAtATime;
    }

    public function import(string $xml_f): bool
    {
        if (preg_match('/^(?:ht|f)tps?:/', $xml_f)) {
            $this->tmp_f = tempnam(sys_get_temp_dir(), 'TMP');

            if (!$this->downloader->download($xml_f, $this->tmp_f)) {
                return $this->haltWithError('Download failed: '.$xml_f);
            }
            $xml_f = $this->tmp_f;
        }

        if (!is_file($xml_f)) {
            return $this->haltWithError("File not found: {$xml_f}");
        }

        $o_xml = new XMLStream($xml_f); // , 'item'
        if (!$o_xml->isReady()) {
            return $this->haltWithError("XML file autoconfiguring failed: {$xml_f}");
        }

        $a_fields = $data = [];

        $current_chunk = 0;
        $first_run = true;

        foreach ($o_xml->streamXml() as $xml) {
            if ($first_run) {
                $err_msg = $this->googlePusher->createSheet('XML2Google');
                if ($err_msg) {
                    return $this->haltWithError('googlePusher.createSheet() failed: '.$err_msg);
                }
                foreach ($xml->children() as $field => $child) {
                    $a_fields[] = $field;
                }
                array_push($data, $a_fields);
                $first_run = false;
                ++$current_chunk;
            }

            $a_v = [];
            foreach ($a_fields as $field) {
                $a_v[] = (string) $xml->{$field};
            }
            array_push($data, $a_v);
            if ($this->CHUNK_SIZE == ++$current_chunk) {
                $err_msg = $this->googlePusher->addRows($data);
                if ($err_msg) {
                    return $this->haltWithError('googlePusher.addRows() failed: '.$err_msg);
                }
                $current_chunk = 0;
                $data = [];
            }
        }

        if ($data) {
            $err_msg = $this->googlePusher->addRows($data);
            if ($err_msg) {
                return $this->haltWithError('googlePusher.addRows() failed: '.$err_msg);
            }
        }

        if ($this->tmp_f) {
            unlink($this->tmp_f);
        }

        return true;
    }

    private function haltWithError(string $err_msg): bool
    {
        $this->logger->error($err_msg);
        if ($this->tmp_f) {
            unlink($this->tmp_f);
        }

        return false;
    }
}
