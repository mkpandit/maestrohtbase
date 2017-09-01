#!/usr/bin/env python

import paramiko
import sys, json

IP_Address = sys.argv[1]

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(IP_Address, username='root', password='htbase')

stdin, stdout, stderr = client.exec_command('lsblk -o name,size,mountpoint -r -n')

driveList = []
for line in stdout:
    if "loop" not in line and "SWAP" not in line:
        #for items in line.split():
            #print items
        driveList.append(line)
    
print json.dumps(driveList)
client.close()
