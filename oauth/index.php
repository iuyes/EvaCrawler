<?php
require_once '../init_autoloader.php';

use EvaOAuth\Service as OAuthService;

$app = new \Slim\Slim();

$app->hook('slim.before', function () use ($app) {
    $posIndex = strpos( $_SERVER['PHP_SELF'], '/index.php');
    $baseUrl = substr( $_SERVER['PHP_SELF'], 0, $posIndex);
    $app->view()->appendData(array('baseUrl' => $baseUrl ));
});


$app->get('/', function () use ($app, $config) {
    $baseUrl = $app->request()->getRootUri();
    $baseUrl = $baseUrl === '\\\\' ? $baseUrl : '';
    echo "<h1>OAuth 1</h1>";
    foreach($config->oauth->oauth1 as $key => $oauth) {
        echo "<a href='$baseUrl/request/oauth1/{$key}'>{$key}</a><br />";
    }

    echo "<h1>OAuth 2</h1>";
    foreach($config->oauth->oauth2 as $key => $oauth) {
        echo "<a href='$baseUrl/request/oauth2/$key'>{$key}</a><br />";
    }
});

$app->get('/request/:oauth/:service', function ($oauth, $service) use ($app, $config) {
    $baseUrl = $app->request()->getRootUri();
    $baseUrl = $baseUrl === '\\\\' ? $baseUrl : '';
    $baseUrl = $app->request()->getUrl() . $baseUrl;


    $oauthStr = $oauth === 'oauth1' ? 'oauth1' : 'oauth2';
    $oauth = new OAuthService();
    $oauth->setOptions(array(
        'callbackUrl' => $baseUrl . "/access/$oauthStr/" . $service,
        'consumerKey' => $config->oauth->$oauthStr->$service->consumer_key,
        'consumerSecret' => $config->oauth->$oauthStr->$service->consumer_secret,
    ));
    $oauth->initAdapter(ucfirst($service), $oauthStr);

    $requestToken = $oauth->getAdapter()->getRequestToken();
    $oauth->getStorage()->saveRequestToken($requestToken);
    $requestTokenUrl = $oauth->getAdapter()->getRequestTokenUrl();
    $app->redirect($requestTokenUrl);
});

$app->get('/access/:oauth/:service', function ($oauth, $service) use ($app, $config) {
    $baseUrl = $app->request()->getRootUri();
    $baseUrl = $baseUrl === '\\\\' ? $baseUrl : '';
    $baseUrl = $app->request()->getUrl() . $baseUrl;

    $oauthStr = $oauth === 'oauth1' ? 'oauth1' : 'oauth2';
    $oauth = new OAuthService();
    $oauth->setOptions(array(
        'callbackUrl' => $baseUrl . "/access/$oauthStr/" . $service,
        'consumerKey' => $config->oauth->$oauthStr->$service->consumer_key,
        'consumerSecret' => $config->oauth->$oauthStr->$service->consumer_secret,
    ));
    $oauth->initAdapter(ucfirst($service), $oauthStr);
    $requestToken = $oauth->getStorage()->getRequestToken();
    $accessToken = $oauth->getAdapter()->getAccessToken($_GET, $requestToken);
    $accessTokenArray = $oauth->getAdapter()->accessTokenToArray($accessToken);
    $oauth->getStorage()->saveAccessToken($accessTokenArray);
    $oauth->getStorage()->clearRequestToken();

    if($accessTokenArray) {
        file_put_contents(__DIR__ . '/../config/config_oauth_' . $service . '_' . $oauthStr . '.php', serialize($accessTokenArray));
    }
});


$app->run();
