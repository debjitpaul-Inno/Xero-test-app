<?php

namespace App\Action;

//use StorageClass;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use XeroAPI\XeroPHP\Api\IdentityApi;
use XeroAPI\XeroPHP\Configuration;

class Callback
{
    public function execute()
    {
        ini_set('display_errors', 'On');
//        require __DIR__ . '/vendor/autoload.php';
        require_once('storage.php');

        // Storage Classe uses sessions for storing token > extend to your DB of choice
//        $storage = new StorageClass();
        $storage = (new Storage());

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => '6B907BAABFF347299F00D976D2D35EA5',
            'clientSecret'            => 'VGkl5Pa1d2_LuQYowpNM0cjjVDdf_q02FJphAFwS4OHuRtjH',
            'redirectUri'             => route('callback'),
            'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
            'urlAccessToken'          => 'https://identity.xero.com/connect/token',
            'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
        ]);

        // If we don't have an authorization code then get one
        if (!isset($_GET['code'])) {
            echo "Something went wrong, no authorization code found";
            exit("Something went wrong, no authorization code found");

            // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            echo "Invalid State";
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        } else {

            try {
                // Try to get an access token using the authorization code grant.
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);

                $config = Configuration::getDefaultConfiguration()->setAccessToken( (string)$accessToken->getToken() );
                $identityApi = new IdentityApi(
                    new Client(),
                    $config
                );

                $result = $identityApi->getConnections();

                // Save my tokens, expiration tenant_id
                $storage->setToken(
                    $accessToken->getToken(),
                    $accessToken->getExpires(),
                    $result[0]->getTenantId(),
                    $accessToken->getRefreshToken(),
                    $accessToken->getValues()["id_token"]
                );

//                header('Location: ' . './authorizedResource.php');
                header('Location: ' . route('authorization.resource'));
                exit();

            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                echo "Callback failed";
                exit();
            }
        }
    }

}
