import requests
import sys
import json

api_key = '2WMC682-ATF4WCE-NW0HZNC-5E7S427'

# base_url = 'https://api-sandbox.nowpayments.io/v1/'  # For testing
base_url = "https://api.nowpayments.io/v1/" #for prod

def create_payment(amount, currency, order_id, pay_currency):
    url = f"{base_url}payment"
    headers = {
        "x-api-key": api_key,
        "Content-Type": "application/json"
    }
    payload = {
        "price_amount": amount,
        "price_currency": currency,  # example: usd
        "order_id": order_id,
        "pay_currency": pay_currency  # example: btc
    }
    response = requests.post(url, headers=headers, data=json.dumps(payload))
    return response.json()

if __name__ == "__main__":
    if len(sys.argv) != 5:
        print("Usage: python create_payment.py <amount> <currency> <order_id> <pay_currency>")
        sys.exit(1)
    
    amount = sys.argv[1]
    currency = sys.argv[2]
    order_id = sys.argv[3]
    pay_currency = sys.argv[4]
    
    try:
        amount = float(amount)
    except ValueError:
        print("Amount must be a number.")
        sys.exit(1)
    
    result = create_payment(amount, currency, order_id, pay_currency)
    print(json.dumps(result, indent=4))

# Example usage:
# python create_payment.py 100 usd test_order_123 btc
