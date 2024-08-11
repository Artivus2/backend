import requests
import json

api_url = 'https://api.nowpayments.io/v1/auth'
email = 'test.greenavi@mail.ru'  # Replace with your NowPayments email
password = 'M354at790!'  # Replace with your NowPayments password

def get_jwt_token(email, password):
    headers = {
        'Content-Type': 'application/json'
    }
    payload = {
        'email': email,
        'password': password
    }
    response = requests.post(api_url, headers=headers, data=json.dumps(payload))
    return response.json()

if __name__ == "__main__":
    result = get_jwt_token(email, password)
    print(json.dumps(result, indent=4))
