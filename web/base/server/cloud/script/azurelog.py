#!/usr/bin/env python

import os, sys, json
import commands

if __name__ == "__main__":
    azLog = []
    try:
        #login_status, login_output = commands.getstatusoutput("az login -u mpandit@htbase.com -p Htb@se123")
        login_status, login_output = commands.getstatusoutput("az login --service-principal -u dee048ad-a200-41cf-9b4a-ce563aa06ecd -p Rv8LBsPbzPivFt5sw2fMW+77wIoD9hAlBfFS7AR4BYg= -t dfd034ad-c274-41ae-b40c-88199e6b7528")
        exec_status, exec_output = commands.getstatusoutput("az monitor activity-log list --resource-group HTBase")
        logout_status, logout_output = commands.getstatusoutput("az logout")

        jsonResponse=json.loads(exec_output)
        
        '''for jsonData in jsonResponse:
            for item in jsonData:
                if isinstance(jsonData[item], dict):
                    print item
                    for k, v in jsonData[item].items():
                        print '\t', k, ' -> ', v
                else:
                    print str(item) + " => " + str(jsonData[item])'''
            
    except Exception as e:
        azLog.append( str(e) )

    if not azLog:
        print json.dumps(jsonResponse, sort_keys=True, separators=(',', ': '))
    sys.exit(0)