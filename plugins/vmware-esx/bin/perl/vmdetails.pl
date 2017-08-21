#!/usr/bin/perl -w
#
# this is a perl script to print out all config of a VM
#
# htvcenter Enterprise developed by htvcenter Enterprise GmbH.
#
# All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.
#
# This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
# The latest version of this license can be found here: http://htvcenter-enterprise.com/license
#
# By using this software, you acknowledge having read this license and agree to be bound thereby.
#
#           http://htvcenter-enterprise.com
#
# Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
#


use strict;
use warnings;

use FindBin;
use lib "/usr/lib/vmware-vcli/apps/";
use lib "/usr/lib/vmware-vcli/VMware/share/";

use VMware::VIRuntime;
use XML::LibXML;
use AppUtil::XMLInputUtil;
use AppUtil::HostUtil;


$Util::script_version = "1.0";

my %opts = (
	vmname => {
		type => "=s",
		help => "The VM name",
		required => 1,
	},
);

Opts::add_options(%opts);
Opts::parse();
# Opts::validate(\&validate);
my $vmname = Opts::get_option('vmname');
Util::connect();

my $vm_view = Vim::find_entity_view(
	view_type => 'VirtualMachine',
	filter => {
		'name' =>  $vmname
	}
);

# state
my $vm_state;
if ($vm_view->runtime->powerState->val eq 'poweredOn') {
	$vm_state = "active";
} else {
	$vm_state = "inactive";
}

# print Dumper($vm_view->config);

# mem
my $memsize = $vm_view->config->hardware->memoryMB;
# cpu
my $cpus = $vm_view->config->hardware->numCPU;
# guest id
my $vm_guest_id = $vm_view->config->guestId;
my $vm_guest_name = $vm_view->config->guestFullName;

# devices
my $devices = $vm_view->config->hardware->device;
my $nic_management_mac = '';
my $nic_management_type = '';
my $nic_management_vswitch = '';
my $nic_additional_mac1 = '';
my $nic_additional_type1 = '';
my $nic_additional_vswitch1 = '';
my $nic_additional_mac2 = '';
my $nic_additional_type2 = '';
my $nic_additional_vswitch2 = '';
my $nic_additional_mac3 = '';
my $nic_additional_type3 = '';
my $nic_additional_vswitch3 = '';
my $nic_additional_mac4 = '';
my $nic_additional_type4 = '';
my $nic_additional_vswitch4 = '';
my $nic_type = '';
my $nic_bridge = '';
my $devloop = 0;
my $vm_disk1_capacity = 0;
my $vm_disk1_filename = '';
my $vm_cdrom_filename = '';
my $vm_boot_dev = '';
my $vm_vnc_port = '';
my $vm_vnc_password = '';

foreach my $device (@$devices){

# debug
# print $device->deviceInfo->label;

	if($device->isa("VirtualEthernetCard")) {
		if ( $device->isa('VirtualE1000'))  {
			$nic_type = 'e1000';
			$nic_bridge = $device->backing->deviceName;
		} elsif ($device->isa('VirtualPCNet32')) {
			$nic_type = 'pcnet';
			$nic_bridge = $device->backing->deviceName;
		} elsif ($device->isa('VirtualVmxnet')) {
			$nic_type = 'vmxnet3';
			$nic_bridge = $device->backing->deviceName;
		}

		if ($devloop == 0) {
			$nic_management_mac = $device->macAddress;
			$nic_management_type = $nic_type;
			$nic_management_vswitch = $nic_bridge;
		} elsif ($devloop == 1) {
			$nic_additional_mac1 = $device->macAddress;
			$nic_additional_type1 = $nic_type;
			$nic_additional_vswitch1 = $nic_bridge;
		} elsif ($devloop == 2) {
			$nic_additional_mac2 = $device->macAddress;
			$nic_additional_type2 = $nic_type;
			$nic_additional_vswitch2 = $nic_bridge;
		} elsif ($devloop == 3) {
			$nic_additional_mac3 = $device->macAddress;
			$nic_additional_type3 = $nic_type;
			$nic_additional_vswitch3 = $nic_bridge;
		} elsif ($devloop == 4) {
			$nic_additional_mac4 = $device->macAddress;
			$nic_additional_type4 = $nic_type;
			$nic_additional_vswitch4 = $nic_bridge;
		}
		$devloop++;
	}

	# disk + cdrom
	if($device->isa("VirtualDisk")) {
		$vm_disk1_capacity = $device->capacityInKB/1024;
		$vm_disk1_filename = $device->backing->fileName;
	}
	if($device->isa("VirtualCdrom")) {
		$vm_cdrom_filename = $device->deviceInfo->summary;
	}
}


# extraconfigs
my $extra_config = $vm_view->config->extraConfig;
foreach my $option (sort @$extra_config) {
	if ( $option->key eq 'bios.bootDeviceClasses' )  {
		$vm_boot_dev = $option->value;
        }
	if ( $option->key eq 'RemoteDisplay.vnc.port' )  {
		$vm_vnc_port = $option->value;
	}
	if ( $option->key eq 'RemoteDisplay.vnc.password' )  {
		$vm_vnc_password = $option->value;
	}
}

# boot
my $vm_boot_dev_str = 'local';
if ($vm_boot_dev eq "allow:hd") {
	$vm_boot_dev_str = "local";
} elsif ($vm_boot_dev eq "allow:cd") {
	$vm_boot_dev_str = "cdrom";
} elsif ($vm_boot_dev eq "allow:net") {
	$vm_boot_dev_str = "network";
}
# print Dumper($extra_config);

print "htvcenter_VMWARE_ESX_VM_NAME=\"".$vmname."\"\n";
print "htvcenter_VMWARE_ESX_VM_STATE=\"".$vm_state."\"\n";
print "htvcenter_VMWARE_ESX_VM_CPUS=\"".$cpus."\"\n";
print "htvcenter_VMWARE_ESX_VM_RAM=\"".$memsize."\"\n";
print "htvcenter_VMWARE_ESX_VM_MAC=\"".$nic_management_mac."\"\n";
print "htvcenter_VMWARE_ESX_VM_NIC_TYPE=\"".$nic_management_type."\"\n";
print "htvcenter_VMWARE_ESX_VM_VSWITCH=\"".$nic_management_vswitch."\"\n";
print "htvcenter_VMWARE_ESX_VM_MAC2=\"".$nic_additional_mac1."\"\n";
print "htvcenter_VMWARE_ESX_VM_NIC_TYPE2=\"".$nic_additional_type1."\"\n";
print "htvcenter_VMWARE_ESX_VM_VSWITCH2=\"".$nic_additional_vswitch1."\"\n";
print "htvcenter_VMWARE_ESX_VM_MAC3=\"".$nic_additional_mac2."\"\n";
print "htvcenter_VMWARE_ESX_VM_NIC_TYPE3=\"".$nic_additional_type2."\"\n";
print "htvcenter_VMWARE_ESX_VM_VSWITCH3=\"".$nic_additional_vswitch2."\"\n";
print "htvcenter_VMWARE_ESX_VM_MAC4=\"".$nic_additional_mac3."\"\n";
print "htvcenter_VMWARE_ESX_VM_NIC_TYPE4=\"".$nic_additional_type3."\"\n";
print "htvcenter_VMWARE_ESX_VM_VSWITCH4=\"".$nic_additional_vswitch3."\"\n";
print "htvcenter_VMWARE_ESX_VM_MAC5=\"".$nic_additional_mac4."\"\n";
print "htvcenter_VMWARE_ESX_VM_NIC_TYPE5=\"".$nic_additional_type4."\"\n";
print "htvcenter_VMWARE_ESX_VM_VSWITCH5=\"".$nic_additional_vswitch4."\"\n";
print "htvcenter_VMWARE_ESX_VM_BOOT=\"".$vm_boot_dev_str."\"\n";
print "htvcenter_VMWARE_ESX_VM_VNC_PORT=\"".$vm_vnc_port."\"\n";
print "htvcenter_VMWARE_ESX_VM_VNC_PASSWORD=\"".$vm_vnc_password."\"\n";
print "htvcenter_VMWARE_ESX_VM_DISK1=\"".$vm_disk1_filename."\"\n";
print "htvcenter_VMWARE_ESX_VM_CAPACITY1=\"".$vm_disk1_capacity."\"\n";
print "htvcenter_VMWARE_ESX_VM_GUEST_ID=\"".$vm_guest_id."\"\n";
print "htvcenter_VMWARE_ESX_VM_GUEST_NAME=\"".$vm_guest_name."\"\n";
print "htvcenter_VMWARE_ESX_VM_CDROM_FILE=\"".$vm_cdrom_filename."\"\n";

Util::disconnect();




