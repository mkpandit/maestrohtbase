#!/usr/bin/env python

import os, sys, json
import configuration

if __name__ == "__main__":
    vmList = []
    OPERATION = sys.argv[1]
    GROUP_NAME = sys.argv[2]
    VM_NAME = sys.argv[3]
    try:
        if OPERATION == 'stop':
            async_vm_stop = configuration.compute_client.virtual_machines.power_off(GROUP_NAME, VM_NAME)
            async_vm_stop.wait()
            vmList.append(VM_NAME + " has been stopped")
        elif OPERATION == 'start':
            async_vm_start = configuration.compute_client.virtual_machines.start(GROUP_NAME, VM_NAME)
            async_vm_start.wait()
            vmList.append(VM_NAME + " has been started")
        elif OPERATION == 'terminate':
            async_vm_delete = configuration.compute_client.virtual_machines.delete(GROUP_NAME, VM_NAME)
            async_vm_delete.wait()
            vmList.append(VM_NAME + " has been terminated")
        elif OPERATION == 'dupdate':
            virtual_machine = configuration.compute_client.virtual_machines.get(GROUP_NAME, VM_NAME)
            async_vm_deallocate = configuration.compute_client.virtual_machines.deallocate(GROUP_NAME, VM_NAME)
            async_vm_deallocate.wait()
            os_disk_name = virtual_machine.storage_profile.os_disk.name
            os_disk = configuration.compute_client.disks.get(GROUP_NAME, os_disk_name)
            if not os_disk.disk_size_gb:
                os_disk.disk_size_gb = 30
            os_disk.disk_size_gb = sys.argv[4]
            async_disk_update = configuration.compute_client.disks.create_or_update(GROUP_NAME, os_disk.name, os_disk)
            async_disk_update.wait()
            async_vm_restart = configuration.compute_client.virtual_machines.restart(GROUP_NAME, VM_NAME)
            async_vm_restart.wait()
            vmList.append( "Disk Size has been updated to "+str(sys.argv[4])+" GB")
        elif OPERATION == 'disk':
            virtual_machine = configuration.compute_client.virtual_machines.get(GROUP_NAME, VM_NAME)
            os_disk_name = virtual_machine.storage_profile.os_disk.name
            os_disk = configuration.compute_client.disks.get(GROUP_NAME, os_disk_name)
            if not os_disk.disk_size_gb:
                os_disk.disk_size_gb = 30
                vmList.append( "Disk Size:"+str(30) )
            else:
                vmList.append( "Disk Size:"+str(os_disk.disk_size_gb) )
        else:
            vm = configuration.compute_client.virtual_machines.get(GROUP_NAME, VM_NAME, expand = 'instanceview')
            vmList.append( "Identity->"+str(vm.identity) )
            vmList.append( "Admin User name->"+str(vm.os_profile.admin_username) )
            vmList.append( "Admin Password->"+str(vm.os_profile.admin_password) )
            vmList.append( "Computer Name->"+str(vm.os_profile.computer_name) )
            vmList.append( "Custom Data->"+str(vm.os_profile.custom_data) )
            vmList.append( "Linux Configuration->"+str(vm.os_profile.linux_configuration) )
            vmList.append( "Secrets->"+str(vm.os_profile.secrets) )
            vmList.append( "Windows Configurations->"+str(vm.os_profile.windows_configuration) )
            vmList.append( "Data Disks->"+str(vm.storage_profile.data_disks) )
            vmList.append( "Image Reference->"+str(vm.storage_profile.image_reference) )
            vmList.append( "OS Disk->"+str(vm.storage_profile.os_disk) )
            vmList.append( "Availability Set->"+str(vm.availability_set) )
            vmList.append( "Name->"+str(vm.name) )
            vmList.append( "Tags->"+str(vm.tags) )
            vmList.append( "Boot Diagnostics->"+str(vm.diagnostics_profile.boot_diagnostics) )
            vmList.append( "VM Size->"+str(vm.hardware_profile.vm_size) )
            vmList.append( "Provisioning State->"+str(vm.provisioning_state) )
            vmList.append( "Network Interface->"+str(vm.network_profile.network_interfaces) )
            vmList.append( "Plan->"+str(vm.plan) )
            vmList.append( "License Type->"+str(vm.license_type) )
            vmList.append( "VM Agent Version->"+str(vm.instance_view.vm_agent.vm_agent_version) )
            vmList.append( "Status Code->"+str(vm.instance_view.statuses[1].code) )
            vmList.append( "Display Status->"+str(vm.instance_view.statuses[1].display_status) )
            vmList.append( "Status Message->"+str(vm.instance_view.statuses[1].message) )
            vmList.append( "VM Type->"+str(vm.type) )
            vmList.append( "VM ID->"+str(vm.id) )
            vmList.append( "VM Location->"+str(vm.location) )
            #print json.dumps(vmList, sort_keys=True, separators=(',', ': '))
    except Exception as e:
        vmList.append( str(e) )
    print json.dumps(vmList, sort_keys=True, separators=(',', ': '))
    sys.exit(1)