import axios from 'axios';

const apimain = axios.create({
  baseURL: 'https://api.binance.com/api/v3/ticker/', // замените на ваш базовый URL
  headers: {
    'Access-Control-Allow-Origin': '*',
    'Content-Type': 'application/json', // добавьте ваши заголовки
  },
});

export default apimain