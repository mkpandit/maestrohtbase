#!/usr/bin/env python

import boto3
import json, sys

def createAWSInstance(ami_id, min_count, max_count, instance_type):
    lines = []
    key_file = open('/usr/share/htvcenter/web/base/server/storage/script/aws.key')
    for line in key_file:
        lines.append(line.rstrip("\n"))
    key_file.close()
    awsaccesskeyid = lines[0].split(":")[1]
    awssecretaccesskey = lines[1].split(":")[1]
    
    session = boto3.Session(aws_access_key_id=awsaccesskeyid, aws_secret_access_key=awssecretaccesskey)
    ec2 = session.resource('ec2', region_name='us-east-1')
    instances = ec2.create_instances(
        ImageId=ami_id, 
        MinCount=min_count, 
        MaxCount=max_count, 
        InstanceType=instance_type,
        KeyName='HTBaseEC2',
        SecurityGroupIds=['sg-34255845', 'sg-d227efad'],
    )
    if instances:
        return "Instance(s) created successfully"
    else:
        return "Instance(s) not created"

if __name__ == "__main__":
    parameters_size = len(sys.argv)
    if parameters_size != 5:
        sys.exit(1)
    instanceCreateMsg = []
    createInstance = createAWSInstance(sys.argv[1], int(sys.argv[2]), int(sys.argv[3]), sys.argv[4])
    instanceCreateMsg.append(createInstance)
    print json.dumps(instanceCreateMsg)
    sys.exit(0)
