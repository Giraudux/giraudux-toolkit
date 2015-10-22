#!/bin/bash
set -e -x

#note: compile openssh with LDFLAGS="-static"

working_dir=`dirname "$0"`
install_dir="$HOME/.sshd"
kernel=`uname -s`
machine=`uname -m`
port="2222"

if [ "$#" -ge "1" ]
then
if [ "$1" -ge "1024" ]
then
if [ "$1" -lt "65536" ]
then
  port="$1"
fi
fi
fi
mkdir -p "$install_dir"
cp "$working_dir/bin/sshd-$kernel-$machine" "$install_dir/sshd"
cp "$working_dir/bin/ssh-keygen-$kernel-$machine" "$install_dir/ssh-keygen"
touch "$install_dir/authorized_keys"
echo "Port $port" >> "$install_dir/sshd_config"
echo "HostKey $install_dir/ssh_host_key" >> "$install_dir/sshd_config"
echo "UsePrivilegeSeparation no" >> "$install_dir/sshd_config"
echo "PidFile $install_dir/sshd.pid" >> "$install_dir/sshd_config"
echo "AuthorizedKeysFile $install_dir/authorized_keys" >> "$install_dir/sshd_config"
"$install_dir/ssh-keygen" -N "" -f "$install_dir/ssh_host_key"
"$install_dir/sshd" -f "$install_dir/sshd_config"
echo "connect to: $USER@$HOSTNAME"
