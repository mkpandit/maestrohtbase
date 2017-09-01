#!/usr/bin/env python

import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('192.168.0.194', username='root', password='htbase')

stdin, stdout, stderr = client.exec_command('lsblk --output NAME,FSTYPE')

for line in stdout:
    print line.strip('\n')

client.close()