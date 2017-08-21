#!/usr/bin/env python

#import nmap
from nmap import PortScanner
import sys, json


def netHostList():
    #nm = nmap.PortScanner()
    nm = PortScanner()
    nm.scan(hosts='192.168.0.7/24', arguments='-n -sP -PE -PA21,23,80,3389')

    hostList = []
    hostArray = ()

    hosts_list = [(x, nm[x]['status']['state']) for x in nm.all_hosts()]
    for host, status in hosts_list:
        hostList.append( host + ' : ' + status )
    return hostList
    #hostArray.append(host)

hostList = netHostList()
#print hostList
print json.dumps(hostList, sort_keys=True, separators=(',', ': '))
