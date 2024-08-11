import requests
import sys
import json

api_key = '2WMC682-ATF4WCE-NW0HZNC-5E7S427'

base_url = "https://api.nowpayments.io/v1/"

def get_payment_status(payment_id):
    url = f"{base_url}payment/{payment_id}"
    headers = {
        "x-api-key": api_key,
        "Content-Type": "application/json"
    }
    response = requests.get(url, headers=headers)
    
    if response.status_code == 200:
        return response.json()
    else:
        return {"error": f"Failed to get payment status. Status code: {response.status_code}, Response: {response.text}"}

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python get_payment_status.py <payment_id>")
        sys.exit(1)
    
    payment_id = sys.argv[1]
    result = get_payment_status(payment_id)
    print(json.dumps(result, indent=4))
