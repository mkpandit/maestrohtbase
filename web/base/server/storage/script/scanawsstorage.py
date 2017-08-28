#!/usr/bin/env python

#Python class to scan AWS Storage

import sys, json
import os, gc
import boto3

class awsDisks:
    
    def __init__(self):
        self.author = "Manish Pandit, HTBase, mpandit@htbase.com"
    
    def awsDiskList(self):
        lines = []
        key_file = open('/usr/share/htvcenter/web/base/server/storage/script/aws.key')
        for line in key_file:
            lines.append(line.rstrip("\n"))
        key_file.close()
        
        awsaccesskeyid = lines[0].split(":")[1]
        awssecretaccesskey = lines[1].split(":")[1]
        
        s3 = boto3.resource('s3', aws_access_key_id = awsaccesskeyid, aws_secret_access_key = awssecretaccesskey)
        clientS3 = boto3.client('s3', aws_access_key_id = awsaccesskeyid, aws_secret_access_key = awssecretaccesskey,)
        
        storage_disks = []
        for bucket in s3.buckets.all():
            if bucket.name:
                storage_disks.append( str(bucket.name) + '_*_' + str(bucket.creation_date) )
        return storage_disks

if __name__ == "__main__":
    Disk = awsDisks()
    try:
        availableDisk = Disk.awsDiskList()
        print json.dumps(availableDisk, sort_keys=True, separators=(',', ': '))
        sys.exit(1)
    except Exception as e:
        print str(e)