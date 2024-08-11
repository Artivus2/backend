import requests
import json

api_key = '2WMC682-ATF4WCE-NW0HZNC-5E7S427'

api_url = 'https://api.nowpayments.io/v1/currencies'  # Use the live URL

headers = {
    'x-api-key': api_key,
    'Content-Type': 'application/json'
}

def list_currencies():
    response = requests.get(api_url, headers=headers)
    return response.json()

if __name__ == "__main__":
    result = list_currencies()
    print(json.dumps(result, indent=4))
