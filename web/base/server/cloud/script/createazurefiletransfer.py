#!/usr/bin/env python

import os, sys, json
from azure.common.credentials import ServicePrincipalCredentials
from azure.mgmt.resource import ResourceManagementClient
from azure.mgmt.storage import StorageManagementClient
from azure.mgmt.network import NetworkManagementClient
from azure.mgmt.compute import ComputeManagementClient
from haikunator import Haikunator
from azure.mgmt.compute.models import DiskCreateOption
import random, string
from random import randint
from azure.storage import CloudStorageAccount
from azure.storage.blob import BlockBlobService, PageBlobService, AppendBlobService

def randomcontainername(length):
    return ''.join(random.choice(string.ascii_lowercase) for i in range(length))

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
    
    fileTransgerMsg = []
    parameters_size = len(sys.argv)
    if parameters_size < 4:
        fileTransgerMsg.append("Bad parameter combination")
        sys.exit(1)
    
    GROUP_NAME = sys.argv[2]
    STORAGE_ACCOUNT_NAME = sys.argv[1]
    STORAGE_CONTAINER_NAME = sys.argv[3]
    FILE_FULL_PATH = sys.argv[4]
    storage_account = storage_client.storage_accounts.get_properties(GROUP_NAME, STORAGE_ACCOUNT_NAME)
    storage_keys = storage_client.storage_accounts.list_keys(GROUP_NAME, STORAGE_ACCOUNT_NAME)
    
    for k in storage_keys.keys:
        STORAGE_ACCOUNT_KEY = k.value
        
    blockblob_service = BlockBlobService(account_name=STORAGE_ACCOUNT_NAME, account_key=STORAGE_ACCOUNT_KEY)
    container_name = STORAGE_CONTAINER_NAME
    blockblob_service.create_container(STORAGE_CONTAINER_NAME)
    
    file_to_upload = FILE_FULL_PATH.split("/")[-1]
    full_path_to_file = FILE_FULL_PATH
    
    file_up = blockblob_service.create_blob_from_path(STORAGE_CONTAINER_NAME, file_to_upload, full_path_to_file)
    fileTransgerMsg.append(file_to_upload+" uploaded successfully")
    print json.dumps(fileTransgerMsg, sort_keys=True, separators=(',', ': '))