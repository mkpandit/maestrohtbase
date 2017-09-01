#!/usr/bin/perl -w
#
# this is a perl script to print out main config of all VMs
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
	hostname => {
		type => "=s",
		help => "The ESX hostname",
		required => 1,
	},
);

Opts::add_options(%opts);
Opts::parse();
# Opts::validate(\&validate);
my $hostname = Opts::get_option('hostname');
Util::connect();

my $host_view = Vim::find_entity_view(view_type => 'HostSystem');
my $vm_views = Vim::find_entity_views(view_type => 'VirtualMachine', begin_entity => $host_view );

foreach my $vm (@$vm_views) {

	# state
	my $vm_state;
	if ($vm->runtime->powerState->val eq 'poweredOn') {
		$vm_state = "active";
	} else {
		$vm_state = "inactive";
	}

	# mem
	my $memsize = $vm->config->hardware->memoryMB;
	# cpu
	my $cpus = $vm->config->hardware->numCPU;
	# guest id
	my $vm_guest_id = $vm->config->guestId;

	# devices
	my $devices = $vm->config->hardware->device;
	my $nic_management = '';
	my $nic_additional = '';
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

		if($device->isa("VirtualEthernetCard")) {
			if ( $device->isa('VirtualE1000'))  {
				$nic_type = 'VirtualE1000';
				$nic_bridge = $device->backing->deviceName;
			} elsif ($device->isa('VirtualPCNet32')) {
				$nic_type = 'VirtualPCNet32';
				$nic_bridge = $device->backing->deviceName;
			} elsif ($device->isa('VirtualVmxnet')) {
				$nic_type = 'VirtualVmxnet';
				$nic_bridge = $device->backing->deviceName;
			}
			if ($devloop == 0) {
				$nic_management = $device->macAddress . ",". $nic_type . ",". $nic_bridge;
			} else {
				$nic_additional = $nic_additional . "/" . $device->macAddress . ",". $nic_type . ",". $nic_bridge;
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
	my $extra_config = $vm->config->extraConfig;
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


	print $vm->name."@".$vm_state."@".$cpus."@".$memsize."@".$nic_management."@".$nic_additional."@".$hostname."@".$vm_guest_id."@".$vm_disk1_filename."@".$vm_disk1_capacity."@".$vm_vnc_password."@".$vm_vnc_port."@".$vm_boot_dev_str."@".$vm_cdrom_filename."@\n";

}


Util::disconnect();




