#!/bin/bash

# Alexis Giraudet

# args -port [PORT] -ssh-keygen [SSH-KEYGEN] -sshd [SSHD]

sshkeygen_bin="ssh-keygen"
sshd_bin="sshd"
sshd_dir="$HOME/.sshd"
sshd_config="$sshd_dir/sshd_config"
sshd_port="2222"
sshd_hostkey="$sshd_dir/ssh_host_key"
sshd_useprivilegeseparation="no"
sshd_pidfile="$sshd_dir/sshd.pid"

# clear/prepare files
#rm -rf $sshd-dir
mkdir $sshd_dir

# prepare sshd config file
echo "Port $sshd_port" >> $sshd_config
echo "HostKey $sshd_hostkey" >> $sshd_config
echo "UsePrivilegeSeparation $sshd_useprivilegeseparation" >> $sshd_config
echo "PidFile $sshd_pidfile" >> $sshd_config

# generate host key
$sshkeygen_bin -f $sshd_hostkey -N ""

# run sshd
#$sshd_bin -f sshd-config
