#!/usr/bin/env python

import os, slackclient
VALET_SLACK_TOKEN = 'xoxb-203005776368-aZ3vUgUQYKAcSQ1MbMPZZcBf' #os.environ.get('VALET_SLACK_NAME')
VALET_SLACK_NAME = 'maestro' #os.environ.get('VALET_SLACK_TOKEN')
VALET_SLACK_ID = 'U5Z05NUAU'
# initialize slack client
valet_slack_client = slackclient.SlackClient(VALET_SLACK_TOKEN)
# check if everything is alright
print(VALET_SLACK_NAME)
print(VALET_SLACK_TOKEN)
is_ok = valet_slack_client.api_call("users.list").get('ok')
print(is_ok)

if(is_ok):
    for user in valet_slack_client.api_call("users.list").get('members'):
        if user.get('name') == VALET_SLACK_NAME:
            print(user.get('id'))
