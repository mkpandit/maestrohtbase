#!/usr/bin/env python

import os, sys, json
from azure.common.credentials import ServicePrincipalCredentials
from azure.mgmt.resource import ResourceManagementClient
from azure.mgmt.storage import StorageManagementClient
from azure.mgmt.network import NetworkManagementClient
from azure.mgmt.compute import ComputeManagementClient
from haikunator import Haikunator
from azure.mgmt.compute.models import DiskCreateOption

import azure
import azure.mgmt.compute
import azure.mgmt.network
import azure.mgmt.resource
import azure.mgmt.storage

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


GROUP_NAME = 'HTBase'
NIC_NAME = 'VMFromSnapShot223'
VNET_NAME = 'VMFromSnapShot223'
SUBNET_NAME = 'VMFromSnapShot223'
IP_CONFIG_NAME = 'VMFromSnapShot223'
LOCATION = 'canadaeast'
VM_NAME = 'VMFromSnapShot223'
OS_DISK_NAME = 'VMFromSnapShot223DISK'
USERNAME = 'htbase'
PASSWORD = 'Htb@se123321'
VM_REFERENCE = {
    'linux': {
        'publisher': 'Canonical',
        'offer': 'UbuntuServer',
        'sku': '14.04.5-LTS',
        'version': 'latest'
    },
    'windows': {
        'publisher': 'MicrosoftWindowsServerEssentials',
        'offer': 'WindowsServerEssentials',
        'sku': 'WindowsServerEssentials',
        'version': 'latest'
    }
}

def create_nic(network_client):
    """Create a Network Interface for a VM.
    """
    # Create VNet
    print('\nCreate Vnet')
    async_vnet_creation = network_client.virtual_networks.create_or_update(
        GROUP_NAME,
        VNET_NAME,
        {
            'location': LOCATION,
            'address_space': {
                'address_prefixes': ['10.0.0.0/16']
            }
        }
    )
    async_vnet_creation.wait()

    # Create Subnet
    print('\nCreate Subnet')
    async_subnet_creation = network_client.subnets.create_or_update(
        GROUP_NAME,
        VNET_NAME,
        SUBNET_NAME,
        {'address_prefix': '10.0.0.0/24'}
    )
    subnet_info = async_subnet_creation.result()

    # Create NIC
    print('\nCreate NIC')
    async_nic_creation = network_client.network_interfaces.create_or_update(
        GROUP_NAME,
        NIC_NAME,
        {
            'location': LOCATION,
            'ip_configurations': [{
                'name': IP_CONFIG_NAME,
                'subnet': {
                    'id': subnet_info.id
                }
            }]
        }
    )
    return async_nic_creation.result()

def create_vm_parameters(nic_id, vm_reference):
    """Create the VM parameters structure.
    """
    return {
        'location': LOCATION,
        'os_profile': {
            'computer_name': VM_NAME,
            'admin_username': USERNAME,
            'admin_password': PASSWORD
        },
        'hardware_profile': {
            'vm_size': 'Standard_DS1_v2'
        },
        'storage_profile': {
            'image_reference': {
                'publisher': vm_reference['publisher'],
                'offer': vm_reference['offer'],
                'sku': vm_reference['sku'],w
                'version': vm_reference['version']
            },
        },
        'network_profile': {
            'network_interfaces': [{
                'id': nic_id,
            }]
        },
    }

if __name__ == "__main__":
    nic = create_nic(network_client)
    
    vm_parameters = create_vm_parameters(nic.id, VM_REFERENCE['linux'])
    async_vm_creation = compute_client.virtual_machines.create_or_update(
                GROUP_NAME, VM_NAME, 
                azure.mgmt.compute.models.VirtualMachine(
                        location=LOCATION,
                        name=VM_NAME,
                        os_profile=azure.mgmt.compute.models.OSProfile(
                            admin_username=USERNAME,
                            admin_password=PASSWORD,
                            computer_name=VM_NAME,
                        ),
                        hardware_profile=azure.mgmt.compute.models.HardwareProfile(
                          vm_size="Standard_A1"
                        ),
                        network_profile=azure.mgmt.compute.models.NetworkProfile(
                            network_interfaces=[
                                azure.mgmt.compute.models.NetworkInterfaceReference(
                                    id=nic.id,
                                ),
                            ],
                        ),
                        storage_profile=azure.mgmt.compute.models.StorageProfile(
                            os_disk=azure.mgmt.compute.models.OSDisk(
                                caching=azure.mgmt.compute.models.CachingTypes.none,
                                create_option=azure.mgmt.compute.models.DiskCreateOptionTypes.from_image,
                                os_type="Linux",
                                vhd=azure.mgmt.compute.models.VirtualHardDisk(
                                    uri='https://snowymud3178.blob.core.windows.net/htbase/mestroazuresnapshot.vhd',
                                ),
                            )
                        ),
                    ),
    )
    async_vm_creation.wait()
    
    '''async_creation = compute_client.disks.create_or_update(
        'HTBase',
        'HTBaseSnapShotDisk004',
        {
            'location': 'westus',
            'creation_data': {
                'create_option': DiskCreateOption.import_enum,
                'source_uri': 'https://witheredmountain7795.blob.core.windows.net/htbasecontainer/maestroonazuresnapshot.vhd'
            }
        }
    )
    disk_resource = async_creation.result()
    
    print('\nGet Virtual Machine by Name')
    virtual_machine = compute_client.virtual_machines.get(
                GROUP_NAME,
                VM_NAME
    )

    # Attach data disk
    print('\nAttach Data Disk')
    managed_disk = compute_client.disks.get(GROUP_NAME, 'HTBaseSnapShotDisk004')
    
    virtual_machine.storage_profile.data_disks.append({
                'lun': 12,
                'name': 'HTBaseSnapShotDisk004',
                'create_option': DiskCreateOption.attach,
                'managed_disk': {
                    'id': managed_disk.id
                }
            })
            
    async_disk_attach = compute_client.virtual_machines.create_or_update(
                GROUP_NAME,
                virtual_machine.name,
                virtual_machine
            )
    async_disk_attach.wait() '''