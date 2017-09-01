#!/usr/bin/env python

import boto3
import json, sys

def createAWSBucket(bucketName):
    lines = []
    key_file = open('/usr/share/htvcenter/web/base/server/storage/script/aws.key')
    for line in key_file:
        lines.append(line.rstrip("\n"))
    key_file.close()
    awsaccesskeyid = lines[0].split(":")[1]
    awssecretaccesskey = lines[1].split(":")[1]
    
    session = boto3.Session(aws_access_key_id=awsaccesskeyid, aws_secret_access_key=awssecretaccesskey)
    aws3 = session.resource('s3', region_name='us-east-1')
    if aws3.create_bucket(Bucket=bucketName):
        return "Bucket created Successfully"
    else:
        return "Bucket not created"

if __name__ == "__main__":
    parameters_size = len(sys.argv)
    if parameters_size != 2:
        sys.exit(1)
    bucketCreateMsg = []
    createBucket = createAWSBucket(sys.argv[1])
    bucketCreateMsg.append(createBucket)
    print json.dumps(bucketCreateMsg)
    sys.exit(0)
