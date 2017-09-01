import os
from azure.common.credentials import ServicePrincipalCredentials
from azure.common.credentials import UserPassCredentials
from azure.mgmt.resource import ResourceManagementClient
from azure.mgmt.storage import StorageManagementClient
from azure.mgmt.storage.models import StorageAccountCreateParameters
from haikunator import Haikunator
from azure.mgmt.storage.models import (
    StorageAccountCreateParameters,
    StorageAccountUpdateParameters,
    Sku,
    SkuName,
    Kind
)

from azure.storage.blob import BlockBlobService
from azure.storage.blob import PublicAccess
from azure.storage.blob import ContentSettings

credentials = ServicePrincipalCredentials(
    client_id='5dcf6682-68f1-4295-a439-cdb49d97ae9b', #os.environ['AZURE_CLIENT_ID'],
    secret='/2caJj+ADd/WZw0nGUDHlQUKCM+SFxOFhTxxUrhWjss=', #os.environ['AZURE_CLIENT_SECRET'],
    tenant='1df86eff-830c-4778-ba7a-1c1bd1d4a01a' #os.environ['AZURE_TENANT_ID']
)

subscription_id = 'dd19825f-8f3d-4be8-9851-9f49a268dadb'
resource_client = ResourceManagementClient(credentials, subscription_id)
storage_client = StorageManagementClient(credentials, subscription_id)

'''
resource_group_params = {'location':'westus'}
GROUP_NAME = 'htbase'
STORAGE_ACCOUNT_NAME = Haikunator().haikunate(delimiter='')
storage_async_operation = storage_client.storage_accounts.create(
    GROUP_NAME,
    STORAGE_ACCOUNT_NAME,
    StorageAccountCreateParameters(sku=Sku(SkuName.standard_ragrs), kind=Kind.storage, location='westus')
)
storage_account = storage_async_operation.result()
'''

def print_item(group):
    """Print an Azure object instance."""
    print("\tName: {}".format(group.name))
    print("\tId: {}".format(group.id))
    print("\tLocation: {}".format(group.location))
    print("\tTags: {}".format(group.tags))
    if hasattr(group, 'properties'):
        print_properties(group.properties)

def print_properties(props):
    """Print a ResourceGroup properties instance."""
    if props and props.provisioning_state:
        print("\tProperties:")
        print("\t\tProvisioning State: {}".format(props.provisioning_state))
    print("\n\n")
    
GROUP_NAME = 'htbase'
STORAGE_ACCOUNT_NAME = 'luckyfirefly0971'

print('Get the account keys')
storage_keys = storage_client.storage_accounts.list_keys(GROUP_NAME, STORAGE_ACCOUNT_NAME)
storage_keys = {v.key_name: v.value for v in storage_keys.keys}
print('\tKey 1: {}'.format(storage_keys['key1']))
print('\tKey 2: {}'.format(storage_keys['key2']))
print("\n\n")


print('List storage accounts by resource group')
for item in storage_client.storage_accounts.list_by_resource_group(GROUP_NAME):
    print_item(item)
print("\n\n")

'''
block_blob_service = BlockBlobService(account_name=STORAGE_ACCOUNT_NAME, account_key=storage_keys['key1'])
block_blob_service.create_container(STORAGE_ACCOUNT_NAME+'-container', public_access=PublicAccess.Container)

block_blob_service.create_blob_from_path(
    STORAGE_ACCOUNT_NAME+'-container',
    'SunSet',
    'SUNSET.png',
    content_settings=ContentSettings(content_type='image/png')
)
            
generator = block_blob_service.list_blobs(STORAGE_ACCOUNT_NAME+'-container')
for blob in generator:
    print(blob.name)
'''