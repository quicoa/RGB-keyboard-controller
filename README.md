# RGB-keyboard-controller
RGB controlller for laptop keyboards (Avell) to manage all keys seperatly.

*Note: The software is currently being rewritten to C only and to be more logic and easier to use.*

Credits to @rodgomesc for his open-source python script which contains the code to communicate with the keyboard hardware controller. The codes that the keyboard expects are adapted from this repository: https://github.com/rodgomesc/avell-unofficial-control-center.
This application sets up a socket and waits for input. As soon as data is sent through the socket the socket is closed, the buffer of 128 sets of 4 charcters (512 bytes total) is changed depented on the input. After that, the usb connection will be opened, the buffer is sent and the usb connection is closed. That's it.

## How to use the driver
Note: The driver needs to write to an usb device, which is not writable without root. It is possible but write without root but that requires additional steps. For now, you can search the internet for a solution if you don't trust this software. Remember that you can study or modify the code if you want to.

The driver (writtin in C++) can be found in the 'driver' folder. The main.cpp file can be compiled into a GNU/Linux executable using the makefile. The following 3 libraries are required for the makefile: usb-1.0, udev and pthread. libusb-1.0 is tested with 1.0.21 and 1.0.22. Version 1.0.22 is compiled into a static library inside 'lib/'. If compiling fails, try to (re)move the libusb static library and build using the system library. 

Now comes the more technical information about how to use the driver. 

Example:
The all keys on the keyboard are full green (#00FF00), in a terminal, establish a connection to the socket (nc localhost 8024) and type '025#FF0000.5;' This will change the 'X' key on the keyboard to full red and set the brightness of the keyboard to full brightness.

The first set of three characters define the key position. In this case position 25, that is the 'X' on my keyboard. 000 is the first key, 127 is the last (128 keys total). Enter '888' to apply the color for all keys (mono-color).
The second set of six characters ('#' is a seperator) defines the color, in this case full red. This is hexadecimal color. Entering `000000` means inherit the color.
The last character is the brightness of the whole keyboard, in this case 5 (again, '.' is a seperator). But this brightness settings is not from 1 to 4, but the following:
0: inherit, apply the last brightness setting. By default (on startup) this is full brightness (5).
1: brightness is 0, means all leds are off, but the hardware controlller is not off.
2: brightness send to the keyboard is 1
3: brightness send to the keyboard is 2
4: brightness send to the keyboard is 3
5: brightness send to the keyboard is 4
Keep in mind: this setting is optional, if brightness is undefined it acts the same as 0 (inherit). At startup, all keys are disabled but on enable the brightness is 5 (full).

If you update the buffer with colors, don't forget to end each key with a semicolon (;). It is possible to send multiple strings like this at once. A few examples: `000#EEEE00;` `127#000000.4;` `005#FFFFFF;106#00FF00;018#101010;`  `005#FFFFFF;106#00FF00;018#101010.0;`
Note: the last two strings result the same. Changing `.0` into `.4` may result differently. Also, the position of the brightness setting does not have to be at the last block: `005#FFFFFF.0;106#00FF00;018#101010;` also works.

By sending `request` through the socket, the driver returns the buffer in the same format as input should be. If you save that, it can be used as a profile in the future. Set some colors by using netcat or the web interface and then try this:
`echo 'request' | nc localhost 8024` # Results current RGB buffer
`echo 'request' | nc localhost 8024 > profile1.txt` # Get buffer and write to profile1.txt
`cat profile1.txt | nc localhost 8024` Read profile1.txt and send it through the socket

### Effects
The hardware controller has some keyboard effects. Just send the name of the effect through the socket without an semicolon on the end. For example: 'rainbow', 'rainbow;' will fail.
Effects available:
* breathing
* wave
* rainbow
* grove
* marquee
* raindrop
* aurora
Depending on keyboard input:
* reactive
* explode

Note: first setting up different colors for all keys and then entering an effect doesn't erase the color buffer. If you enter `000#000000.0;` it sends the buffer (that still is unchanged) to the hardware controlller, and all custom colors are back.

## Interface
I'm working on a webinterface containing all keys that you can change, a color selector and all processing through the socket. All written in HTML5, CSS, PHP and a bit of JS. The webinterface can be found in '../interface/www'. The index.php is all you need with the right php and HTTP daemon settings.

## ToDo
Improve web interface (get, set profiles and using effects)
Provide a way of running the driver without root
