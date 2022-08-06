<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Downloader
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    public function download(string $url, string $target_file): bool
    {
        $response = $this->client->request('GET', $url);

        if (200 !== $response->getStatusCode()) {
            return false;
        }

        $fileHandler = @fopen($target_file, 'w');
        if (!$fileHandler) {
            return false;
        }

        // get the response content in chunks and save them in a file
        // response chunks implement Symfony\Contracts\HttpClient\ChunkInterface
        foreach ($this->client->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }
        fclose($fileHandler);

        return true;
    }
}
