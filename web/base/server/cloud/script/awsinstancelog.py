#!/usr/bin/env python

import sys, json
import os, gc, pprint
import boto3
import awsec2manipulation

if __name__ == "__main__":
    AWSInstances = awsec2manipulation.awsEC2()
    try:
        awsLog = AWSInstances.awsConnectionClient('logs')
        log = awsLog.describe_log_streams(
            logGroupName='HTBaseCloudWatch'
        )
    except Exception as e:
        volumeID.append(str(e))
    print json.dumps(AWSInstances.processDictionary(log), sort_keys=True, separators=(',', ': '))
    sys.exit(1)