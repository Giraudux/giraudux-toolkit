#!/bin/bash
set -e

#note: compile openssh with LDFLAGS="-static"

working_dir=`dirname "$0"`
install_dir="$HOME/.sshd"
authorized_keys="authorized_keys"
kernel=`uname -s`
machine=`uname -m`
port="2222"

while getopts "i:k:m:p:s:w:x" opt
do
  case "$opt" in
    i)
      install_dir="$OPTARG"
      ;;
    k)
      authorized_keys="$OPTARG"
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
      echo "Usage : [-i install_dir] [-k authorized_keys] [-m machine] [-p port] [-s kernel] [-w working_dir] [-x]"
      exit 0
  esac
done

mkdir -p "$install_dir"
tar -xzf "$working_dir/openssh.tar.gz" "sshd-$kernel-$machine"
mv "$working_dir/sshd-$kernel-$machine" "$install_dir/sshd"
tar -xzf "$working_dir/openssh.tar.gz" "ssh-keygen-$kernel-$machine"
mv "$working_dir/ssh-keygen-$kernel-$machine" "$install_dir/ssh-keygen"
touch "$install_dir/authorized_keys"
if [ -f "$authorized_keys" ]
then
  cat "$authorized_keys" >> "$install_dir/authorized_keys"
fi
echo "Port $port" >> "$install_dir/sshd_config"
echo "HostKey $install_dir/ssh_host_key" >> "$install_dir/sshd_config"
echo "UsePrivilegeSeparation no" >> "$install_dir/sshd_config"
echo "PidFile $install_dir/sshd.pid" >> "$install_dir/sshd_config"
echo "AuthorizedKeysFile $install_dir/authorized_keys" >> "$install_dir/sshd_config"
"$install_dir/ssh-keygen" -N "" -f "$install_dir/ssh_host_key"
"$install_dir/sshd" -f "$install_dir/sshd_config"
