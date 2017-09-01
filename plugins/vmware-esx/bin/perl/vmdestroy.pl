#!/usr/bin/perl -w
#
# this is a perl script to destroy a VM
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
      help => "The name of the VM to destroy",
      required => 0,
      default => "none",
   },
);

Opts::add_options(%opts);
Opts::parse();
# Opts::validate(\&validate);

Util::connect();
destroy_vms();
Util::disconnect();

sub destroy_vms {
	my $vmname = Opts::get_option('vmname');
	my $vm_views = Vim::find_entity_views(view_type => 'VirtualMachine', filter => { 'config.name' => $vmname });
	foreach (@$vm_views) {
		$_->Destroy ;
	}
	return 0;
}

