from azure.mgmt.compute import ComputeManagementClient
from azure.mgmt.network import *
from azure.common.credentials import *
from azure.mgmt.compute.models import DiskCreateOption


'''
mpandit@htbase.com
Application ID: dee048ad-a200-41cf-9b4a-ce563aa06ecd
Client ID: dee048ad-a200-41cf-9b4a-ce563aa06ecd
Secret Key: Rv8LBsPbzPivFt5sw2fMW+77wIoD9hAlBfFS7AR4BYg=
Tenant ID: dfd034ad-c274-41ae-b40c-88199e6b7528
Subscription ID: 865ee318-9b61-4c3b-a0ce-83b84c976705
'''

'''
updatedmanish@live.com
Client ID: 5dcf6682-68f1-4295-a439-cdb49d97ae9b
Secret Key: /2caJj+ADd/WZw0nGUDHlQUKCM+SFxOFhTxxUrhWjss=
Tenant ID: 1df86eff-830c-4778-ba7a-1c1bd1d4a01a
Subscription ID: dd19825f-8f3d-4be8-9851-9f49a268dadb
'''

cred = UserPassCredentials('htbase_htbase.com#EXT#@updatedmanishlive.onmicrosoft.com', 'Manish@123')
subscription_id = '865ee318-9b61-4c3b-a0ce-83b84c976705'

credentials = ServicePrincipalCredentials(
    client_id='dee048ad-a200-41cf-9b4a-ce563aa06ecd', #os.environ['AZURE_CLIENT_ID'],
    secret='Rv8LBsPbzPivFt5sw2fMW+77wIoD9hAlBfFS7AR4BYg=', #os.environ['AZURE_CLIENT_SECRET'],
    tenant='dfd034ad-c274-41ae-b40c-88199e6b7528' #os.environ['AZURE_TENANT_ID']
)

compute_client = ComputeManagementClient(credentials,subscription_id)
network_client = NetworkManagementClient(credentials,subscription_id)


async_creation = compute_client.disks.create_or_update(
    'Maestro',
    'managed_disk_15_may',{
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