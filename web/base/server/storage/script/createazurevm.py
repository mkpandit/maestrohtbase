#!/usr/bin/env python

import os, sys
from azure.common.credentials import ServicePrincipalCredentials
from azure.mgmt.resource import ResourceManagementClient
from azure.mgmt.storage import StorageManagementClient
from azure.mgmt.network import NetworkManagementClient
from azure.mgmt.compute import ComputeManagementClient
from haikunator import Haikunator

def createAzureVM():
    print "Manish"

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
            'vm_size': 'Standard_DS1'
        },
        'storage_profile': {
            'image_reference': {
                'publisher': vm_reference['publisher'],
                'offer': vm_reference['offer'],
                'sku': vm_reference['sku'],
                'version': vm_reference['version']
            },
            'os_disk': {
                'name': OS_DISK_NAME,
                'caching': 'None',
                'create_option': 'fromImage',
                'vhd': {
                    'uri': 'https://{}.blob.core.windows.net/vhds/{}.vhd'.format(
                        STORAGE_ACCOUNT_NAME, VM_NAME+haikunator.haikunate())
                }
            },
        },
        'network_profile': {
            'network_interfaces': [{
                'id': nic_id,
            }]
        },
    }


if __name__ == "__main__":
    haikunator = Haikunator()

    # Azure Datacenter
    LOCATION = 'westus'

    # Resource Group
    GROUP_NAME = 'Maestro'

    # Network
    VNET_NAME = 'HTBase-azure-vnet'
    SUBNET_NAME = 'HTBase-azure-subnet'

    # VM
    OS_DISK_NAME = 'HTBase-azure-osdisk'
    STORAGE_ACCOUNT_NAME = haikunator.haikunate(delimiter='')
    
    IP_CONFIG_NAME = 'HTBase-azure-ip-config'
    NIC_NAME = 'HTBase-azure-nic'
    USERNAME = 'htbase'
    PASSWORD = 'Htb@se123Manish'
    VM_NAME = 'HTBase-VM'
    
    VM_REFERENCE = {
        'linux': {
            'publisher': 'Canonical',
            'offer': 'UbuntuServer',
            'sku': '16.04.0-LTS',
            'version': 'latest'
        },
        'windows': {
            'publisher': 'MicrosoftWindowsServerEssentials',
            'offer': 'WindowsServerEssentials',
            'sku': 'WindowsServerEssentials',
            'version': 'latest'
        }
    }
    
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
    
    ###########
    # Prepare #
    ###########

    # Create Resource group
    #print('\nCreate Resource Group')
    #resource_client.resource_groups.create_or_update(GROUP_NAME, {'location':LOCATION})

    # Create a storage account
    print('\nCreate a storage account')
    storage_async_operation = storage_client.storage_accounts.create(
        GROUP_NAME,
        STORAGE_ACCOUNT_NAME,
        {
            'sku': {'name': 'standard_lrs'},
            'kind': 'storage',
            'location': LOCATION
        }
    )
    storage_async_operation.wait()
    
    # Create a NIC
    nic = create_nic(network_client)
    
    # Create Linux VM
    print('\nCreating Linux Virtual Machine')
    vm_parameters = create_vm_parameters(nic.id, VM_REFERENCE['linux'])
    async_vm_creation = compute_client.virtual_machines.create_or_update(
        GROUP_NAME, VM_NAME, vm_parameters)
    async_vm_creation.wait()
    
    # Tag the VM
    print('\nTag Virtual Machine')
    async_vm_update = compute_client.virtual_machines.create_or_update(
        GROUP_NAME,
        VM_NAME,
        {
            'location': LOCATION,
            'tags': {
                'who-rocks': 'Manish',
                'where': 'HTBase'
            }
        }
    )
    async_vm_update.wait()
    
    # Attach data disk
    print('\nAttach Data Disk')
    async_vm_update = compute_client.virtual_machines.create_or_update(
        GROUP_NAME,
        VM_NAME,
        {
            'location': LOCATION,
            'storage_profile': {
                'data_disks': [{
                    'name': 'HTBase-VM-data-disk',
                    'disk_size_gb': 100,
                    'lun': 0,
                    'vhd': {
                        'uri' : "http://{}.blob.core.windows.net/vhds/HTBase-VM-data-disk.vhd".format(
                            STORAGE_ACCOUNT_NAME)
                    },
                    'create_option': 'Empty'
                }]
            }
        }
    )
    async_vm_update.wait()
    
    print "Manish"
    sys.exit(0)
