#!/usr/bin/env python3

# Alexis Giraudet

from ftplib import FTP
from sys import argv
from threading import Thread, Lock
from urllib.request import urlopen
from xml.etree import ElementTree

class ftp_checker:
	def __init__(self, timeout=3):
		self._io_mtx = Lock()
		self._timeout = int(timeout)
	def check_ftp_from_url(self, url):
	    with urlopen(url) as f:
	    	self.check_ftp_from_data(f)
	def check_ftp_from_data(self, data):
		try:
			thread_group = list()
			root = ElementTree.parse(data).getroot()
			for server in root.iter('Server'):
				t = Thread(target=self.check_ftp, args=(server.find('Host').text, server.find('Port').text, server.find('User').text, server.find('Pass').text, server.find('Protocol').text))
				thread_group.append(t)
				t.start()
			for server in root.iter('LastServer'):
				t = Thread(target=self.check_ftp, args=(server.find('Host').text, server.find('Port').text, server.find('User').text, server.find('Pass').text, server.find('Protocol').text))
				thread_group.append(t)
				t.start()
			for t in thread_group:
				t.join()
		except Exception as e:
			print('Exception: {}'.format(e))
	def check_ftp(self, host='', port='', user='', passwd='', protocol=''):
		pro = int()
		res = str()
		ftp = None
		try:
			if(pro == 0):
				ftp = FTP()
			elif(pro == 1):
				ftp = FTP_TLS()
			else:
				raise Exception('unknown protocol')
			ftp.connect(host, int(port), self._timeout)
			ftp.login(user, passwd)
			ftp.quit()
			res = 'OK'
		except Exception as e:
			ftp.close()
			res = 'Exception: {}'.format(e)
		with self._io_mtx:
			print('Check: {}@{}:{} {}'.format(user, host, port, passwd))
			print(res)
			print()

def main():
	if (len(argv) > 1):
		checker = ftp_checker()
		for i in range(1, len(argv)):
			checker.check_ftp_from_url(argv[i])
	else:
		print('Usage: ftp_checker.py [URL]...')

if __name__ == "__main__":
    main()
