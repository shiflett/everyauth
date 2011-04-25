<?php

use \Flickr;

class Everyauth {

    const VERSION = '0.0.1';

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
        throw new Exception("Twitter support is not yet ready.");
    }

}

?>