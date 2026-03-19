<?php 
namespace App;

use PHPMailer\PHPMAiler\OAuthTokenProvider;

class GoogleOauthClient implements OAuthTokenProvider
{
    private $oauthUserEmail;
    private $client;
    private $tokenPath;

    public function __construct($oauthUserEmail, $credentialsFile, $tokenPath)
    {
        $this->oauthUserEmail = $oauthUserEmail;

        $this->client = new \Google_Client();
        $this->client->setScopes([\Google_Service_Gmail::MAIL_GOOGLE_COM]);
        $this->client->setAuthConfig($credentialsFile);
        $this->client->setApplicationName('fdiengdoh.com');
        $this->client->setAccessType('offline');

        // Set the token path
        $this->tokenPath = $tokenPath;

        // Load previously stored auth token
        if (file_exists($this->tokenPath)) {
            $accessToken = json_decode(file_get_contents($this->tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }
    }

    public function refreshOAuthToken()
    {
        // If our token has not expired, there is nothing to do
        if (!$this->client->isAccessTokenExpired()) {
            return;
        }

        // If our token has expired, but we do not have a refresh token
        if (!$this->client->getRefreshToken()) {
            $authUrl = $this->client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
            $this->client->setAccessToken($accessToken);

            if (array_key_exists('error', $accessToken)) {
                throw new \Exception(join(', ', $accessToken));
            }
        }

        $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());

        // Save the token to the token file
        file_put_contents($this->tokenPath, json_encode($this->client->getAccessToken()));
    }

    /**
     * @see \PHPMailer\PHPMailer\OAuth::getOauth64()
     */
    public function getOauth64(): string
    {
        $this->refreshOAuthToken();

        $oauthUserEmail = env('GOOGLE_CLIENT_EMAIL');
        $oauthToken = $this->client->getAccessToken();
        return base64_encode(
            'user=' .
            $this->oauthUserEmail .
            "\001auth=Bearer " .
            $oauthToken['access_token'] .
            "\001\001"
        );
    }
}