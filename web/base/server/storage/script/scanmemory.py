#!/usr/bin/env python

from psutil import virtual_memory
import sys, json
import subprocess

if __name__ == "__main__":
    memoryStatus = virtual_memory()
    memoryList = []
    
    bashCommand = "df -h |grep ramdisk|awk '{print $2}'"
    p = subprocess.Popen(bashCommand, stdout=subprocess.PIPE, shell=True)
    (output, err) = p.communicate()
    memtotal = float(memoryStatus.total / (1024 * 1024 * 1024))
    memfree = float( memoryStatus.free / (1024 * 1024 * 1024) )
    
    tmpfsmem = output
    if tmpfsmem:
        tmpfsmem = output.replace('G', '')
        tmpfsmem = float(tmpfsmem)
        memfree = memfree - tmpfsmem
    
    memoryList.append("memtotal_"+str(memtotal))
    memoryList.append("memfree_"+str(memfree))
    
    print json.dumps(memoryList, sort_keys=True, separators=(',', ': '))