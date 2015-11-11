#!/usr/bin/env python3

import argparse, base64, hashlib, json, urllib.parse, urllib.request

class Uploader:
  def protocol():
    raise Exception()
  def upload(self, meta, block):
    raise Exception()

class HttpB64Sha512Uploader(Uploader):
  def protocol():
    return "http_b64_sha512"
  def upload(self, meta, block):
    block_b64 = base64.urlsafe_b64encode(block)
    block_b64_sha512 = hashlib.sha512(block_b64).hexdigest()
    data = urllib.parse.urlencode({"data": block_b64}).encode("ascii")
    request = urllib.request.Request(meta["post"])
    request.add_header("Content-Type", "application/x-www-form-urlencoded;charset=ascii")
    with urllib.request.urlopen(request, data) as reply:
      if block_b64_sha512 == reply.read().decode("ascii"):
        return {"hash": block_b64_sha512, "get": meta["get"]+"?id="+block_b64_sha512}
    raise Exception()

def main():
  parser = argparse.ArgumentParser()
  parser.add_argument("-f", "--file",      type=argparse.FileType("rb"), required=True)
  parser.add_argument("-m", "--meta",      type=argparse.FileType("w"),  required=True)
  parser.add_argument("-p", "--providers", type=argparse.FileType("r"),  required=True)
  parser.add_argument("-b", "--block",     type=int,                     required=False, default=785664)
  parser.add_argument("-t", "--threads",   type=int,                     required=False, default=8)
  args = parser.parse_args()
  providers = json.load(args.providers)
  args.providers.close()
  uploaders = dict()
  uploaders[HttpB64Sha512Uploader.protocol()] = HttpB64Sha512Uploader()
  size = 0
  meta = dict()
  pieces = list()
  file_sha512 = hashlib.sha512()
  block = args.file.read(args.block)
  while block:
    file_sha512.update(block)
    size += len(block)
    piece = dict()
    piece_providers = list()
    for provider in providers:
      try:
        protocol_meta = uploaders[provider["protocol"]].upload(provider["meta"], block)
        piece_providers.append({"protocol": provider["protocol"], "meta": protocol_meta})
      except:
        continue
    piece["size"] = len(block)
    piece["hash"] = hashlib.sha512(block).hexdigest()
    piece["providers"] = piece_providers
    pieces.append(piece)
    block = args.file.read(args.block)
  meta["name"] = args.file.name
  meta["size"] = size
  meta["hash"] = file_sha512.hexdigest()
  meta["pieces"] = pieces
  json.dump(meta, args.meta)
  args.file.close()
  args.meta.close()

if __name__ == "__main__":
  main()
