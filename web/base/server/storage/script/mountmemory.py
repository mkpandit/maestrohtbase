#!/usr/bin/env python

import sys, json

if __name__ == "__main__":
    returnlist = []
    returnlist.append(str(sys.argv[1]))
    print json.dumps(returnlist, sort_keys=True, separators=(',', ': '))