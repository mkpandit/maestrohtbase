#!/usr/bin/env python

#Python class to find the Hosts

import sys, json
import commands
import os
import re
from glob import glob


class stotageDisk:

	def __init__(self):
		self.author = "Manish Pandit, HTBase, mpandit@htbase.com"
	
	def ex_command(self, command):
		return commands.getoutput(command)

Disk = stotageDisk()
argProcessed = json.loads(sys.argv[1])
availableDisk = Disk.ex_command(argProcessed)
availableDisk = availableDisk.split("\n")
availableDisk.pop(0)

'''
diskInformation = []

for b in availableDisk:
    if b.startswith("Filesystem"):
        diskInformation.append(b)
'''
print json.dumps(availableDisk)