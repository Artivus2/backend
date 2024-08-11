import requests
import json
import sys
api_key = '2WMC682-ATF4WCE-NW0HZNC-5E7S427'
base_url = 'https://api.nowpayments.io/v1/'  # Live environment URL
# base_url = 'https://api-sandbox.nowpayments.io/v1/' #For testing

def create_payout(jwt_token, amount, currency, address):
    url = f"{base_url}payout"
    headers = {
        'Authorization': f'Bearer {jwt_token}',
        'x-api-key': api_key,
        'Content-Type': 'application/json'
    }
    payload = {
        'withdrawals': [
            {
                'address': address,
                'amount': amount,
                'currency': currency,
                'ipn_callback_url': 'https://greenavi.com/api/payment/notice-ipn'  # Your IPN callback URL
            }
        ]
    }
    response = requests.post(url, headers=headers, data=json.dumps(payload))
    return response.json()

if __name__ == "__main__":
    if len(sys.argv) != 5:
        print("Usage: python create_payout.py <jwt_token> <amount> <currency> <address>")
        sys.exit(1)
    
    jwt_token = sys.argv[1]
    amount = sys.argv[2]
    currency = sys.argv[3]
    address = sys.argv[4]
    
    try:
        amount = float(amount)
    except ValueError:
        print("Amount must be a number.")
        sys.exit(1)
    
    result = create_payout(jwt_token, amount, currency, address)
    print(json.dumps(result, indent=4))

# python create_payout.py <jwt_token> <amount> <currency> <address>
