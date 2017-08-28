#!/usr/bin/env python

import nmap
import psutil
import sys, json


def netHostList():
    nm = nmap.PortScanner()
    nm.scan(hosts='192.168.0.190/24', arguments='-sP')

    hostList = []
    hostArray = ()

    hosts_list = [(x, nm[x]['status']['state']) for x in nm.all_hosts()]
    for host, status in hosts_list:
        hostList.append( host + ' : ' + status + ' : ' + nm[host].hostname())
        print host + ' : ' + nm[host].hostname()
    return hostList
    #hostArray.append(host)
    
    print hosts_list

hostList = netHostList()
print hostList