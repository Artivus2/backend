import sys
import requests

api_key = '2WMC682-ATF4WCE-NW0HZNC-5E7S427'
api_url = 'https://api.nowpayments.io/v1/payment'

headers = {
    'x-api-key': api_key,
    'Content-Type': 'application/json',
    'x-test-mode': 'true'  # Enable test mode
}

def check_payment_status(payment_id):
    response = requests.get(f"{api_url}/{payment_id}", headers=headers)
    return response.json()

if __name__ == "__main__":
    payment_id = sys.argv[1]
    result = check_payment_status(payment_id)
    print(result)
