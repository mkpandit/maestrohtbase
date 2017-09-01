#!/usr/bin/env python

import sys, json
import os, gc, pprint
import boto3
import awsec2manipulation

if __name__ == "__main__":
    awsEC2 = awsec2manipulation.awsEC2()
    ec2_client = awsEC2.awsConnectionClient('ec2')
    volumeID = []
    try:
        if sys.argv[1] != '':
            response = ec2_client.create_image(
                InstanceId = sys.argv[1],
                    Name = 'image-boto3-i-094ff81137c4c8d03'
            )
        volumeID.append(str(response))
    except Exception as e:
        volumeID.append(str(e))
    print json.dumps(volumeID, sort_keys=True, separators=(',', ': '))
    sys.exit(1)