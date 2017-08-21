#!/bin/bash

action=$1
vmname=$2
isofile=$3

if [ "$action" == 'insert' ]; then
        echo "change ide1-cd0 /linuxcoe-iso/$isofile" | socat stdio unix:/var/run/htvcenter/kvm/kvm.$vmname.mon > /dev/null 2>&1
        exit 1;
fi

if [ "$action" == 'eject' ]; then
        echo "eject ide1-cd0" | socat stdio unix:/var/run/htvcenter/kvm/kvm.$vmname.mon > /dev/null 2>&1
        exit 1;
fi

