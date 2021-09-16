#!/bin/bash

os=`uname -s`

case $os in
'Linux')
gcc -O2 -fPIC -std=c99 -D_XOPEN_SOURCE=500 -shared -g mmap.c -o linux/libcmmap.so
;;
'Darwin')
gcc -O2 -fPIC -std=c99 -shared -g mmap.c -o darwin/libcmmap.so
;;
*)
;;
esac