#!/usr/bin/env python3

# Alexis Giraudet 2016

# TODO: use ssl
# TODO: manage IPv6

import argparse
import logging
import os
import select
import socket
# import ssl
import sys
import termios
import tty


def main():
    fd_stdin = None
    termios_stdin = None

    try:
        logging.basicConfig(filename="amarklor_client.log", level=logging.DEBUG,
                            format="%(asctime)s: %(filename)s: %(lineno)d: %(funcName)s: %(process)d: %(thread)d: %(levelname)s: %(message)s")

        parser = argparse.ArgumentParser()
        parser.add_argument("--port", default="3333")
        parser.add_argument("--host", default="")
        args = parser.parse_args()
        print(args)

        host = str(args.host)
        port = int(args.port)

        logging.info("host={}".format(host))
        logging.info("port={}".format(port))

        logging.info("start Amarklor client")

        fd_stdin = sys.stdin.fileno()
        fd_stdout = sys.stdout.fileno()
        termios_stdin = termios.tcgetattr(fd_stdin)
        tty.setraw(fd_stdin, termios.TCSANOW)

        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as sock:
            sock.connect((host, port))
            fd_socket = sock.fileno()
            fds = [fd_stdin, fd_socket]
            while True:
                rfds, wfds, xfds = select.select(fds, [], [])
                if fd_stdin in rfds:
                    data = os.read(fd_stdin, 1024)
                    if not data:
                        break
                    else:
                        sock.sendall(data)
                if fd_socket in rfds:
                    data = sock.recv(1024)
                    if not data:
                        break
                    else:
                        while data:
                            n = os.write(fd_stdout, data)
                            data = data[n:]
    except Exception as e:
        logging.info("exception: {}".format(e))
    finally:
        if fd_stdin is not None and termios_stdin is not None:
            termios.tcsetattr(fd_stdin, termios.TCSANOW, termios_stdin)

    logging.info("stop Amarklor client")


if __name__ == "__main__":
    main()
