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
    GROUP_NAME = sys.argv[2]
    OP_NAME = sys.argv[1]
    VM_NAME = sys.argv[3]
    storage_disks = []
    try:
        if OP_NAME == "info":
            virtual_machine = configuration.compute_client.virtual_machines.get( GROUP_NAME, VM_NAME )
            for items in virtual_machine.storage_profile.data_disks:
                storage_disks.append(str(items.name) + " / " + str(items.disk_size_gb) + " GB")
            managed_disk = configuration.compute_client.disks.list()
            disk_list = "DiskList"
            for items in managed_disk:
                disk_id = items.id
                disk_group = disk_id.split("/")[4]
                if disk_group == GROUP_NAME.upper():
                    if 'OsDisk' not in items.name:
                        disk_list = disk_list + '_*_' + items.name
            storage_disks.append(disk_list)
        if OP_NAME == "attach":
            DISK_NAME = sys.argv[4]
            data_disk = configuration.compute_client.disks.get(GROUP_NAME, DISK_NAME)
            virtual_machine = configuration.compute_client.virtual_machines.get( GROUP_NAME, VM_NAME )
            virtual_machine.storage_profile.data_disks.append({
                'lun': 16,
                'name': data_disk.name,
                'create_option': DiskCreateOption.attach,
                'managed_disk': {
                    'id': data_disk.id
                }
            })
            async_disk_attach = configuration.compute_client.virtual_machines.create_or_update(
                GROUP_NAME,
                virtual_machine.name,
                virtual_machine
            )
            async_disk_attach.wait()
            storage_disks.append( "Data disk " + DISK_NAME + " has been attached to VM " + VM_NAME)
            
    except Exception as e:
        storage_disks.append( str(e) )
    print json.dumps(storage_disks, sort_keys=True, separators=(',', ': '))
    sys.exit(0)