#!/usr/bin/env python

import os, sys, json
from azure.common.credentials import ServicePrincipalCredentials
from azure.mgmt.resource import ResourceManagementClient
from azure.mgmt.storage import StorageManagementClient
from azure.mgmt.network import NetworkManagementClient
from azure.mgmt.compute import ComputeManagementClient
from haikunator import Haikunator
from azure.mgmt.compute.models import DiskCreateOption
import configuration

if __name__ == "__main__":
    diskCreateMsg = []
    parameters_size = len(sys.argv)
    if parameters_size < 3:
        diskCreateMsg.append("Bad parameter combination")
        sys.exit(1)

    DISK_NAME = sys.argv[1]
    DISK_SIZE_GB = sys.argv[2]
    RESOURCE_GROUP = sys.argv[3]
    
    async_creation = configuration.compute_client.disks.create_or_update(
        RESOURCE_GROUP,
        DISK_NAME,
        {
            'location': 'westus',
            'disk_size_gb': DISK_SIZE_GB,
            'creation_data': {
                'create_option': DiskCreateOption.empty
            }
        }
    )
    disk_resource = async_creation.result()
    
    if async_creation:
        diskCreateMsg.append("Disk "+DISK_NAME+" created successfully")
        print json.dumps(diskCreateMsg, sort_keys=True, separators=(',', ': '))
    sys.exit(0)