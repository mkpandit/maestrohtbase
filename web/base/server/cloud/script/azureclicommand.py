#!/usr/bin/env python

import os, sys, json
import commands

if __name__ == "__main__":
    vmCreateMsg = []
    '''parameters_size = len(sys.argv)
    if parameters_size < 4:
        vmCreateMsg.append("Bad parameter combination")
        sys.exit(1)'''
    try:
        vm_name = sys.argv[1]
        rg_name = sys.argv[2]
        user_name = sys.argv[3]
        pass_word = sys.argv[4]
        #login_status, login_output = commands.getstatusoutput("az login -u mpandit@htbase.com -p Htb@se123")
        login_status, login_output = commands.getstatusoutput("az login --service-principal -u dee048ad-a200-41cf-9b4a-ce563aa06ecd -p Rv8LBsPbzPivFt5sw2fMW+77wIoD9hAlBfFS7AR4BYg= -t dfd034ad-c274-41ae-b40c-88199e6b7528")
        exec_status, exec_output = commands.getstatusoutput("az vm create --resource-group "+rg_name+" --name "+vm_name+" --image /subscriptions/865ee318-9b61-4c3b-a0ce-83b84c976705/resourceGroups/HTBase/providers/Microsoft.Compute/images/HTBaseVmImageNSG --nsg HTVM012NSG --admin-username "+user_name+" --authentication-type password --admin-password "+pass_word)
        logout_status, logout_output = commands.getstatusoutput("az logout")
        
    except Exception as e:
        vmCreateMsg.append( str(e) )
    if(vmCreateMsg):
        print json.dumps(vmCreateMsg, sort_keys=True, separators=(',', ': '))
    else:
        vmCreateMsg.append(vm_name + " has been created")
        print json.dumps(vmCreateMsg, sort_keys=True, separators=(',', ': '))
    sys.exit(0)