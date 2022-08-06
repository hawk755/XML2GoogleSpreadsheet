<?php

declare(strict_types=1);

use App\Service\Downloader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

/**
 * @internal
 * @coversNothing
 */
class DownloaderTest extends TestCase
{
    public function testDownload()
    {
        $http_client = HttpClient::create();
        $downloader = new Downloader($http_client);

        $tmp_f = tempnam(sys_get_temp_dir(), 'TMP');
        $this->assertEquals(0, filesize($tmp_f));

        $downloader->download('https://example.com/', $tmp_f);

        clearstatcache();
        $this->assertGreaterThan(0, filesize($tmp_f));
        $this->assertStringContainsString('Example Domain', file_get_contents($tmp_f));
        unlink($tmp_f);
    }
}
