<?php

namespace App\Service;

use Exception;
use Google;
use Google_Service_Sheets_Spreadsheet;
use Google_Service_Sheets_ValueRange;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GoogleSpreadsheetPusher
{
    private $service;
    private $spreadsheetId;

    public function __construct(ParameterBagInterface $params)
    {
        $client = new Google\Client();
        $client->setApplicationName('GoogleSpreadsheetPusher');
        $client->setScopes('https://www.googleapis.com/auth/spreadsheets');

        // $client->setAuthConfig('credentials.json');

        $config = ['installed' => [
            'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
            'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'redirect_uris' => ['http://localhost'],
        ]];

        $client->setAuthConfig($config);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.

        $tokenPath = $params->get('kernel.project_dir').'/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.

                echo "Open the following link in your browser:\n".$client->createAuthUrl().
                "\nEnter verification code (see the final URL between \"?code=\" and \"&scope=\"): ";

                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }

        $this->service = new Google\Service\Sheets($client);
    }

    public function createSheet($title): string
    {
        /* Load pre-authorized user credentials from the environment.
           TODO(developer) - See https://developers.google.com/identity for
            guides on implementing OAuth2 for your application. */
        try {
            $spreadsheet = new Google_Service_Sheets_Spreadsheet([
                'properties' => [
                    'title' => $title,
                ],
            ]);

            $spreadsheet = $this->service->spreadsheets->create($spreadsheet, [
                'fields' => 'spreadsheetId',
            ]);

            $this->spreadsheetId = $spreadsheet->spreadsheetId;
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return '';
    }

    public function addRows($data): string
    {
        try {
            $valueRange = new Google_Service_Sheets_ValueRange();
            $valueRange->setValues($data);
            $range = 'Sheet1!A1:A';
            $conf = ['valueInputOption' => 'USER_ENTERED'];
            $this->service->spreadsheets_values->append($this->spreadsheetId, $range, $valueRange, $conf);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return '';
    }
}
