#!/usr/bin/env python3

import argparse, base64, hashlib, json, urllib.parse, urllib.request

def upload_http_post_b64_sha512(block, meta):
  block_b64 = base64.urlsafe_b64encode(block)
  block_b64_sha512 = hashlib.sha512(block_b64).hexdigest()
  data = urllib.parse.urlencode({"data": block_b64})
  data = data.encode("ascii")
  request = urllib.request.Request(meta["post"])
  request.add_header("Content-Type", "application/x-www-form-urlencoded;charset=ascii")
  with urllib.request.urlopen(request, data) as reply:
    if block_b64_sha512 == reply.read().decode():
      return {"protocol": "http_get_b64_sha512", "hash": block_b64_sha512, "get": meta["get"]+"?id="+block_b64_sha512}
  raise Exception("Failure")

def main():
  parser = argparse.ArgumentParser()
  parser.add_argument("-b", "--block", default=785664, type=int, required=False)
  parser.add_argument("-i", "--input", type=argparse.FileType("rb"), required=True)
  parser.add_argument("-o", "--output", type=argparse.FileType("w"), required=True)
  parser.add_argument("-p", "--providers", type=argparse.FileType("r"), required=True)
  parser.add_argument("-t", "--threads", default=8, type=int, required=False)
  args = parser.parse_args()
  providers = json.load(args.providers)
  args.providers.close()
  output = dict()
  size = 0
  pieces = list()
  hash = hashlib.sha512()
  block = args.input.read(args.block)
  while block:
    hash.update(block)
    size += len(block)
    piece = dict()
    piece_providers = list()
    for provider in providers:
      try:
        # use getattr(module, provider["protocol"])
        piece_providers.append(upload_http_post_b64_sha512(block, provider["meta"]))
      except:
        print()
    piece["size"] = len(block)
    piece["hash"] = hashlib.sha512(block).hexdigest()
    piece["providers"] = piece_providers
    pieces.append(piece)
    block = args.input.read(args.block)
  output["name"] = args.input.name
  output["size"] = size
  output["hash"] = hash.hexdigest()
  output["pieces"] = pieces
  json.dump(output, args.output)
  args.input.close()
  args.output.close()

if __name__ == "__main__":
  main()
