#!/usr/bin/env python

import os, sys, json
import requests
from pprint import pprint
import azurepriceparsing

if __name__ == "__main__":
    awsPriceJSONUrl = "https://pricing.us-east-1.amazonaws.com/offers/v1.0/aws/AmazonEC2/current/us-east-1/index.json"
    header = {'x-requested-with': 'XMLHttpRequest'}
    priceResponse = requests.get(awsPriceJSONUrl, headers = header)
    awsPriceData = priceResponse.json()
    dictVM = { }
    count = 0
    for items in awsPriceData['terms']['OnDemand']:
        virtualMachine = []
        awsVM = {}
        count = count + 1

        if 'processorArchitecture' in awsPriceData['products'][items]['attributes'].keys():
            #print "Processor Architecture: " + awsPriceData['products'][items]['attributes']['processorArchitecture']
            virtualMachine.append(awsPriceData['products'][items]['attributes']['processorArchitecture'])
            awsVM['processorArchitecture'] = awsPriceData['products'][items]['attributes']['processorArchitecture']
        else:
            virtualMachine.append('NA')
            awsVM['processorArchitecture'] = 'NA'
        
        if 'memory' in awsPriceData['products'][items]['attributes'].keys():
            #print "Memory: " + awsPriceData['products'][items]['attributes']['memory']
            virtualMachine.append(awsPriceData['products'][items]['attributes']['memory'])
            awsVM['memory'] = awsPriceData['products'][items]['attributes']['memory']
        else:
            virtualMachine.append('NA')
            awsVM['memory'] = 'NA'
        
        if 'operatingSystem' in awsPriceData['products'][items]['attributes'].keys():
            #print "Operating System: " + awsPriceData['products'][items]['attributes']['operatingSystem']
            virtualMachine.append(awsPriceData['products'][items]['attributes']['operatingSystem'])
            awsVM['operatingSystem'] = awsPriceData['products'][items]['attributes']['operatingSystem']
        else:
            virtualMachine.append('NA')
            awsVM['operatingSystem'] = 'NA'
        
        if 'vcpu' in awsPriceData['products'][items]['attributes'].keys():
            #print "CPU: " + awsPriceData['products'][items]['attributes']['vcpu']
            virtualMachine.append(awsPriceData['products'][items]['attributes']['vcpu'])
            awsVM['vcpu'] = awsPriceData['products'][items]['attributes']['vcpu']
        else:
            virtualMachine.append('NA')
            awsVM['vcpu'] = 'NA'
            
        if 'clockSpeed' in awsPriceData['products'][items]['attributes'].keys():
            #print "Clock Speed: " + awsPriceData['products'][items]['attributes']['clockSpeed']
            virtualMachine.append(awsPriceData['products'][items]['attributes']['clockSpeed'])
            awsVM['clockSpeed'] = awsPriceData['products'][items]['attributes']['clockSpeed']
        else:
            virtualMachine.append('NA')
            awsVM['clockSpeed'] = 'NA'
            
        for item in awsPriceData['terms']['OnDemand'][items]:
            for abc in awsPriceData['terms']['OnDemand'][items][item]['priceDimensions']:
                #pprint(awsPriceData['terms']['OnDemand'][items][item]['priceDimensions'][abc])
                #print "\n"
                #print awsPriceData['terms']['OnDemand'][items][item]['priceDimensions'][abc]['description']
                virtualMachine.append(awsPriceData['terms']['OnDemand'][items][item]['priceDimensions'][abc]['description'])
                awsVM['description'] = awsPriceData['terms']['OnDemand'][items][item]['priceDimensions'][abc]['description']
                #print awsPriceData['terms']['OnDemand'][items][item]['priceDimensions'][abc]['rateCode']
                for xyz in awsPriceData['terms']['OnDemand'][items][item]['priceDimensions'][abc]['pricePerUnit']:
                    #print awsPriceData['terms']['OnDemand'][items][item]['priceDimensions'][abc]['pricePerUnit'][xyz]
                    virtualMachine.append(awsPriceData['terms']['OnDemand'][items][item]['priceDimensions'][abc]['pricePerUnit'][xyz])
                    awsVM['pricePerUnit'] = awsPriceData['terms']['OnDemand'][items][item]['priceDimensions'][abc]['pricePerUnit'][xyz]
        #dictVM[items] = virtualMachine
        dictVM[items] = awsVM
        virtualMachine = []
        awsVM = {}
        #print "\n========================================================================================================\n"
        #if count == 20:
            #break
    #print dictVM
    if len(sys.argv) > 1:
        vmPrice = []
        
        if sys.argv[1] == 'coreprice':
            aws_core_1_price = 100
            aws_core_2_price = 100
            aws_core_3_price = 100
            aws_core_4_price = 100
            aws_core_5_price = 100
            aws_core_8_price = 100
            aws_core_12_price = 100
            aws_core_16_price = 100
            aws_core_24_price = 100
            aws_core_32_price = 100
            
            for items in dictVM:
                if dictVM[items]['vcpu'] != "NA" and float(dictVM[items]['pricePerUnit']) != 0.00 and int(dictVM[items]['vcpu']) == 1:
                    if (float(dictVM[items]['pricePerUnit']) < aws_core_1_price):
                        aws_core_1_price = float(dictVM[items]['pricePerUnit'])
                        
                if dictVM[items]['vcpu'] != "NA" and float(dictVM[items]['pricePerUnit']) != 0.00 and int(dictVM[items]['vcpu']) == 2:
                    if (float(dictVM[items]['pricePerUnit']) < aws_core_2_price):
                        aws_core_2_price = float(dictVM[items]['pricePerUnit'])
                
                if dictVM[items]['vcpu'] != "NA" and float(dictVM[items]['pricePerUnit']) != 0.00 and int(dictVM[items]['vcpu']) == 3:
                    if (float(dictVM[items]['pricePerUnit']) < aws_core_3_price):
                        aws_core_3_price = float(dictVM[items]['pricePerUnit'])
                
                if dictVM[items]['vcpu'] != "NA" and float(dictVM[items]['pricePerUnit']) != 0.00 and int(dictVM[items]['vcpu']) == 4:
                    if (float(dictVM[items]['pricePerUnit']) < aws_core_4_price):
                        aws_core_4_price = float(dictVM[items]['pricePerUnit'])
                        
                if dictVM[items]['vcpu'] != "NA" and float(dictVM[items]['pricePerUnit']) != 0.00 and int(dictVM[items]['vcpu']) == 5:
                    if (float(dictVM[items]['pricePerUnit']) < aws_core_5_price):
                        aws_core_5_price = float(dictVM[items]['pricePerUnit'])
                
                if dictVM[items]['vcpu'] != "NA" and float(dictVM[items]['pricePerUnit']) != 0.00 and int(dictVM[items]['vcpu']) == 8:
                    if (float(dictVM[items]['pricePerUnit']) < aws_core_8_price):
                        aws_core_8_price = float(dictVM[items]['pricePerUnit'])
                
                if dictVM[items]['vcpu'] != "NA" and float(dictVM[items]['pricePerUnit']) != 0.00 and int(dictVM[items]['vcpu']) == 12:
                    if (float(dictVM[items]['pricePerUnit']) < aws_core_12_price):
                        aws_core_12_price = float(dictVM[items]['pricePerUnit'])
                
                if dictVM[items]['vcpu'] != "NA" and float(dictVM[items]['pricePerUnit']) != 0.00 and int(dictVM[items]['vcpu']) == 16:
                    if (float(dictVM[items]['pricePerUnit']) < aws_core_16_price):
                        aws_core_16_price = float(dictVM[items]['pricePerUnit'])
                
                if dictVM[items]['vcpu'] != "NA" and float(dictVM[items]['pricePerUnit']) != 0.00 and int(dictVM[items]['vcpu']) == 24:
                    if (float(dictVM[items]['pricePerUnit']) < aws_core_24_price):
                        aws_core_24_price = float(dictVM[items]['pricePerUnit'])
                        
                if dictVM[items]['vcpu'] != "NA" and float(dictVM[items]['pricePerUnit']) != 0.00 and int(dictVM[items]['vcpu']) == 32:
                    if (float(dictVM[items]['pricePerUnit']) < aws_core_32_price):
                        aws_core_32_price = float(dictVM[items]['pricePerUnit'])
            
            vmPrice.append(aws_core_1_price)
            vmPrice.append(aws_core_2_price)
            vmPrice.append(aws_core_3_price)
            vmPrice.append(aws_core_4_price)
            vmPrice.append(aws_core_5_price)
            vmPrice.append(aws_core_8_price)
            vmPrice.append(aws_core_12_price)
            vmPrice.append(aws_core_16_price)
            vmPrice.append(aws_core_24_price)
            vmPrice.append(aws_core_32_price)
            
            azureCorePrice = azurepriceparsing.azureCorePrice()
            
            vmPrice.append(azureCorePrice[0])
            vmPrice.append(azureCorePrice[1])
            vmPrice.append(azureCorePrice[2])
            vmPrice.append(azureCorePrice[3])
            vmPrice.append(azureCorePrice[4])
            vmPrice.append(azureCorePrice[5])
            vmPrice.append(azureCorePrice[6])
            vmPrice.append(azureCorePrice[7])
            vmPrice.append(azureCorePrice[8])
            vmPrice.append(azureCorePrice[9])
            
        else:
            memory                      = sys.argv[1]
            operating_system            = sys.argv[2]
            vcpu                        = sys.argv[3]

            for k in dictVM:
                if dictVM[k]['memory'] == memory and dictVM[k]['operatingSystem'] == operating_system and dictVM[k]['vcpu'] == vcpu:
                    vmPrice.append(dictVM[k]['pricePerUnit'])
                    vmPrice.append(dictVM[k]['description'])

                    # Processing Azure Data
                    azureCloudPrice = azurepriceparsing.azureCloudPrice()
                    azMemory = float(memory.replace(" GiB", ""))
                    azOpSys = operating_system.lower()
                    azVCPU = vcpu
                    for m in azureCloudPrice:
                        if str(azureCloudPrice[m]['ram']) == str(azMemory) and azureCloudPrice[m]['os'] == azOpSys and str(azureCloudPrice[m]['cores']) == str(azVCPU):
                            vmPrice.append('az_' + str(azureCloudPrice[m]['price']))
                            vmPrice.append('az_' + m)
        
        print json.dumps(vmPrice, sort_keys=True, separators=(',', ': '))
    else:
        print json.dumps(dictVM, sort_keys=True, separators=(',', ': '))