#!/bin/bash
#

NAME=$1
KVM_NAME="$NAME.kvm"
sleep 80
cd /var/lib/kvm/htvcenter/$NAME/
./$KVM_NAME