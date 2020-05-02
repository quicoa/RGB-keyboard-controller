/*
 * main.cpp
 *
 *  Created on: Oct 23, 2019
 */

#include <stdio.h>
#include <iostream>
#include <string.h>
#include <cstring>
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
//#include <libusb-1.0/libusb.h>
#include "libusb.h"

#define null NULL
#define tcp_port 8024
#define quit 9

class usb
{
	private:
	const int reqS = 8;
	const int dataS = 512;
	int result;
	libusb_device **devs;
	libusb_device_handle *dev_handle;
	libusb_context *ctx = null;

	public:
	int initialize()
	{
		std::cout << "Initializing USB library...\n";

		result = libusb_init(&ctx);
		//libusb_set_debug(ctx, 3);
		libusb_set_option(ctx, LIBUSB_OPTION_LOG_LEVEL, 3);

		if ( result < 0 )
		{
			std::cout << "Failed to initialize USB library! Exiting...\n";
			return 1;
		}
		else
		{
			std::cout << "USB library initialized.\n";
		}

		return 0;
	}

	public:
	int CheckAndWrite(unsigned char* req, unsigned char* data, unsigned char* brightReq)
	{
		int result;

		//if ( this->check(req) != 0 )
		//{
		//	return 1;
		//}

		if ( this->open() != 0 )
		{
			return 1;
		}

		this->update(brightReq);
		result = this->update(req);
		if ( result != 0 )
		{
			return result;
		}

		result = this->edit(data);

		this->close();

		if ( result > 0 )
		{
			return 4;
		}
		else
		{
			return 0;
		}
	}

	public:
	int UpdateOnly(unsigned char* req)
	{
		int result;

		//if ( this->check(req) != 0 )
		//{
		//	return 1;
		//}

		if ( this->open() != 0 )
		{
			return 1;
		}

		result =  this->update(req);
		this->close();
		return result;
	}

	//private:
	//int check(unsigned char* request)
	//{
	//	std::cout << "Checking data...\n";
    //
	//	if ( sizeof(request) != reqS )
	//	{
	//		std::cout << "Invalid request to send!\n";
	//		return 1;
	//	}
	//	else
	//	{
	//		return 0;
	//	}
	//}

	private:
	int open()
	{
		std::cout << "Processing data to keyboard RGB controller over USB...\n";

		dev_handle = libusb_open_device_with_vid_pid(ctx, 0x048d, 0xce00);

		if ( dev_handle == null )
		{
			std::cout << "Failed to open USB device! Exiting...\n";
			return 2;
		}
		else
		{
			std::cout << "USB device successfully opened.\n";
		}

		if ( libusb_kernel_driver_active(dev_handle, 0) == 1 )
		{
			std::cout << "USB kernel driver is active. Detaching... ";

			if ( libusb_detach_kernel_driver(dev_handle, 0) == 0 )
			{
				std::cout << "success.\n";
			}
			else
			{
				std::cout << "failed.\n";
			}
		}

		if ( libusb_claim_interface(dev_handle, 0) < 0 )
		{
			std::cout << "Failed to claim interface! Exiting...";
			libusb_close(dev_handle);
			return 3;
		}
		else
		{
			std::cout << "Interface successfully claimed.\n";
			return 0;
		}
	}

	private:
	int update(unsigned char* request)
	{
		int res = libusb_control_transfer(dev_handle, 0x21, 9, 0x300, 1, request, reqS, 0);

		if ( res != reqS )
		{
			std::cout << "Request failed: " << libusb_error_name(res) << "\n";
			return 3;
		}
		else
		{
			std::cout << "Request successfull.\n";
			return 0;
		}
	}

	int edit(unsigned char* toSend)
	{
		int writes;

		std::cout << "Writing data... ";

		if ( libusb_bulk_transfer(dev_handle, 2, toSend, dataS, &writes, 0) == 0 )
		{
			if ( writes == dataS )
			{
				std::cout << "success.\n";
				return 0;
			}
		}

		std::cout << "failed.\n";
		return 1;
	}

	int close()
	{
		int result = libusb_release_interface(dev_handle, 0);

		libusb_close(dev_handle);

		if ( result != 0 )
		{
			std::cout << "Failed to release interface.\n";
			return 1;
		}
		else
		{
			return 0;
		}
	}
};

class controller
{
	private:
	const unsigned char defaultColor[4] = { 0x00, 0x00, 0x00, 0x00 };
	unsigned char reqDis[8] = { 0x08, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00 };
	unsigned char reqClr[8] = { 0x12, 0x00, 0x00, 0x08, 0x00, 0x00, 0x00, 0x00 };
	unsigned char reqBrt[8] = { 0x08, 0x02, 0x33, 0x00, 0x32, 0x00, 0x00, 0x00 };
	unsigned char breathing[8] = { 0x08, 0x02, 0x02, 0x05, 0x24, 0x08, 0x00, 0x00 };
	unsigned char wave[8] = { 0x08, 0x02, 0x03, 0x05, 0x24, 0x00, 0x08, 0x00 };
	unsigned char random[8] = { 0x08, 0x02, 0x04, 0x05, 0x24, 0x08, 0x00, 0x00 };
	unsigned char rainbow[8] = { 0x08, 0x02, 0x05, 0x05, 0x24, 0x08, 0x00, 0x00 };
	unsigned char grove[8] = { 0x08, 0x02, 0x06, 0x05, 0x24, 0x08, 0x00, 0x00 };
	unsigned char marquee[8] = { 0x08, 0x02, 0x09, 0x05, 0x24, 0x08, 0x00, 0x00 };
	unsigned char raindrop[8] = { 0x08, 0x02, 0x0A, 0x05, 0x24, 0x08, 0x00, 0x00 };
	unsigned char aurora[8] = { 0x08, 0x02, 0x0E, 0x05, 0x24, 0x08, 0x00, 0x00 };

	unsigned char reactive[8] = { 0x08, 0x02, 0x04, 0x05, 0x24, 0x08, 0x01, 0x00 };
	unsigned char explode[8] = { 0x08, 0x02, 0x06, 0x05, 0x24, 0x08, 0x01, 0x00 };

	private:
	usb usbObj;

	unsigned char data[512];
	char reply[1664+1] = {};

	public:
	int initialize()
	{
		int count;

		std::cout << "Initializing...\n";

		for ( count=0; count<128; count++ )
		{
			if ( !memcpy(data+count*4, defaultColor, sizeof(defaultColor)) )
			{
				std::cout << "Failed to initialize! Copying default values resulted non-zero. Exiting...\n";
				return 1;
			}
		}

		if ( usbObj.initialize() != 0 )
		{
			return 1;
		}

		std::cout << "Initialing complete.\n";
		return 0;
	}

	public:
	int communication()
	{
		int result;

		usbObj.UpdateOnly(this->reqDis);

		do
		{
			int x = 0;
			int newSocket = 0;
			int reads = 0;
			//char msg[256];
			char msg[1664+1] = {};
			//char reply[2];
			//int port = htons(tcp_port);
			socklen_t lenght;
			struct sockaddr_in serv_addr;
			//struct linger sl;
			int SockOption = 1;

			int sock = socket(AF_INET, SOCK_STREAM, 0);

			if ( sock < 0 )
			{
				std::cout << "Failed to create a socket! Exiting...\n";
				return 1;
			}
			else
			{
				if ( setsockopt(sock, SOL_SOCKET, SO_REUSEADDR, &SockOption, sizeof(SockOption)) )
				{
					std::cout << "Failed to set socket options! Exiting...\n";
					close(sock);
					return 1;
				}


				//sl.l_onoff = 1;
				//sl.l_linger = 0;

				//if ( setsockopt(sock, SOL_SOCKET, SO_LINGER, &sl, sizeof(sl)) )
				//{
				//	std::cout << "Failed to set socket options! Exiting...\n";
				//	close(sock);
				//	return 1;
				//}

				result = 0;
			}

			serv_addr.sin_family = AF_INET;
			serv_addr.sin_port = htons(tcp_port);
			serv_addr.sin_addr.s_addr = inet_addr("127.0.0.1");

			std::cout << "Setting up tcp communication for port " << tcp_port << ".\n";

			if ( bind(sock, (struct sockaddr *) &serv_addr, sizeof(serv_addr)) < 0 )
			{
				perror("bind");
				std::cout << "Failed to set up socket bind! Exiting..." << errno << "\n";
				close(sock);
				return 1;
			}

			std::cout << "Listening to socket...\n";
			listen(sock, 5); // Set to 1 later
			//newSocket = accept(sock, (struct sockaddr *) &serv_addr, (socklen_t*) sizeof(serv_addr));
			newSocket = accept(sock, (struct sockaddr *) &serv_addr, &lenght);

			if ( newSocket < 0 )
			{
				std::cout << "Failed to accept socket! Exiting...\n";
				result = 1;
			}
			else
			{
				reads = read(newSocket, msg, 1664);
				if ( reads < 0 )
				{
					std::cout << "Could not read socket input.\nSending reply to client...\n";

					if ( write(newSocket, "Failed to read input!", 22) < 0 )
					{
						std::cout << "Failed to send reply. Resetting socket connection...\n";
					}
					else
					{
						std::cout << "Reply successfully sent.\n";
					}

					continue;
				}
				else if ( reads == 0 )
				{
					std::cout << "Nothing read. Retry...\n";
				}
				else
				{
					if ( msg[reads-1] == '\n' )
					{
						msg[reads-1] = 0;
					}
					else
					{
						msg[reads] = 0;
					}

//					if ( write(newSocket, "Data received.", 15) < 0 )
//					{
						std::cout << "Data from socket: <" << msg << ">.\n";
//					}
//					else
//					{
//						std::cout << "Data from socket: <" << msg << ">, reply failed.\n";
//					}

					x = process(msg);

					if ( x == 9 )
					{
						std::cout << "Cleaning up...\n";

						close(newSocket);
						close(sock);

						std::cout << "Exiting...\n";
						return 0;
					}
					else if ( x == 5 )
					{
						this->request();

						if ( write(newSocket, reply, strlen(reply)) < 0 )
						{
							std::cout << "Replied data.\n";
						}
						else
						{
							if ( strlen(reply) == 0 )
							{
								std::cout << "Failed to send reply! Request data is empty.\n";
							}
							else
							{
								std::cout << "Failed to send reply! Request: " << strerror(errno) << reply << "\n";
							}
						}
					}
				}
			}

			close(newSocket);
			close(sock);
		}
		while( result == 0 );

		return result;
	}

	private:
	int process(char* input)
	{
		std::string cmd(input);
		int bright = 0;
		int position;
		int color;

		if ( std::strcmp(input, "quit") == 0 )
		{
			return 9;
		}
		else if ( std::strcmp(input, "request") == 0 )
		{
			return 5;
		}
		else if ( std::strcmp(input, "disable") == 0 )
		{
			return usbObj.UpdateOnly(this->reqDis);
		}
		else if ( std::strcmp(input, "breathing") == 0 )
		{
			return usbObj.UpdateOnly(this->breathing);
		}
		else if ( std::strcmp(input, "wave") == 0 )
		{
			return usbObj.UpdateOnly(this->wave);
		}
		else if ( std::strcmp(input, "rainbow") == 0 )
		{
			return usbObj.UpdateOnly(this->rainbow);
		}
		else if ( std::strcmp(input, "grove") == 0 )
		{
			return usbObj.UpdateOnly(this->grove);
		}
		else if ( std::strcmp(input, "marquee") == 0 )
		{
			return usbObj.UpdateOnly(this->marquee);
		}
		else if ( std::strcmp(input, "raindrop") == 0 )
		{
			return usbObj.UpdateOnly(this->raindrop);
		}
		else if ( std::strcmp(input, "aurora") == 0 )
		{
			return usbObj.UpdateOnly(this->aurora);
		}
		else if ( std::strcmp(input, "aurora") == 0 )
		{
			return usbObj.UpdateOnly(this->aurora);
		}
		else if ( std::strcmp(input, "reactive") == 0 )
		{
			return usbObj.UpdateOnly(this->reactive);
		}
		else if ( std::strcmp(input, "explode") == 0 )
		{
			return usbObj.UpdateOnly(this->explode);
		}
		else
		{
			char* ptr1 = input;
			char* ptr2 = null;
			bool changed = false;

			do
			{
				ptr2 = strchr(ptr1, ';');

				if ( ptr2 != null )
				{
					bright = 0;
					ptr2[0] = 0;
					ptr2++;

					if ( strchr(ptr1, '.') == null )
					{
						sscanf(ptr1,"%03d#%06x",&position,&color);
					}
					else
					{
						sscanf(ptr1,"%03d#%06x.%d",&position,&color,&bright);
					}

					this->update(position, color, bright);
					changed = true;

					ptr1 = ptr2;
				}
			}
			while ( ptr2 != null );


			if ( changed )
			{
				return usbObj.CheckAndWrite(this->reqClr, this->data, this->reqBrt);
			}
			else
			{
				return 1;
			}
		}
	}

	private:
	int request()
	{
		int count;

		for ( count=0; count<128; count++ )
		{
			sprintf(&reply[count*11], "%03d#%02x%02x%02x;", count, data[count*4+1], data[count*4+2], data[count*4+3]);
		}

		reply[count*11] = 0;

		return 0;
	}

	private:
	int update(int pos, int clr, int brt)
	{
		unsigned char sample[4];
		int count;

		if ( pos == 888 )
		{
			sample[0] = 0x00;
			sample[1] = (clr>>16) & 0xFF;
			sample[2] = (clr>>8) & 0xFF;
			sample[3] = (clr>>0) & 0xFF;

			for ( count=0; count<128; count++ )
			{
				memcpy(data+count*4, sample, sizeof(sample));
			}
		}
		else if ( pos < 0 || pos >= 128 )
		{
			std::cout << "Key position out of range!\n";
		}
		else if ( clr != 000000 )
		{
			data[pos*4] = 0x00;
			data[pos*4+1] = (clr>>16) & 0xFF;
			data[pos*4+2] = (clr>>8) & 0xFF;
			data[pos*4+3] = (clr>>0) & 0xFF;
		}

		switch(brt)
		{
			case 1:
				this->reqBrt[4] = 0x00;
				break;
			case 2:
				this->reqBrt[4] = 0x08;
				break;
			case 3:
				this->reqBrt[4] = 0x16;
				break;
			case 4:
				this->reqBrt[4] = 0x24;
				break;
			case 5:
				this->reqBrt[4] = 0x32;
				break;
			default:
				break;
		}

		return 0;
	}
};

int main()
{
	std::cout << "Starting keyboard RGB lighting daemon...\n";

	controller baseObj;

	if ( baseObj.initialize() != 0 )
	{
		return 1;
	}

	if ( baseObj.communication() != 0 )
	{
		return 2;
	}

	return 0;
}
