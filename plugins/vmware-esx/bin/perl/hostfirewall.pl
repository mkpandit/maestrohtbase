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


use strict;
use warnings;
use FindBin;
use lib "/usr/lib/vmware-vcli/apps/";
use lib "/usr/lib/vmware-vcli/VMware/share/";

use VMware::VILib;
use VMware::VIRuntime;

$SIG{__DIE__} = sub{Util::disconnect();};

my %opts = (
   vmhost => {
      type => "=s",
      help => "The name of the ESX Host",
      required => 1,
      default => "none",
   },
);

Opts::add_options(%opts);
Opts::parse();
Opts::validate();
Util::connect();
&listfirewall();
Util::disconnect();

sub listfirewall {
	my $host_name =  Opts::get_option('vmhost');
	my $host = Vim::find_entity_view(view_type => 'HostSystem');
	my $fw_ruleset = $host->config->firewall->ruleset;
	foreach(@$fw_ruleset) {
		 my $rules = $_->rule;
		 if($_->enabled) {
			 print $_->label, "\n";
#			 foreach(@$rules) {
#				print "Direction: ", $_->direction->val, "\n";
#				print "End Port: ", $_->endPort, "\n";
#				print "Port: ", $_->port, "\n";
#				print "Protocol: ", $_->protocol, "\n";
#			  }

		 }
	}
}




