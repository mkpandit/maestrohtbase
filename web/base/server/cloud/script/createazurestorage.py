#!/usr/bin/env python

import os, sys, json
from azure.common.credentials import ServicePrincipalCredentials
from azure.mgmt.resource import ResourceManagementClient
from azure.mgmt.storage import StorageManagementClient
from azure.mgmt.network import NetworkManagementClient
from azure.mgmt.compute import ComputeManagementClient
from haikunator import Haikunator
from azure.mgmt.compute.models import DiskCreateOption

if __name__ == "__main__":
    storageCreateMsg = []
    parameters_size = len(sys.argv)
    if parameters_size < 2:
        storageCreateMsg.append("Bad parameter combination")
        sys.exit(1)
    
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
    
    STORAGE_ACCOUNT_NAME = sys.argv[1]
    GROUP_NAME = sys.argv[2]
    storage_async_operation = storage_client.storage_accounts.create(
        GROUP_NAME,
        STORAGE_ACCOUNT_NAME,
        {
            'sku': {'name': 'standard_lrs'},
            'kind': 'storage',
            'location': 'westus'
        }
    )
    storage_async_operation.wait()
    
    if storage_async_operation:
        storageCreateMsg.append("Storage Account "+STORAGE_ACCOUNT_NAME+" created successfully")
        print json.dumps(storageCreateMsg, sort_keys=True, separators=(',', ': '))
    sys.exit(0)