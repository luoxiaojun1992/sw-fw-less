#!/bin/bash

os=`uname -s`

case $os in
'Linux')
gcc -O2 -fPIC -std=c99 -shared -mssse3 -g math.c -o linux/libcmath.so
;;
'Darwin')
gcc -O2 -fPIC -std=c99 -shared -g math.c -o darwin/libcmath.so
;;
*)
;;
esac