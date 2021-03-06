#!/usr/bin/env python3

import argparse, base64, hashlib, json, urllib.request

class Downloader:
  def protocol():
    raise Exception()
  def download(self, meta):
    raise Exception()

class HttpB64Sha512Downloader(Downloader):
  def protocol():
    return "http_b64_sha512"
  def download(self, meta):
    block_b64 = None
    with urllib.request.urlopen(meta["get"]) as reply:
      block_b64 = reply.read()
    if hashlib.sha512(block_b64).hexdigest() == meta["hash"]:
      return base64.urlsafe_b64decode(block_b64)
    raise Exception()

def main():
  parser = argparse.ArgumentParser()
  parser.add_argument("-m", "--meta", type=argparse.FileType("r"), required=True)
  # output path
  parser.add_argument("-f", "--file", type=argparse.FileType("wb"), required=True)
  parser.add_argument("-t", "--threads", default=8, type=int, required=False)
  args = parser.parse_args()
  meta = json.load(args.meta)
  downloaders = dict()
  downloaders[HttpB64Sha512Downloader.protocol()] = HttpB64Sha512Downloader()
  args.meta.close()
  size = 0
  file_sha512 = hashlib.sha512()
  for piece in meta["pieces"]:
    block = None
    for provider in piece["providers"]:
      try:
        block = downloaders[provider["protocol"]].download(provider["meta"])
      except:
        continue
      if len(block) == piece["size"] and hashlib.sha512(block).hexdigest() == piece["hash"]:
        break
      else:
        block = None
    size += len(block)
    file_sha512.update(block)
    if args.file.write(block) != len(block):
      raise Exception()
  if size != meta["size"] or file_sha512.hexdigest() != meta["hash"]:
    raise Exception()
  args.file.close()

if __name__ == "__main__":
  main()
