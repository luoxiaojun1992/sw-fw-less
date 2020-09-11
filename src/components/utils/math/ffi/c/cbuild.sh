#!/bin/bash

gcc -O2 -fPIC -shared -g math.c -o libcmath.so
