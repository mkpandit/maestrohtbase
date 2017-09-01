#!/usr/bin/perl -w
#
# this is a perl list iso images on the hosts datastore
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

use POSIX qw(ceil floor);
use strict;
use warnings;
use FindBin;
use lib "/usr/lib/vmware-vcli/apps/";
use lib "/usr/lib/vmware-vcli/VMware/share/";

use Getopt::Long;
use VMware::VIRuntime;
use VMware::VILib;
use VMware::VIExt;

my @options = (
    ['list'],
);


my %opts = (
   vmhost => {
      alias => "h",
      type => "=s",
      help => qq!    The host to use when connecting to the ESX Host!,
      required => 0,
   },
   'list' => {
      alias => "l",
      type => "",
      help => qq!    List vmnics of the ESX Host!,
      required => 0,
   },

);



Opts::add_options(%opts);
Opts::parse();
Opts::validate();
Util::connect();

my $host_view = VIExt::get_host_view(1);
Opts::assert_usage(defined($host_view), "Invalid host.");
my $network_system = Vim::get_view (mo_ref => $host_view->configManager->networkSystem);

if (defined OptVal('list')) {
	getPnicName ($network_system);
}

Util::disconnect();


sub OptVal {
	my $opt = shift;
	return Opts::get_option($opt);
}


sub getPnicName {
	my ($network_system) = @_;
	my $pNics = $network_system->networkInfo->pnic;
	#print Dumper($pNics);
	foreach my $pNic (@$pNics) {
		print $pNic->device."\n";
	}
}
