#!/usr/bin/env python

import os, sys, json
from azure.common.credentials import ServicePrincipalCredentials
from azure.mgmt.resource import ResourceManagementClient
from azure.mgmt.storage import StorageManagementClient
from azure.mgmt.network import NetworkManagementClient
from azure.mgmt.compute import ComputeManagementClient
from haikunator import Haikunator

def create_nic(network_client):
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

    async_subnet_creation = network_client.subnets.create_or_update(
        GROUP_NAME,
        VNET_NAME,
        SUBNET_NAME,
        {'address_prefix': '10.0.0.0/24'}
    )
    subnet_info = async_subnet_creation.result()

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
        },
        'network_profile': {
            'network_interfaces': [{
                'id': nic_id,
            }]
        },
    }


if __name__ == "__main__":
    haikunator = Haikunator()
    vmCreateMsg = []
    parameters_size = len(sys.argv)
    if parameters_size < 5:
        vmCreateMsg.append("Bad parameter combination")
        sys.exit(1)
    
    LOCATION = 'westus'
    GROUP_NAME = sys.argv[9] #'Maestro'
    
    VNET_NAME = sys.argv[2] #'HTBase-azure-vnet'
    SUBNET_NAME = sys.argv[3] #'HTBase-azure-subnet'

    # VM
    OS_DISK_NAME = sys.argv[4] #'HTBase-azure-osdisk'
    STORAGE_ACCOUNT_NAME = haikunator.haikunate(delimiter='')
    
    IP_CONFIG_NAME = sys.argv[5] #'HTBase-azure-ip-config'
    NIC_NAME = sys.argv[6] #'HTBase-azure-nic'
    USERNAME = sys.argv[7] #'htbase'
    PASSWORD = sys.argv[8] #'Htb@se123Manish'
    VM_NAME = sys.argv[1] #'HTBase-VM'
    
    VM_REFERENCE = {
        'linux': {'publisher': 'Canonical', 'offer': 'UbuntuServer', 'sku': '16.04.0-LTS', 'version': 'latest'},
        'windows': {'publisher': 'MicrosoftWindowsServerEssentials', 'offer': 'WindowsServerEssentials', 'sku': 'WindowsServerEssentials', 'version': 'latest'}
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
    
    # Create a storage account
    storage_async_operation = storage_client.storage_accounts.create(
        GROUP_NAME,
        STORAGE_ACCOUNT_NAME,
        {'sku': {'name': 'standard_lrs'}, 'kind': 'storage', 'location': LOCATION }
    )
    
    storage_async_operation.wait()
    
    # Create a NIC
    nic = create_nic(network_client)
    
    # Create Linux VM
    vm_parameters = create_vm_parameters(nic.id, VM_REFERENCE['linux'])
    
    async_vm_creation = compute_client.virtual_machines.create_or_update(
        GROUP_NAME, VM_NAME, vm_parameters)
    async_vm_creation.wait()
    
    # Tag the VM
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
    
    if async_vm_creation:
        vmCreateMsg.append("VM "+VM_NAME+" created successfully")
        print json.dumps(vmCreateMsg, sort_keys=True, separators=(',', ': '))
    sys.exit(0)
