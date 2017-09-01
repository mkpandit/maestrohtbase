#!/usr/bin/env python

#Python class to scan AWS Storage

import sys, json
import os, gc
import boto3

class awsEC2:
    
    def __init__(self):
        self.author = "Manish Pandit, HTBase, mpandit@htbase.com"
    
    def awsInstanceList(self):
        lines = []
        key_file = open('/usr/share/htvcenter/web/base/server/storage/script/aws.key')
        for line in key_file:
            lines.append(line.rstrip("\n"))
        key_file.close()
        
        awsaccesskeyid = lines[0].split(":")[1]
        awssecretaccesskey = lines[1].split(":")[1]
        session = boto3.Session(
            aws_access_key_id=awsaccesskeyid,
            aws_secret_access_key=awssecretaccesskey
        )
        ec2 = session.resource('ec2', region_name='us-east-1')
        instances = ec2.instances.filter()
        return instances

if __name__ == "__main__":
    AWSInstances = awsEC2()
    try:
        availableInstances = AWSInstances.awsInstanceList()
        instanceList = []
        for instance in availableInstances:
            if instance.tags:
                instance_name = instance.tags[0]['Value']
            else:
                instance_name = "None"
            instanceList.append(str(instance.id) + "_" + str(instance_name) + "_" + str(instance.public_ip_address) + "_" + str(instance.state) + "_" + str(instance.launch_time))
        print json.dumps(instanceList, sort_keys=True, separators=(',', ': '))
        sys.exit(1)
    except Exception as e:
        print str(e)