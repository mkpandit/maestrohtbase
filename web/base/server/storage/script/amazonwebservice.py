#!/usr/bin/env python

import nmap
import sys, json
s3 = boto3.resource('s3')

amazon_access_key_id = 'AKIAIGL7WOW3HXNR5W6Q'
amazon_secret_access_key = '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'

''' AWS Account ID: 8385-3963-2958
Canonical User ID: 838bf828de78e7e3bdf13008287247f65bfdcba2374cc5fe85f939992e7dbe24 '''