# RGB-keyboard-controller
RGB controlller for laptop keyboards (avell) to manage all keys seperatly.

Credits to @rodgomesc for his open-source python script which contains the code to communicate with the keyboard hardware controller. The codes that the keyboard expects are adapted from this repository: https://github.com/rodgomesc/avell-unofficial-control-center.
This application sets up a socket and waits for input. As soon as data is sent through the socket the socket is closed, the buffer of 128 sets of 4 charcters (512 bytes total) is changed depented on the input. After that, the usb connection will be opened, the buffer is sent and the usb connection is closed. That's it.

## How to use this software
Example:
The all keys on the keyboard are full green (#00FF00), in a terminal, establish a connection to the socket (nc localhost 8024) and we type '025#FF0000.5;'

The first set of three characters define the key position. In this case position 25, that is the 'X' on my keyboard. 000 is the first key, 127 is the last (128 keys total).
The second set of six characters ('#' is a seperator) define the color, in this case full red.
The last character is the brightness of the whole keyboard, in this case 5 (again, '.' is a seperator). But this brightness settings is not from 1-4, but the following:
0: inherit, apply the last brightness setting. By default (on startup) this is full brightness (5).
1: brightness is 0, means all leds are off, but the keyboard controlller is not disabled.
2: brightness is 1
3: brightness is 2
4: brightness is 3
5: brightness is 4
Keep in mind: this setting is optional, if brightness is undefined it acts the same as 0 (inherit).

If you update the buffer with colors, don't forget to end each key with a semicolon (;). It is possible to send multiple strings like this at once. A few examples: `000#EEEE00;` `127#000000.4;` `005#FFFFFF;106#00FF00;018#101010;`  `005#FFFFFF;106#00FF00;018#101010.0;` Note: the last two strings result the same. Changing `.0` into `.4` can result different. Also, the position of the brightness setting does not have to be at the last block: `005#FFFFFF.0;106#00FF00;018#101010;` still results the same.

### Effects
The hardware controller has some keyboard effects. Just send the name of the effect through the socket without an semicolon on the end. For example: 'rainbow', 'rainbow;' will fail.
All effects available:
rainbow
reactive
raindrop
marquee
aurora

## Interface
I'm working on a webinterface containing all keys that you can change. All written in HTML5, CSS, PHP and a bit of JS.
