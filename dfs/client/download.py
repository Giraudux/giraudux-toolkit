#!/usr/bin/env python3

import argparse, base64, hashlib, json, urllib.parse, urllib.request

def download_http_get_b64_sha512(meta):
  raise Exception()

def main():
  parser = argparse.ArgumentParser()
  parser.add_argument("-m", "--meta", type=argparse.FileType("r"), required=True)
  # output path
  parser.add_argument("-f", "--file", type=argparse.FileType("wb"), required=True)
  parser.add_argument("-t", "--threads", default=8, type=int, required=False)
  args = parser.parse_args()
  meta = json.load(args.meta)
  args.meta.close()
  size = 0
  file_sha512 = hashlib.sha512()
  for piece in meta["pieces"]:
    block = None
    for provider in piece["providers"]:
      try:
        # use getattr(module, provider["protocol"])
        block = download_http_get_b64_sha512(provider["meta"])
      except:
        block = None
        continue
      if len(block) == piece["size"] and hashlib.sha512(block).hexdigest() == piece["hash"]:
        block = None
        break
    size += len(block)
    file_sha512.update(block)
    if args.file.write(block) != len(block):
      raise Exception()
  if size != meta["size"] or file_sha512.hexdigest() != meta["hash"]:
    raise Exception()
  args.file.close()

if __name__ == "__main__":
  main()