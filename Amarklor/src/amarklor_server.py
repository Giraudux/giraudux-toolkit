#!/usr/bin/env python3

# Alexis Giraudet 2016

# TODO: use daemon
# TODO: use ssl
# TODO: manage IPv6

import argparse
# import daemon
import logging
import os
import pty
import select
import socket
# import ssl
import threading


def run(sock):
    logging.info("start client")

    socket_fd = sock.fileno()
    pid, master_fd = pty.fork()
    if pid == 0:
        try:
            logging.info("execute bash")
            os.execlp("/bin/bash", "-")
        except Exception as e:
            logging.info("exception: {}".format(e))
    else:
        try:
            logging.info("read/write socket/master")
            fds = [master_fd, socket_fd]
            while True:
                rfds, wfds, xfds = select.select(fds, [], [])
                if master_fd in rfds:
                    data = os.read(master_fd, 1024)
                    if not data:
                        break
                    else:
                        sock.sendall(data)
                if socket_fd in rfds:
                    data = sock.recv(1024)
                    if not data:
                        break
                    else:
                        while data:
                            n = os.write(master_fd, data)
                            data = data[n:]
        except Exception as e:
            logging.info("exception:".format(e))
        finally:
            logging.info("close socket/master and wait child")
            os.close(master_fd)
            sock.close()
            os.waitpid(pid, 0)

    logging.info("stop client")


def main():
    try:
        logging.basicConfig(filename="amarklor_server.log", level=logging.DEBUG,
                            format="%(asctime)s: %(filename)s: %(lineno)d: %(funcName)s: %(process)d: %(thread)d: %(levelname)s: %(message)s")

        parser = argparse.ArgumentParser()
        parser.add_argument("--port", default="3333")
        parser.add_argument("--host", default="")
        args = parser.parse_args()

        host = str(args.host)
        port = int(args.port)

        logging.info("host={}".format(host))
        logging.info("port={}".format(port))

        logging.info("start Amarklor server")

        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            s.bind((host, port))
            s.listen(128)
            while True:
                sock, addr = s.accept()
                logging.info("accept new connection: {}".format(addr))
                thread = threading.Thread(target=run, args=(sock,))
                thread.start()
    except Exception as e:
        logging.info("exception: {}".format(e))

    logging.info("stop Amarklor server")


if __name__ == "__main__":
    main()
