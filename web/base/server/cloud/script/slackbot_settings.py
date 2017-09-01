#!/usr/bin/env python

import json, sys, os
import awsvolumes

if __name__ == "__main__":
    Disk = awsvolumes.awsVolumes()
    awsVolumes = []
    try:
        awsVolumes = Disk.awsVolumeList()
    except Exception as e:
        awsVolumes.append(str(e))
    print json.dumps(awsVolumes, sort_keys=True, separators=(',', ': '))
    sys.exit(1)