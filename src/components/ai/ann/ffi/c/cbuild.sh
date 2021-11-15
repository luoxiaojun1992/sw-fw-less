#!/bin/bash

os=`uname -s`

case $os in
'Linux')
gcc -O2 -fPIC -std=c99 -shared -g ann.c -o build/linux/libcann.so
;;
'Darwin')
gcc -O2 -fPIC -std=c99 -shared -g ann.c -o build/darwin/libcann.so
;;
*)
;;
esac