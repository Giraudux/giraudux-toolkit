#!/usr/bin/env python3

# Alexis Giraudet

from base64 import b64decode
from ftplib import FTP, FTP_TLS
from itertools import chain
from multiprocessing.pool import ThreadPool
from sqlite3 import connect
from sys import argv
from time import time
from urllib.request import urlopen
from xml.etree import ElementTree


#
#
#
def is_valid_ftp(host, port, user, passwd, protocol, timeout=8):
    host = str(host)
    port = int(port)
    user = str(user)
    passwd = str(passwd)
    protocol = int(protocol)

    try:
        if (protocol == 0):
            with FTP(timeout=timeout) as ftp:
                ftp.connect(host, port)
                ftp.login(user, passwd)
        elif (protocol == 1):
            with FTP_TLS(timeout=timeout) as ftp_tls:
                ftp_tls.connect(host, port)
                ftp_tls.login(user, passwd)
        else:
            raise Exception("unknown protocol: {}".format(protocol))

        return True
    except Exception as e:
        print(e)
        return False


#
#
#
def get_ftp_data(server_xml):
    try:
        host = server_xml.find("Host").text
        port = server_xml.find("Port").text
        user = server_xml.find("User").text
        protocol = server_xml.find("Protocol").text

        passwd = server_xml.find("Pass")
        if passwd.get("encoding") == "base64":
            passwd = b64decode(passwd.text.encode()).decode()
        else:
            passwd = passwd.text

        return {"host": host, "port": port, "user": user, "passwd": passwd, "protocol": protocol}
    except Exception as e:
        print(e)
        return None


#
#
#
def check_url(url, pool=None, timeout=8):
    try:
        ftps = list()

        if pool is None:
            pool = ThreadPool()

        with urlopen(url, timeout=timeout) as f:
            results = list()
            root = ElementTree.parse(f).getroot()

            for server in chain(root.iter('Server'), root.iter('LastServer')):
                ftp_data = get_ftp_data(server)
                if ftp_data is not None:
                    result = pool.apply_async(func=is_valid_ftp, kwds=ftp_data)
                    results.append((result, ftp_data))

            for result, ftp_data in results:
                result.wait()
                if result.get():
                    ftps.append(ftp_data)

        return ftps
    except Exception as e:
        print(e)
        return None


#
#
#
def check_urls(urls, pool=None, timeout=8):
    try:
        ftps = list()
        results = list()

        if pool is None:
            pool = ThreadPool()

        for url in urls:
            result = pool.apply_async(func=check_url, args=(url, pool, timeout))
            results.append(result)

        for result in results:
            result.wait()
            ftps_url = result.get()
            if ftps_url is not None:
                ftps.extend(ftps_url)

        return ftps
    except Exception as e:
        print(e)
        return None


#
#
#
def main():
    if (len(argv) > 1):
        ftps = check_urls(argv[1:], ThreadPool(64))
        if ftps:
            t = time()
            connection = connect("ftp.db")
            cursor = connection.cursor()
            cursor.execute(
                "CREATE TABLE IF NOT EXISTS ftp(time REAL, host TEXT, port INTEGER, user TEXT, passwd TEXT, protocol INTEGER)")

            for ftp in ftps:
                print(ftp)
                cursor.execute("INSERT INTO ftp(time, host, port, user, passwd, protocol) VALUES(?, ?, ?, ?, ?, ?)", (
                    float(t), str(ftp.get("host")), int(ftp.get("port")), str(ftp.get("user")), str(ftp.get("passwd")),
                    int(ftp.get("protocol"))))

            connection.commit()
            cursor.close()
            connection.close()
    else:
        print("Usage : {} [URL]...".format(argv[0]))


#
#
#
if __name__ == "__main__":
    main()
