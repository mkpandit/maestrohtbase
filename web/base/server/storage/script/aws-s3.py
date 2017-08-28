#!/usr/bin/env python

import boto3
from inspect import getmembers
from pprint import pprint
import json

s3 = boto3.resource('s3', aws_access_key_id = "AKIAIB5HVDDEH4CEL6EQ", aws_secret_access_key = "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")

clientS3 = boto3.client('s3', aws_access_key_id = "AKIAIB5HVDDEH4CEL6EQ", aws_secret_access_key = "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",)

ec2 = boto3.client('ec2', aws_access_key_id = "AKIAIB5HVDDEH4CEL6EQ", aws_secret_access_key = "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")
ec2R = boto3.resource('ec2', aws_access_key_id = "AKIAIB5HVDDEH4CEL6EQ", aws_secret_access_key = "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")

for bucket in s3.buckets.all():
    print(bucket.name)
    print(bucket.creation_date)

if clientS3.upload_file("/home/htbase/pyparsing-2.2.0.zip", "HTBaseDemo", "pyparsing-2.2.0.zip"):
    print "File uploaded successfully"