#!/usr/bin/env python

import base64
import hmac, hashlib

policy_document = {"expiration": "2020-01-01T00:00:00Z",
  "conditions": [ 
    {"bucket": "htbaseawsbucket"}, 
    ["starts-with", "$key", "uploads/"],
    {"acl": "private"},
    {"success_action_redirect": "http://192.168.0.190/htvcenter/base/index.php?base=storage&storage_action=addawsfile"},
    ["starts-with", "$Content-Type", ""],
    ["content-length-range", 0, 1048576]
  ]
}

AWS_SECRET_ACCESS_KEY = "AKIAIB5HVDDEH4CEL6EQ"

policy = base64.b64encode(policy_document)

signature = base64.b64encode(hmac.new(AWS_SECRET_ACCESS_KEY, policy, hashlib.sha1).digest())

print policy

print "\n"

print signature