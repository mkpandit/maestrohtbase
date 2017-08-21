#!/usr/bin/env python

import sys, json
import subprocess
import paramiko
import uuid
from time import sleep

def create_partition(ip, login, password, disk, htvcenter_SERVER):
    fdisk_options = '<<EOF\n'+password+'\no\nn\np\n1\n\n\nw\nEOF'
    xfs_options = 'mkfs.xfs -f '+disk+'p1'
    mkdir_parameter = 'mkdir -p /mnt/'
    blkid_parameter = 'blkid -s UUID -o value '+disk+'p1'

	# connecting to the SSH
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(ip, username=login, password=password)

    # creating partition
    stdin, stdout, stderr = ssh.exec_command('sudo -S ' + 'fdisk ' + disk + ' ' + fdisk_options, get_pty=True)

	# formating partition
    sleep(2)
    stdin, stdout, stderr = ssh.exec_command('sudo -S ' + xfs_options, get_pty=True)
    stdin.write(password+'\n')
    stdin.flush()

	# creating folder
    unique_filename = str(uuid.uuid4())
    stdin, stdout, stderr = ssh.exec_command('sudo -S ' + mkdir_parameter+unique_filename, get_pty=True)
    stdin.write(password+'\n')
    stdin.flush()

	# getting UUID
    stdout.flush()
    stdin, stdout, stderr = ssh.exec_command(blkid_parameter, get_pty=True)
    disk_uuid = stdout.read()
    disk_uuid = disk_uuid.strip()

	# creating disk on fstab
    fstab_parameter = 'UUID='+disk_uuid+' /mnt/'+unique_filename+' xfs defaults 0 1'
    stdin, stdout, stderr = ssh.exec_command('echo \''+fstab_parameter+'\' | sudo tee --append /etc/fstab', get_pty=True)
    stdin.write(password+'\n')
    stdin.flush()

	# Mount the disk
    sleep(2)
    #print('Mount: ' + 'sudo mount '+disk+'p1 /mnt/'+unique_filename)
    stdin, stdout, stderr = ssh.exec_command('sudo mount '+disk+'p1 /mnt/'+unique_filename, get_pty=True)
    stdin.write(password+'\n')
    stdin.flush()
    
    mount_message = stdout.read()
    #print(mount_message)
	# check if the disk mounted and show the result
    #stdin, stdout, stderr = ssh.exec_command('mountpoint /mnt/'+ unique_filename, get_pty=True)
    #print(stdout.read())
    stdin, stdout, stderr = ssh.exec_command('wget -q --no-check-certificate -O /tmp/chunk_htfs_integration http://'+htvcenter_SERVER+'/htvcenter/boot-service/chunk_htfs_integration', get_pty=True)
    stdin, stdout, stderr = ssh.exec_command('chmod +x /tmp/chunk_htfs_integration', get_pty=True)    
    stdin, stdout, stderr = ssh.exec_command('sudo bash /tmp/chunk_htfs_integration integrate -s '+htvcenter_SERVER+' -d /mnt/'+unique_filename+' > /tmp/loghtfs 2>&1', get_pty=True)
    stdin.write(password+'\n')
    stdin.flush()
    #print('Error: ' + stdout.read())
    #print('\n\n')   
    mount_message = mount_message + '\n' + stdout.read()
    return mount_message

if __name__ == "__main__":
    "Main diskCreation Function"
    parameters_size = len(sys.argv)
    
    if parameters_size != 6:
        sys.exit(1)
    
    mountMsg = []
    disk = '/dev/'+ sys.argv[4]
    mountdisk = create_partition(sys.argv[1], sys.argv[2], sys.argv[3], disk, sys.argv[5])
    #mountMsg.append(sys.argv[5])
    mountMsg.append(mountdisk)
    print json.dumps(mountMsg)
    sys.exit(0)
