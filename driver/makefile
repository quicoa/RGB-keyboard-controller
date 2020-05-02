# Usage:
# make        # compile all binary
# make clean  # remove ALL binaries and objects

.PHONY = all clean

#compiler to use
CC = g++

SRCS := src/main.cpp
BINS := bin/KbdLighting

all: build

build:
	@echo "Building ${BINS}..."
	${CC} -Iinclude -Llib -L/usr/lib64 src/main.cpp -o "bin/KbdLighting" -lusb-1.0 -ludev -lpthread

clean:
	@echo "Cleaning up..."
	rm -rvf ${BINS}

