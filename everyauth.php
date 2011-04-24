<?php

class Everyauth {

    const VERSION = '0.0.1';

    protected $callback;

    public function __construct($args) {
        if (isset($args['callback'])) {
            $this->callback = $args['callback'];
        }
    }

    public function controller() {
    }

    public function flickr() {
    }

}

?>
