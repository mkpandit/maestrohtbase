#!/usr/bin/env python

import sys, json
import os, gc, pprint
import boto3
import awsec2manipulation

if __name__ == "__main__":
    AWSInstances = awsec2manipulation.awsEC2()
    try:
        volumeID = []
        if sys.argv[1] == 'update':
            awsEC2Client = AWSInstances.awsConnectionClient()
            
            if ( (sys.argv[3] != "") and (sys.argv[4] != "") and (sys.argv[5] != "") and (sys.argv[6] != "") ):
                response = awsEC2Client.modify_volume(VolumeId=sys.argv[3], Size=int(sys.argv[4]), VolumeType=sys.argv[6], Iops=int(sys.argv[5]))
            else:
                volumeID.append("Invalid Parameter Combination.")

            if response:
                volumeID.append("Disk updated successfully.")
        else:
            ec2Details = AWSInstances.processDictionary(AWSInstances.describeInstance(sys.argv[1]))
            for items in ec2Details:
                temp = items.split("->")
                if temp[0] == "VolumeId":
                    volumeID.append("Volume ID: " + temp[1])
                    awsVolumeID = temp[1]

            awsEC2 = AWSInstances.awsConnectionResource()
            awsVolume = awsEC2.Volume(awsVolumeID)
        
            volumeID.append("Disk size: "+str(awsVolume.size) + " GB")
            volumeID.append("IOPS: "+str(awsVolume.iops))
            volumeID.append("Volume State: " + str(awsVolume.state))

            if awsVolume.volume_type == "gp2":
                volumeID.append("Volume Type: General Purpose SSD - "+ str(awsVolume.volume_type))
            elif awsVolume.volume_type == "io1":
                volumeID.append("Volume Type: Provisioned IOPS SSD - "+ str(awsVolume.volume_type))
            elif awsVolume.volume_type == "st1":
                volumeID.append("Volume Type: Throughput Optimized HDD - "+ str(awsVolume.volume_type))
            elif awsVolume.volume_type == "sc1":
                volumeID.append("Volume Type: Cold HDD - "+ str(awsVolume.volume_type))
            else:
                volumeID.append("Volume Type: "+ str(awsVolume.volume_type))

    except Exception as e:
        volumeID.append(str(e))
    print json.dumps(volumeID, sort_keys=True, separators=(',', ': '))
    sys.exit(1)