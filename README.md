# SocketToMe

Copyright(c) 2013, Robin Wood <robin@digininja.org>

SocketToMe is little application I wrote to go along with my blog post on [OWASP ZAP and WebSockets](https://digi.ninja/blog/zap_web_sockets.php). It combines chat, a simple number guessing game and a few other hidden features.

The app is in two parts, the web socket app and a web page to access it. The whole lot is written PHP and is the first web socket work I've done so don't look on it as an example of how to do things.

The only dependencies are PHP and a web server which can serve it.

## Web Socket

The web socket app is self contained and should run on any server with PHP. From the application root, start it with:

```
php bin/sockettome.php
```

Assuming all is well, when it starts up you will get a nice welcome message. 

The app prints lots of debug so if you are having problems with anything check the console for help.

## Web App

Point the web server at the htdocs directory and browse to it. If you get a page then everything is probably working. To see if the web socket connection has been made successfully check the console for a new user message.

If the web socket connection fails it could be the IP it is trying to connect to. The page assumes that the web socket is running on the same server it is served from, if it isn't then you need to edit the connection line:

```php
var conn = new WebSocket('ws://<?=$_SERVER['SERVER_ADDR']?>:8080');
```

Changing the address as appropriate, e.g.:

```php
var conn = new WebSocket('ws://192.168.1.0:99');
```

## Usage

Once you've started it all up the usage is easy, the chat system simply takes messages from one user and sends them to every other connected user. To play the guessing game simply enter a guess between 0 and 100, if you guess correctly you win and a new number will be chosen. To make it easier to identify who is talking you can also set your name.

Unless you have a friend who wants to play along with you, I didn't in testing, I simply had two browsers open and passed messages between them.

There are a few other "hidden" features in the system, I cover how to find these in the [Fuzzing WebSockets with ZAP](https://digi.ninja/blog/zap_fuzzing.php). If you want to cheat and see what these features are, all the interesting code can be found in
`src/MyApp/SocketToMe.php`.
