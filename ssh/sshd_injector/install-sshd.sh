#!/bin/bash
set -e

#note: compile openssh with LDFLAGS="-static"

working_dir=`dirname "$0"`
install_dir="$HOME/.sshd"
kernel=`uname -s`
machine=`uname -m`
port="2222"

while getopts "i:m:p:s:w:x" opt
do
  case "$opt" in
    i)
      install_dir="$OPTARG"
      ;;
    m)
      machine="$OPTARG"
      ;;
    p)
      if [ "$OPTARG" -ge "1024" ]
      then
        if [ "$OPTARG" -lt "65536" ]
        then
          port="$OPTARG"
        fi
      fi
      ;;
    s)
      kernel="$OPTARG"
      ;;
    w)
      working_dir="$OPTARG"
      ;;
    x)
      set -x
      ;;
    *)
      echo "Usage : [-i install_dir] [-m machine] [-p port] [-s kernel] [-w working_dir] [-x]"
  esac
done

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
