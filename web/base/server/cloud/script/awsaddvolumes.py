#!/usr/bin/env python

import os, MySQLdb, json, sys
import boto3
from phpserialize import serialize, unserialize

class awsVolumes:
    
    def __init__(self):
        self.author = "Manish Pandit, HTBase, mpandit@htbase.com"

    def awsConnection(self):
        db = MySQLdb.connect(host="localhost", user="root", passwd="htbase", db="htvcenter")
        cur = db.cursor()
        cur.execute("SELECT * FROM cloud_credential WHERE id = 1")
        row = cur.fetchone()
        unserializedData = unserialize(row[2])
        awsaccesskeyid = unserializedData['aws_access_key_id']
        awssecretaccesskey = unserializedData['aws_secret_access_key']
        session = boto3.Session(aws_access_key_id=awsaccesskeyid, aws_secret_access_key=awssecretaccesskey, region_name='us-east-1')
        s3 = boto3.resource('s3', aws_access_key_id = awsaccesskeyid, aws_secret_access_key = awssecretaccesskey, region_name='us-east-1')
        client = boto3.resource('ec2', aws_access_key_id=awsaccesskeyid, aws_secret_access_key=awssecretaccesskey, region_name='us-east-1')
        ec2 = session.resource('ec2', region_name='us-east-1')
        return client

    def awsVolumeList(self):
        client = self.awsConnection()
        awsVolumes = []
        for v in client.volumes.all():
            awsVolumes.append(str(v.id) + "_*_" + str(v.size) + "_*_" + str(v.state) + "_*_" + str(v.volume_type))
        return awsVolumes
    def awsVolumeCreate(self, sizeingb, vtype):
        client = self.awsConnection()
        response = client.create_volume(AvailabilityZone='us-east-1a', Size=sizeingb, VolumeType=vtype)
        if response:
            return ["Volume created"]

if __name__ == "__main__":
    Disk = awsVolumes()
    awsVolumes = []
    sizeingb = int(sys.argv[1])
    vtype = sys.argv[2]
    try:
        vCreateMsg = Disk.awsVolumeCreate(sizeingb, vtype)
        awsVolumes = vCreateMsg
    except Exception as e:
        awsVolumes.append(str(e))
    print json.dumps(awsVolumes, sort_keys=True, separators=(',', ': '))
    sys.exit(1)