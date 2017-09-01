#!/usr/bin/env python

import os, sys, json
from azure.common.credentials import ServicePrincipalCredentials
from azure.mgmt.resource import ResourceManagementClient
from azure.mgmt.storage import StorageManagementClient
from azure.mgmt.network import NetworkManagementClient
from azure.mgmt.compute import ComputeManagementClient
from haikunator import Haikunator
from pprint import pprint

def createAzureVM():
    return ""

if __name__ == "__main__":
    lines = []
    key_file = open('/usr/share/htvcenter/web/base/server/storage/script/azure.key')
    for line in key_file:
        lines.append(line.rstrip("\n"))
    key_file.close()
        
    subscription_id = lines[0].split(":")[1]
    clientid = lines[1].split(":")[1]
    secretkey = lines[2].split(":")[1]
    tenantid = lines[3].split(":")[1]
    
    credentials = ServicePrincipalCredentials(client_id=clientid, secret=secretkey, tenant=tenantid)
    
    resource_client = ResourceManagementClient(credentials, subscription_id)
    compute_client = ComputeManagementClient(credentials, subscription_id)
    storage_client = StorageManagementClient(credentials, subscription_id)
    network_client = NetworkManagementClient(credentials, subscription_id)
    
    storageList = []
    for storage in storage_client.storage_accounts.list():
        storageList.append(storage.name + '_*_' + storage.location + '_*_' + str(storage.creation_time) + '_*_' + str(storage.encryption) + '_*_' + str(storage.type))
        #print storage.name + ' ' + storage.location
    print json.dumps(storageList, sort_keys=True, separators=(',', ': '))
