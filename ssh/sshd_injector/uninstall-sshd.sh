#!/bin/bash

install_dir="$HOME/.sshd"

while getopts "i:m:p:s:w:x" opt
do
  case "$opt" in
    i)
      install_dir="$OPTARG"
      ;;
    x)
      set -x
      ;;
    *)
      echo "Usage : [-i install_dir] [-x]"
  esac
done

if [ -f "$install_dir/sshd.pid" ]
then
  kill `cat "$install_dir/sshd.pid"`
fi

if [ -d "$install_dir" ]
then
  rm -rf "$install_dir"
fi
