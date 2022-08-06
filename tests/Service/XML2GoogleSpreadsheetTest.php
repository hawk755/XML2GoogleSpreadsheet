<?php

declare(strict_types=1);

use App\Service\Downloader;
use App\Service\GoogleSpreadsheetPusher;
use App\Service\XML2GoogleSpreadsheet;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 * @coversNothing
 */
class XML2GoogleSpreadsheetTest extends KernelTestCase
{
    public function testXmlPush()
    {
        $downloader = $this->createMock(Downloader::class);
        $logger = $this->createMock(LoggerInterface::class);

        $googlePusher = static::getContainer()->get(GoogleSpreadsheetPusher::class);

        $xml2GoogleSpreadsheet = new XML2GoogleSpreadsheet($downloader, $googlePusher, $logger);
        $this->assertTrue($xml2GoogleSpreadsheet->import(__DIR__.'/coffee_feed_trimmed.xml'));
    }
}
