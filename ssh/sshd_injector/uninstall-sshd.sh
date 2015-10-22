#!/bin/bash
set -x

install_dir="$HOME/.sshd"

if [ -f "$install_dir/sshd.pid" ]
then
  kill `cat "$install_dir/sshd.pid"`
fi
if [ -d "$install_dir" ]
then
  rm -rf "$install_dir"
fi
