#!/usr/bin/env python

import sys, json
import os

lines = [line.rstrip('\n') for line in open('azure.key')]
subscription_id = lines[0].split(":")[1]
#print "Subscription ID: " + subscription_id
client_id = lines[1].split(":")[1]
#print "Client ID: " + client_id
secret_key = lines[2].split(":")[1]
#print "Secret Key: " + secret_key
tenant_id = lines[3].split(":")[1]
#print "Tenant ID: " + tenant_id
print json.dumps(lines, sort_keys=True, separators=(',', ': '))