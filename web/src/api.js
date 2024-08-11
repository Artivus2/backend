import axios from "axios";

const api = axios.create({
  baseURL: 'https://test.greenavi.com',
  headers: {
    'Access-Control-Allow-Origin': '*',
    'Content-Type': 'application/json',
},
withCredentials: true
 
   // замените на ваш базовый URL
 
});
api.prototype.$http = axios;
//api.prototype.$http = Axios;
const token = localStorage.getItem('access_token')

 if (token) {
   api.prototype.$http.defaults.headers.common['Authorization'] = token
   
 } else {
  console.log("нет сессии")
 }
 
export default api