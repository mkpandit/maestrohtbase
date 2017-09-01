# simple program to do an imperative VM quick create from a platform image
# Arguments:
# -name [resource names are defaulted from this]
# -image
# -location [same location used for all resources]
import argparse
import azurerm
import json
from haikunator import Haikunator
import sys
import time

# validate command line arguments
argParser = argparse.ArgumentParser()

argParser.add_argument('--name', '-n', required=True, action='store', help='Name')
argParser.add_argument('--rgname', '-g', required=True, action='store', help='Resource Group Name')
argParser.add_argument('--location', '-l', required=True, action='store', help='Location, e.g. eastus')
argParser.add_argument('--verbose', '-v', action='store_true', default=False, help='Print operational details')

args = argParser.parse_args()

name = args.name
rgname = args.rgname
location = args.location


tenant_id = "dfd034ad-c274-41ae-b40c-88199e6b7528" #configData['tenantId']
app_id = "dee048ad-a200-41cf-9b4a-ce563aa06ecd" #configData['appId']
app_secret = "Rv8LBsPbzPivFt5sw2fMW+77wIoD9hAlBfFS7AR4BYg=" #configData['appSecret']
subscription_id = "865ee318-9b61-4c3b-a0ce-83b84c976705" #configData['subscriptionId']
#resource_group = "HTBase" #configData['resourceGroup']

# authenticate
access_token = azurerm.get_access_token(tenant_id, app_id, app_secret)

# initialize haikunator
h = Haikunator()

# create NSG
nsg_name = name + 'nsg'
print('Creating NSG: ' + nsg_name)
rmreturn = azurerm.create_nsg(access_token, subscription_id, rgname, nsg_name, location)
nsg_id = rmreturn.json()['id']
print('nsg_id = ' + nsg_id)

# create NSG rule
nsg_rule = 'ssh'
print('Creating NSG rule: ' + nsg_rule)
rmreturn = azurerm.create_nsg_rule(access_token, subscription_id, rgname, nsg_name, nsg_rule, description='ssh rule',
                                  destination_range='22')
print(rmreturn)
print(json.dumps(rmreturn.json(), sort_keys=False, indent=2, separators=(',', ': ')))

# create VNET
vnetname = name + 'vnet'
print('Creating VNet: ' + vnetname)
rmreturn = azurerm.create_vnet(access_token, subscription_id, rgname, vnetname, location, nsg_id=nsg_id)
print(rmreturn)
# print(json.dumps(rmreturn.json(), sort_keys=False, indent=2, separators=(',', ': ')))
subnet_id = rmreturn.json()['properties']['subnets'][0]['id']
print('subnet_id = ' + subnet_id)

# create public IP address
public_ip_name = name + 'ip'
dns_label = name + 'ip'
print('Creating public IP address: ' + public_ip_name)
rmreturn = azurerm.create_public_ip(access_token, subscription_id, rgname, public_ip_name, dns_label, location)
print(rmreturn)
ip_id = rmreturn.json()['id']
print('ip_id = ' + ip_id)

print('Waiting for IP provisioning..')
waiting = True
while waiting:
    ip = azurerm.get_public_ip(access_token, subscription_id, rgname, public_ip_name)
    if ip['properties']['provisioningState'] == 'Succeeded':
        waiting = False
    time.sleep(1)

# create NIC
nic_name = name + 'nic'
print('Creating NIC: ' + nic_name)
rmreturn = azurerm.create_nic(access_token, subscription_id, rgname, nic_name, ip_id, subnet_id, location)
#print(json.dumps(rmreturn.json(), sort_keys=False, indent=2, separators=(',', ': ')))
nic_id = rmreturn.json()['id']

print('Waiting for NIC provisioning..')
waiting = True
while waiting:
    nic = azurerm.get_nic(access_token, subscription_id, rgname, nic_name)
    if nic['properties']['provisioningState'] == 'Succeeded':
        waiting = False
    time.sleep(1)

# create VM
vm_name = name
vm_size = 'Standard_D1'
publisher = 'Canonical'
offer = 'UbuntuServer'
sku = '14.04.0-LTS'
version = 'latest'

os_uri = 'http://' + name + '.blob.core.windows.net/vhds/' + name + 'osdisk.vhd'

username = 'azure'
password = h.haikunate(delimiter=',') # creates random password
print('password = ' + password)
print('Creating VM: ' + vm_name)
rmreturn = azurerm.create_vm(access_token, subscription_id, rgname, vm_name, vm_size, publisher, offer, sku,
                             version, nic_id, location, username=username, password=password)
print(rmreturn)
print(json.dumps(rmreturn.json(), sort_keys=False, indent=2, separators=(',', ': ')))