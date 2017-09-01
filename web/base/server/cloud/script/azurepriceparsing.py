#!/usr/bin/env python

import os, sys, json
import requests
from pprint import pprint

def azureCloudPrice():
    azurePriceJSONUrl = "https://azure.microsoft.com/api/v1/pricing/virtual-machines/calculator/?culture=en-us"
    header = {'x-requested-with': 'XMLHttpRequest'}
    priceResponse = requests.get(azurePriceJSONUrl, headers = header)
    azurePriceData = priceResponse.json()
    
    count = 0
    dictVM = {}
    for items in azurePriceData['offers']:
        count = count + 1
        operating_system = items.split('-')[0]
        price_value = 0
        for abc in azurePriceData['offers'][items]['prices']:
            if price_value < azurePriceData['offers'][items]['prices'][abc]:
                price_value = azurePriceData['offers'][items]['prices'][abc]
            else:
                price_value = price_value
        dictVM[items] = {'price': price_value, 'os': operating_system, 'cores': azurePriceData['offers'][items]['cores'], 'ram':azurePriceData['offers'][items]['ram']}
    
    return dictVM

def azureCorePrice():
    azurePrice = azureCloudPrice()
    az_core_1_price = 100
    az_core_2_price = 100
    az_core_3_price = 100
    az_core_4_price = 100
    az_core_5_price = 100
    az_core_8_price = 100
    az_core_12_price = 100
    az_core_16_price = 100
    az_core_24_price = 100
    az_core_32_price = 100
    
    for items in azurePrice:
        if azurePrice[items]['cores'] == 1:
            if(azurePrice[items]['price'] < az_core_1_price):
                az_core_1_price = azurePrice[items]['price']
                
        if azurePrice[items]['cores'] == 2:
            if(azurePrice[items]['price'] < az_core_2_price):
                az_core_2_price = azurePrice[items]['price']
        
        if azurePrice[items]['cores'] == 3:
            if(azurePrice[items]['price'] < az_core_3_price):
                az_core_3_price = azurePrice[items]['price']
        
        if azurePrice[items]['cores'] == 4:
            if(azurePrice[items]['price'] < az_core_4_price):
                az_core_4_price = azurePrice[items]['price']

        if azurePrice[items]['cores'] == 5:
            if(azurePrice[items]['price'] < az_core_5_price):
                az_core_5_price = azurePrice[items]['price']

        if azurePrice[items]['cores'] == 8:
            if(azurePrice[items]['price'] < az_core_8_price):
                az_core_8_price = azurePrice[items]['price']
        
        if azurePrice[items]['cores'] == 12:
            if(azurePrice[items]['price'] < az_core_12_price):
                az_core_12_price = azurePrice[items]['price']
        
        if azurePrice[items]['cores'] == 16:
            if(azurePrice[items]['price'] < az_core_16_price):
                az_core_16_price = azurePrice[items]['price']
        
        if azurePrice[items]['cores'] == 24:
            if(azurePrice[items]['price'] < az_core_24_price):
                az_core_24_price = azurePrice[items]['price']
        
        if azurePrice[items]['cores'] == 32:
            if(azurePrice[items]['price'] < az_core_32_price):
                az_core_32_price = azurePrice[items]['price']
    
    az_Core_Price = []
    az_Core_Price.append(az_core_1_price)
    az_Core_Price.append(az_core_2_price)
    az_Core_Price.append(az_core_3_price)
    az_Core_Price.append(az_core_4_price)
    az_Core_Price.append(az_core_5_price)
    az_Core_Price.append(az_core_8_price)
    az_Core_Price.append(az_core_12_price)
    az_Core_Price.append(az_core_16_price)
    az_Core_Price.append(az_core_24_price)
    az_Core_Price.append(az_core_32_price)
    
    return az_Core_Price

if __name__ == "__main__":
    #azurePrice = azureCloudPrice()
    #print azurePrice
    azureCorePrice = azureCorePrice()
    print azureCorePrice