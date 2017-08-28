#!/usr/bin/env python

#Python class to find the Hosts

import sys, json
import os, gc

from azure.mgmt.compute import ComputeManagementClient
from azure.mgmt.network import *
from azure.common.credentials import *
from azure.mgmt.compute.models import DiskCreateOption

from haikunator import Haikunator
from azure.common.credentials import ServicePrincipalCredentials
from azure.mgmt.resource import ResourceManagementClient
from azure.mgmt.storage import StorageManagementClient
from azure.mgmt.storage.models import (
    StorageAccountCreateParameters,
    StorageAccountUpdateParameters,
    Sku,
    SkuName,
    Kind
)


class azureDisks:
    
    def __init__(self):
        self.author = "Manish Pandit, HTBase, mpandit@htbase.com"

    def print_item(self, group):
        print("\tName: {}".format(group.name))
        print("\tId: {}".format(group.id))
        print("\tLocation: {}".format(group.location))
        print("\tTags: {}".format(group.tags))
        print("\tSize: {}".format(group.disk_size_gb))
        if hasattr(group, 'properties'):
            self.print_properties(group.properties)

    def print_properties(self, props):
        if props and props.provisioning_state:
            print("\tProperties:")
            print("\t\tProvisioning State: {}".format(props.provisioning_state))
            print("\n\n")
    
    def azureDiskList(self, clientid, secretkey, tenantid, subscription_id):
        '''subscription_id = 'dd19825f-8f3d-4be8-9851-9f49a268dadb'
        credentials = ServicePrincipalCredentials(
        client_id='5dcf6682-68f1-4295-a439-cdb49d97ae9b', 
        secret='/2caJj+ADd/WZw0nGUDHlQUKCM+SFxOFhTxxUrhWjss=', 
        tenant='1df86eff-830c-4778-ba7a-1c1bd1d4a01a')'''
        
        #lines = [line.rstrip('\n') for line in open('azure.key')]
        '''lines = []
        key_file = open('azure.key')
        for line in key_file:
            lines.append(line.rstrip("\n"))
        key_file.close()
        
        subscription_id = lines[0].split(":")[1]
        clientid = lines[1].split(":")[1]
        secretkey = lines[2].split(":")[1]
        tenantid = lines[3].split(":")[1]'''
        
        '''subscription_id = '865ee318-9b61-4c3b-a0ce-83b84c976705'
        credentials = ServicePrincipalCredentials(
            client_id='dee048ad-a200-41cf-9b4a-ce563aa06ecd', 
            secret='Rv8LBsPbzPivFt5sw2fMW+77wIoD9hAlBfFS7AR4BYg=', 
            tenant='dfd034ad-c274-41ae-b40c-88199e6b7528')'''
        
        credentials = ServicePrincipalCredentials(client_id=clientid, secret=secretkey, tenant=tenantid)
        compute_client = ComputeManagementClient(credentials,subscription_id)
        resource_client = ResourceManagementClient(credentials, subscription_id)
        storage_client = StorageManagementClient(credentials, subscription_id)
        
        managed_disk = compute_client.disks.list()
        storage_disks = []
        for item in compute_client.disks.list():
            if item.name:
                storage_disks.append( str(item.name) + "_*_" +str(item.disk_size_gb) + "_*_" + str(item.location) + "_*_" + str(item.type) + "_*_" + str(item.os_type) )
        return storage_disks

if __name__ == "__main__":
    Disk = azureDisks()
    '''subscription_id = '865ee318-9b61-4c3b-a0ce-83b84c976705'
    clientid='dee048ad-a200-41cf-9b4a-ce563aa06ecd'
    secretkey='Rv8LBsPbzPivFt5sw2fMW+77wIoD9hAlBfFS7AR4BYg='
    tenantid='dfd034ad-c274-41ae-b40c-88199e6b7528' '''
    
    lines = []
    key_file = open('/usr/share/htvcenter/web/base/server/storage/script/azure.key')
    for line in key_file:
        lines.append(line.rstrip("\n"))
    key_file.close()
        
    subscription_id = lines[0].split(":")[1]
    clientid = lines[1].split(":")[1]
    secretkey = lines[2].split(":")[1]
    tenantid = lines[3].split(":")[1]
    del lines[:]
    gc.collect()
    
    try:
        availableDisk = Disk.azureDiskList(clientid, secretkey, tenantid, subscription_id)
        print json.dumps(availableDisk, sort_keys=True, separators=(',', ': '))
        sys.exit(1)
    except Exception as e:
        print str(e)