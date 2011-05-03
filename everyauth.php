<?php

use \Flickr;
use \TwitterOAuth;

class Everyauth {

    const VERSION = '0.0.2';

    protected $callback;
    protected $return;

    // Configuration for supported apps
    protected $flickr;
    protected $twitter;

    public function __construct($args) {
        if (isset($args['callback'])) {
            $this->callback = $args['callback'];
        }

        if (isset($args['flickr'])) {
            $this->flickr = $args['flickr'];
        }

        if (isset($args['twitter'])) {
            $this->twitter = $args['twitter'];
        }
    }

    public function controller() {
        $url = parse_url($_SERVER['REQUEST_URI']);
        $app = basename($url['path']);
        if (isset($this->$app)) {
            $this->$app();
        } else {
            throw new Exception("Configuration for {$app} not found");
        }
    }

    public function flickr() {
        // http://flickr.com/services/api/auth.howto.web.html

        // https://github.com/kellan/flickr.simple.php
        include __DIR__ . '/flickr/flickr.simple.php';

        $flickr = new Flickr($this->flickr['key'], $this->flickr['secret'], 'api.flickr.com', FALSE);

        if (!isset($_GET['frob'])) {
            // If no frob in URL, send user to Flickr.
            $url = $flickr->auth_url($frob, $this->flickr['perms']);
            header("Location: {$url}");
            exit;
        } else {
            // If frob in URL, user is returning from Flickr, so convert frob to token.
            $response = $flickr->call_method('flickr.auth.getToken', array('frob' => $_GET['frob']), TRUE);

            // Only set the token if the request was successful.
            if ($response['stat'] == 'ok') {
                $_SESSION['everyauth']['flickr'] = array('token' => $response['auth']['token']['_content'],
                                                         'nsid' => $response['auth']['user']['nsid']);

                // Send user back into the fray.
                header("Location: {$this->flickr['return']}");
                exit;
            } else {
                throw new Exception("flickr.auth.getToken error {$response['code']}: {$response['message']}");
            }
        }
    }

    public function twitter() {
        // http://dev.twitter.com/pages/auth

        //https://github.com/abraham/twitteroauth
        include __DIR__ . '/twitter/twitteroauth/twitteroauth.php';

        if (!isset($_GET['oauth_token'])) {
            // If no request token, send user to Twitter.
            $twitter = new TwitterOAuth($this->twitter['key'], $this->twitter['secret']);

            // At a minimum, make sure the host has only printable characters.
            if (!ctype_print($_SERVER['HTTP_HOST'])) {
                throw new Exception('Malicious characters in Host header detected.');
            }

            // Return user to this page.
            if (empty($_SERVER['HTTPS'])) {
                $url = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            } else {
                $url = "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            }

            $response = $twitter->getRequestToken($url);

            // Keep the request token in the session.
            $_SESSION['everyauth']['twitter'] = array('request' => $response['oauth_token'],
                                                      'secret' => $response['oauth_token_secret']);
    
            // Redirect the user to Twitter for authorization.
            $url = $twitter->getAuthorizeURL($response['oauth_token']);
            header("Location: {$url}");
            exit;
        } else {
            // User is returning from Twitter, so get access token.
            $twitter = new TwitterOAuth($this->twitter['key'], $this->twitter['secret'], $_SESSION['everyauth']['twitter']['request'], $_SESSION['everyauth']['twitter']['secret']);

            // Get access token and stuff.
            $token = $twitter->getAccessToken($_GET['oauth_verifier']);

            $_SESSION['everyauth']['twitter'] = array('token' => $token['oauth_token'],
                                                      'secret' => $token['oauth_token_secret'],
                                                      'screen_name' => $token['screen_name']);

            // Send user back into the fray.
            header("Location: {$this->twitter['return']}");
            exit;
        }

    }

}

?>