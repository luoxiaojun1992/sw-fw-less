#!/bin/bash

os=`uname -s`

case $os in
'Linux')
gcc -O2 -fPIC -std=c99 -shared -g log.c -o build/linux/libclog.so
;;
'Darwin')
gcc -O2 -fPIC -std=c99 -shared -g log.c -o build/darwin/libclog.so
;;
*)
;;
esac