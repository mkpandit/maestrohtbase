from azure.mgmt.compute.models import (
    Sku,
    DiskCreateOption
)
from azure.mgmt.compute import ComputeManagementClient
from azure.mgmt.network import NetworkManagementClient
from azure.common.credentials import UserPassCredentials

''' from azure.common.credentials import ServicePrincipalCredentials
credentials = ServicePrincipalCredentials(
    client_id='5dcf6682-68f1-4295-a439-cdb49d97ae9b', #os.environ['AZURE_CLIENT_ID'],
    secret='/2caJj+ADd/WZw0nGUDHlQUKCM+SFxOFhTxxUrhWjss=', #os.environ['AZURE_CLIENT_SECRET'],
    tenant='1df86eff-830c-4778-ba7a-1c1bd1d4a01a' #os.environ['AZURE_TENANT_ID']
) '''

cred = UserPassCredentials('htbase_htbase.com#EXT#@updatedmanishlive.onmicrosoft.com', 'Manish@123')
subscription_id = 'dd19825f-8f3d-4be8-9851-9f49a268dadb'

compute_client = ComputeManagementClient(cred, subscription_id)
network_client = NetworkManagementClient(cred, subscription_id)


async_creation = compute_client.disks.create_or_update(
    'htbase',
    'managed_disk_python',{
        'location': 'westus',
        'disk_size_gb': 35,
        'creation_data': {
            'create_option': DiskCreateOption.empty
        }
    }
)

disk_resource = async_creation.result()

print ("Azure Sucks!")

#import jsonpickle # pip install jsonpickle
#import json

#serialized = jsonpickle.encode(compute_client)
#print json.dumps(json.loads(serialized), indent=2)