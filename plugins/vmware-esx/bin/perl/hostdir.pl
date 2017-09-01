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
        operation => {
                type => "=s",
                help => "[create|remove]",
                required => 1,
        },
        dirname => {
                type => "=s",
                help => "directory name",
                required => 1,
        },
        datastore => {
                type => "=s",
                help => "datastore name",
                required => 1,
        },

);


Opts::add_options(%opts);
Opts::parse();
my $operation = Opts::get_option('operation');
my $dirname = Opts::get_option('dirname');
my $datastore = Opts::get_option('datastore');
Opts::validate();
Util::connect();

my $content = Vim::get_service_content();
my $rf=Vim::get_view(mo_ref => $content->{fileManager});
if ($operation eq 'create') {
	my $myFolder = $rf->MakeDirectory(name => "[".$datastore."]".$dirname);
} elsif ($operation eq 'remove') {
	$rf->DeleteDatastoreFile_Task(name => "[".$datastore."]".$dirname);
}

     
Util::disconnect();

