from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import requests
import json

app = FastAPI()

api_key = 'TVM51YA-KYQ4PYW-M030XMQ-CFSY7WD'
base_url = 'https://api.nowpayments.io/v1/'
# base_url = 'https://api-sandbox.nowpayments.io/v1/' #For testing

# Models
class CreatePaymentRequest(BaseModel):
    amount: float
    currency: str
    order_id: str
    pay_currency: str

class CreatePayoutRequest(BaseModel):
    jwt_token: str
    amount: float
    currency: str
    address: str

# List currencies
@app.get("/list_currencies")
def list_currencies():
    api_url = f'{base_url}currencies'
    headers = {
        'x-api-key': api_key,
        'Content-Type': 'application/json'
    }
    response = requests.get(api_url, headers=headers)
    if response.status_code != 200:
        raise HTTPException(status_code=response.status_code, detail=response.json())
    return response.json()

# Create payment
@app.post("/create_payment")
def create_payment(request: CreatePaymentRequest):
    url = f"{base_url}payment"
    headers = {
        "x-api-key": api_key,
        "Content-Type": "application/json"
    }
    payload = {
        "price_amount": request.amount,
        "price_currency": request.currency,
        "order_id": request.order_id,
        "pay_currency": request.pay_currency
    }
    response = requests.post(url, headers=headers, data=json.dumps(payload))
    if response.status_code != 200:
        raise HTTPException(status_code=response.status_code, detail=response.json())
    return response.json()

# Create payout
@app.post("/create_payout")
def create_payout(request: CreatePayoutRequest):
    url = f"{base_url}payout"
    headers = {
        'Authorization': f'Bearer {request.jwt_token}',
        'x-api-key': api_key,
        'Content-Type': 'application/json'
    }
    payload = {
        'withdrawals': [
            {
                'address': request.address,
                'amount': request.amount,
                'currency': request.currency,
                'ipn_callback_url': 'https://yourcallbackurl.com/ipn'  # Your IPN callback URL
            }
        ]
    }
    response = requests.post(url, headers=headers, data=json.dumps(payload))
    if response.status_code != 200:
        raise HTTPException(status_code=response.status_code, detail=response.json())
    return response.json()

# Get JWT token
@app.post("/get_jwt_token")
def get_jwt_token(email: str, password: str):
    api_url = f'{base_url}auth'
    headers = {
        'Content-Type': 'application/json'
    }
    payload = {
        'email': email,
        'password': password
    }
    response = requests.post(api_url, headers=headers, data=json.dumps(payload))
    if response.status_code != 200:
        raise HTTPException(status_code=response.status_code, detail=response.json())
    return response.json()

# Get payment status
@app.get("/get_payment_status/{payment_id}")
def get_payment_status(payment_id: str):
    url = f"{base_url}payment/{payment_id}"
    headers = {
        "x-api-key": api_key,
        "Content-Type": "application/json"
    }
    response = requests.get(url, headers=headers)
    if response.status_code != 200:
        raise HTTPException(status_code=response.status_code, detail=response.json())
    return response.json()
