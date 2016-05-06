#!/usr/bin/env python3

# Alexis Giraudet

from ftplib import FTP, FTP_TLS
from sys import argv
from threading import Thread, Lock
from urllib.request import urlopen
from xml.etree import ElementTree
from itertools import chain
from base64 import b64decode

#todo: use opt arg to read files/URLs parameters
#todo: manage base64 passwords
#todo: output results to sqlite, json or standard output
#todo: set max thread

class FileZillaXmlChecker:

    def __init__(self, database=None, timeout = 8):
        self._io_mtx = Lock()
        self._database = database
        self._timeout = timeout

    def checkUrls(self, urls):
        try:
            threads = list()

            for url in urls:
                thread = Thread(target = self.checkFromUrl, args = (url,))
                threads.append(thread)
                thread.start()

            for thread in threads:
                thread.join()
        except Exception as e:
            print(e)

    def checkFromUrl(self, url):
        try:
            with urlopen(url, timeout = self._timeout) as f:
                threads = list()
                root = ElementTree.parse(f).getroot()

                for server in chain(root.iter('Server'), root.iter('LastServer')):
                    thread = Thread(target = self.checkFromServer, args = (server,))
                    threads.append(thread)
                    thread.start()

                for thread in threads:
                    thread.join()
        except Exception as e:
            print(e)

    def checkFromServer(self, server):
        try:
            serverHost = server.find('Host').text
            serverPort = server.find('Port').text
            serverUser = server.find('User').text
            serverPass = server.find('User')
            serverProtocol = server.find('Protocol').text

            if serverPass.get("encoding") == "base64":
                serverPass = b64decode(serverPass.text.encode()).decode()
            else:
                serverPass = serverPass.text

            self.checkFtp(serverHost, serverPort, serverUser, serverPass, serverProtocol)

            print("SUCCESS {} {} {} {} {}".format(serverHost, serverPort, serverUser, serverPass, serverProtocol))
        except Exception as e:
            print(e)

    def checkFtp(self, host, port, user, passwd, protocol):
        port = int(port)
        protocol = int(protocol)
        try:
            if(protocol == 0):
                with FTP(timeout = self._timeout) as ftp:
                    ftp.connect(host, port)
                    ftp.login(user, passwd)
            elif(protocol == 1):
                with FTP_TLS(timeout = self._timeout) as ftp_tls:
                    ftp_tls.connect(host, port)
                    ftp_tls.login(user, passwd)
            else:
                raise Exception("unknown protocol: {}".format(protocol))
        except Exception as e:
            print(e)

def main():
    if (len(argv) > 1):
        checker = FileZillaXmlChecker()
        checker.checkUrls(argv[1:])
    else:
        print("Usage : {} [URL]...".format(argv[0]))

if __name__ == "__main__":
    main()
