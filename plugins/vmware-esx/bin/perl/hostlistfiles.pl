#!/usr/bin/perl -w
#
# this is a perl list files (e.g. .iso or .vmdk) on the hosts datastore
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
   type => {
      type => "=s",
      help => "The file type (e.g. iso/vmdk)",
      required => 1,
      default => "none",
   },
);

Opts::add_options(%opts);
Opts::parse();
Opts::validate();
Util::connect();
&listfiles();
Util::disconnect();

sub listfiles {
		my $host_name = Opts::get_option('vmhost');
		my $file_type = Opts::get_option('type');
		my $host = Vim::find_entity_view(view_type => 'HostSystem');
        my $datastores =  Vim::get_views(mo_ref_array => $host->datastore);
        foreach my $ds (@$datastores) {
		my $browser = Vim::get_view (mo_ref => $ds->browser);
		my $ds_path = "[" . $ds->info->name . "]";
		my $file_query = FileQueryFlags->new(fileOwner => 0, fileSize => 0,fileType => 0,modification => 0);
		my $searchSpec = HostDatastoreBrowserSearchSpec->new(details => $file_query,matchPattern => ["*.$file_type"]);
		my $search_res = $browser->SearchDatastoreSubFolders(datastorePath => $ds_path,searchSpec => $searchSpec);
		foreach my $result (@$search_res) {
			my $files = $result->file;
			foreach my $file (@$files) {
				print $result->folderPath ."/". $file->path . "\n";
			}
		}

	}
}
