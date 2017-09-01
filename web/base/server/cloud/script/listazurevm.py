#!/usr/bin/env python

import os, sys, json
import configuration

if __name__ == "__main__":
    vmList = []
    count = 0
    for vm in configuration.compute_client.virtual_machines.list_all():
        vmID = vm.id
        resourceGroup = vmID.split("/")[4]
        v_m = configuration.compute_client.virtual_machines.get(resourceGroup, vm.name, expand = 'instanceview')
        vmList.append(str(vm.name) + '_*_' + str(vm.location) + '_*_' + str(vm.storage_profile.image_reference.sku) + '_*_' + str(vm.vm_id) + '_*_' + str(vm.type) + '_*_' + str(v_m.instance_view.statuses[1].display_status) + '_*_' + str(resourceGroup))
    print json.dumps(vmList, sort_keys=True, separators=(',', ': '))
