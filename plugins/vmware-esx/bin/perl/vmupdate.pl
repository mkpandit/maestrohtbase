#!/usr/bin/perl -w
#
# this is a perl script to update cpu and memory of a VM
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
        mem_mb => {
                type => "=s",
                help => "Memory (MB)",
                required => 0,
        },
        cpu_cores => {
                type => "=s",
                help => "CPUs",
                required => 0,
        },
);


Opts::add_options(%opts);
Opts::parse();
# Opts::validate(\&validate);
my $vmname = Opts::get_option('vmname');
my $mem_mb = Opts::get_option('mem_mb');
my $cpu_cores = Opts::get_option('cpu_cores');
Util::connect();

my $vm_view = Vim::find_entity_view(
        view_type => 'VirtualMachine',
        filter => {
                'name' =>  $vmname
        }
);


if($vm_view) {
	print "Updating  for " . $vmname . "\n";
	eval {
		my ($memMB,$cpuCore,$vmSpec);
		if($mem_mb) {
			$memMB = (VirtualMachineConfigSpec->memoryMB => $mem_mb);
		}
		if($cpu_cores) {
			$cpuCore = (VirtualMachineConfigSpec->numCPUs => $cpu_cores);
		}
		if($mem_mb && !$cpu_cores) {
			$vmSpec = VirtualMachineConfigSpec->new(memoryMB => $memMB);
		} elsif (!$mem_mb && $cpu_cores) {
			$vmSpec = VirtualMachineConfigSpec->new(numCPUs => $cpuCore);
		} else {
			$vmSpec = VirtualMachineConfigSpec->new(numCPUs => $memMB, cpuAllocation => $cpuCore);
		}
		my $task_ref = $vm_view->ReconfigVM_Task(spec => $vmSpec);
		my $msg = "\tSuccessfully updated hardware for " . $vm_view->name . "\n";
		&getStatus($task_ref,$msg);
	};
	if($@) { print "Error: " . $@ . "\n"; }
} else {
	print "Unable to locate VM: \"" . $vmname . "\"\n";
}


sub getStatus {
	my ($taskRef,$message) = @_;
	my $task_view = Vim::get_view(mo_ref => $taskRef);
	my $taskinfo = $task_view->info->state->val;
	my $continue = 1;
	while ($continue) {
		my $info = $task_view->info;
		if ($info->state->val eq 'success') {
			print $message;
			$continue = 0;
		} elsif ($info->state->val eq 'error') {
			my $soap_fault = SoapFault->new;
			$soap_fault->name($info->error->fault);
			$soap_fault->detail($info->error->fault);
			$soap_fault->fault_string($info->error->localizedMessage);
			die "$soap_fault\n";
		}
		sleep 5;
		$task_view->ViewBase::update_view_data();
	}
} 


Util::disconnect();

