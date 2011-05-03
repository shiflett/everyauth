Everyauth, a PHP abstraction library for universal auth support
===============================================================

Requirements
------------

- [PHP](http://php.net/) 5.3

Usage
-----

It's probably easiest to just clone this project, so you can use `git pull` to
update stuff:

    git clone git@github.com:shiflett/everyauth.git

Because it depends upon external libraries, you'll also need to do this:

    git submodule init
    git submodule update

Within your app, dedicate a route to Everyauth. I use
[Lithium](http://lithify.me/), so I define a route like this:

    Router::connect('/auth/{:app}', array('Auth::everyauth'));

In `AuthController.php`, my `everyauth()` method includes `everyauth.php`,
starts a session, sets the configuration of the apps I want to support, and uses
Everyauth's `controller()` method to handle everything else:

    session_start();

    include '/path/to/everyauth.php';

    $flickr = array('key' => '412e11d5317627e48a4b0615c84b9a8f',
                    'secret' => 'abcdefghijklmnopqrstuvwxyz',
                    'perms' => 'write',
                    'return' => 'http://shiflett.org/');
    $twitter = array('key' => '412e11d5317627e48a4b0615c84b9a8f',
                     'secret' => 'abcdefghijklmnopqrstuvwxyz',
                     'return' => 'http://shiflett.org/');

    $everyauth = new Everyauth(array('flickr' => $flickr,
                                     'twitter' => $twitter));
    $everyauth->controller();

With this in place, I can just link to `/auth/flickr` or `/auth/twitter`
whenever I want to authenticate a user with Flickr or Twitter. Everyauth stores
everything I need in `$_SESSION['everyauth']` and returns the user to the
`return` URL.

Support
-------

For now, Everyauth supports the following apps:

- [Flickr](http://flickr.com/)
- [Twitter](http://twitter.com/)