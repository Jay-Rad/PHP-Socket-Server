# PHP-Socket-Server
A PHP version of the InstaTech socket server.  This was mostly an experiment, so it's missing a lot of optimizations and error checking I put into the production version.  However, it works, and it shows how one might implement a socket server such as InstaTech's with PHP.

Overall, I found it much more difficult to work with sockets in PHP than in other languages, such as JavaScript or C#.  Doubly so because it apparently doesn't support web socket protocol out of the box.  I was able to get the connection established using stream_socket_server() and complete the websocket upgrade handshake manually, but I was having trouble reconstructing the JSON data after receiving it through the socket connection.  PHP handles binary data in a way I'm not used to, and I eventually found Ratchet to use instead.

With that said, this project includes Ratchet and its dependencies.  It uses composer, which I've never used before but seems pretty useful.  Check out Ratchet at http://socketo.me/.

## Installation and Setup
The CLI folder contains the server.  You need to run it from the terminal.

`php ./CLI/bin/Server.php`

The web files are in the www folder.  Drop those on your web server and open Viewer.php in your browser.

Then you just need to point the InstaTech client at that address instead of InstaTech.org.
