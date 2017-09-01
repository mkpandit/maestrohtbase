#!/usr/bin/perl -w
#
# this is a perl script to set the boot order of a VM
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

use lib "/usr/lib/vmware-vcli/apps/";
use lib "/usr/lib/vmware-vcli/VMware/share/";

use VMware::VIRuntime;
use AppUtil::VMUtil;
use Data::Dumper;

my %opts = (
	vmname => {
		type => "=s",
		help => "The name of the VM",
		required => 1,
	},
	bootorder => {
	  type => "=s",
	  help => "one of net, hd or cd",
	  required => 1,
	},
);

Opts::add_options(%opts);
Opts::parse();
Opts::validate();
Util::connect();

my %filterhash = ();
my $vmname = Opts::get_option('vmname');
my $bootorder = Opts::get_option('bootorder');
my $vm_view = Vim::find_entity_view(view_type => 'VirtualMachine', filter => {name => $vmname});
if($vm_view) {
	my $vm_config_spec = VirtualMachineConfigSpec->new(
		name => $vmname,
		extraConfig => [OptionValue->new( key => 'bios.bootDeviceClasses',
		value => $bootorder ),]
	);
    $vm_view->ReconfigVM( spec => $vm_config_spec );
}
Util::disconnect();

