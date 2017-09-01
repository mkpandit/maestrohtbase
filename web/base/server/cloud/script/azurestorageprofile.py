#!/usr/bin/env python

import azure
import azure.mgmt.compute
import azure.mgmt.network
import azure.mgmt.resource
import azure.mgmt.storage

import time
import random
import string
import os
from base64 import b64decode

from azure.common.credentials import ServicePrincipalCredentials
from azure.mgmt.resource import ResourceManagementClient
from azure.mgmt.resource.resources import ResourceManagementClient, ResourceManagementClientConfiguration
from azure.mgmt.storage import StorageManagementClient
from azure.mgmt.network import NetworkManagementClient
from azure.mgmt.compute import ComputeManagementClient

def run_example():
    async_create_image = compute_client.images.create_or_update(
        'HTBase',
        'genImageMaestro',
        {
            'location': 'canadaeast',
            'storage_profile': {
                'os_disk': {
                    'os_type': 'Linux',
                    'os_state': "Generalized",
                    'blob_uri': 'https://snowymud3178.blob.core.windows.net/htbase/mestroazuresnapshot.vhd',
                    'caching': "ReadWrite",
                }
            }
        }
    )
    image = async_create_image.result()
    
if __name__ == "__main__":
    #run_example()
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

    BASE_NAME = "HT-VM001"

    GROUP_NAME = 'HTBase'
    STORAGE_NAME = "snowymud3178"
    VIRTUAL_NETWORK_NAME = BASE_NAME + '-VNET'
    SUBNET_NAME = BASE_NAME + '-SUBNET'
    NETWORK_INTERFACE_NAME = BASE_NAME + '-NIC'
    VM_NAME = BASE_NAME
    OS_DISK_NAME = BASE_NAME + '-OSDisk'
    PUBLIC_IP_NAME = BASE_NAME + '-IP'
    COMPUTER_NAME = BASE_NAME

    REGION = 'canadaeast'

    ADMIN_USERNAME='htbase'
    ADMIN_PASSWORD='Htb@se123321'

    # Create Resource Group
    result = resource_client.resource_groups.create_or_update(
        GROUP_NAME,
        azure.mgmt.resource.resources.models.ResourceGroup(
            location=REGION,
        ),
    )

    # Create Storage Group
    result = storage_client.storage_accounts.create(
        GROUP_NAME,
        STORAGE_NAME,
        azure.mgmt.storage.models.StorageAccountCreateParameters(
            location=REGION,
            account_type=azure.mgmt.storage.models.AccountType.standard_lrs,
        ),
    )
    result.wait()



    def create_network_interface(network_client, region, group_name, interface_name, network_name, subnet_name, ip_name):
        result = network_client.virtual_networks.create_or_update(
            group_name,
            network_name,
            azure.mgmt.network.models.VirtualNetwork(
                location=region,
                address_space=azure.mgmt.network.models.AddressSpace(
                    address_prefixes=[
                        '10.1.0.0/16',
                    ],
                ),
                subnets=[
                    azure.mgmt.network.models.Subnet(
                        name=subnet_name,
                        address_prefix='10.1.0.0/24',
                    ),
                ],
            ),
        )
        result.wait()
        subnet = network_client.subnets.get(group_name, network_name, subnet_name)
        result = network_client.public_ip_addresses.create_or_update(
            group_name,
            ip_name,
            azure.mgmt.network.models.PublicIPAddress(
                location=region,
                public_ip_allocation_method=azure.mgmt.network.models.IPAllocationMethod.dynamic,
                idle_timeout_in_minutes=4,
            ),
        )
        result.wait()
        public_ip_address = network_client.public_ip_addresses.get(group_name, ip_name)
        public_ip_id = public_ip_address.id
        result = network_client.network_interfaces.create_or_update(
            group_name,
            interface_name,
            azure.mgmt.network.models.NetworkInterface(
                name=interface_name,
                location=region,
                ip_configurations=[
                    azure.mgmt.network.models.NetworkInterfaceIPConfiguration(
                        name='default',
                        private_ip_allocation_method=azure.mgmt.network.models.IPAllocationMethod.dynamic,
                        subnet=subnet,
                        public_ip_address=azure.mgmt.network.models.PublicIPAddress(
                            id=public_ip_id,
                        ),
                    ),
                ],
            ),
        )
        time.sleep(30)  # result.wait() throws errors, but need to wait before getting the ID
        network_interface = network_client.network_interfaces.get(
            group_name,
            interface_name,
        )
        return network_interface.id


    nic_id = create_network_interface(
        network_client,
        REGION,
        GROUP_NAME,
        NETWORK_INTERFACE_NAME,
        VIRTUAL_NETWORK_NAME,
        SUBNET_NAME,
        PUBLIC_IP_NAME,
    )

    result = compute_client.virtual_machines.create_or_update(
        GROUP_NAME,
        VM_NAME,
        azure.mgmt.compute.models.VirtualMachine(
            location=REGION,
            name=VM_NAME,
            os_profile=azure.mgmt.compute.models.OSProfile(
                admin_username=ADMIN_USERNAME,
                admin_password=ADMIN_PASSWORD,
                computer_name=COMPUTER_NAME,
            ),
            hardware_profile=azure.mgmt.compute.models.HardwareProfile(
              vm_size="Basic_A0"
            ),
            network_profile=azure.mgmt.compute.models.NetworkProfile(
                network_interfaces=[
                    azure.mgmt.compute.models.NetworkInterfaceReference(
                        id=nic_id,
                    ),
                ],
            ),
            storage_profile=azure.mgmt.compute.models.StorageProfile(
                os_disk=azure.mgmt.compute.models.OSDisk(
                    caching=azure.mgmt.compute.models.CachingTypes.none,
                    create_option=azure.mgmt.compute.models.DiskCreateOptionTypes.from_image,
                    name=OS_DISK_NAME,
                    os_type="Linux",
                    vhd=azure.mgmt.compute.models.VirtualHardDisk(
                        uri='https://{0}.blob.core.windows.net/vhds/{1}.vhd'.format(
                            STORAGE_NAME, OS_DISK_NAME,
                        ),
                    ),
                    image=azure.mgmt.compute.models.VirtualHardDisk(
                        uri='https://snowymud3178.blob.core.windows.net/htbase/mestroazuresnapshot.vhd'
                    ),
                )
            ),
        ),
    )
    try:
        result.wait()
    finally:
        import ipdb; ipdb.set_trace()
        print result._response.text