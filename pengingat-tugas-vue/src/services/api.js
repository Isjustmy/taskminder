// import axios
import axios from 'axios'

// import js cookie
import Cookies from 'js-cookie'

const api = axios.create({
  // set endpoint API
  baseURL: 'http://127.0.0.1:8000',

  // set header axios
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json'
  }
})

api.interceptors.request.use(
  (config) => {
    // Tambahkan Authorization Bearer (token JWT) jika tersedia di Cookies
    const authData = Cookies.get('authData');
    if (authData) {
      const authObject = JSON.parse(authData);
      if (authObject && authObject.token) {
        config.headers['Authorization'] = `Bearer ${authObject.token}`;
      }
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);


api.interceptors.response.use(
  function (response) {
    // return response
    return response
  },
  function (error) {
    // check if response unauthenticated
    if (401 === error.response.status) {
      // remove token
      Cookies.remove('authData')

    } else if (403 === error.response.status) {
      // redirect "/forbidden"
      // store.dispatch('auth/forbidden'); // gantilah 'auth/forbidden' dengan action untuk halaman forbidden di store Anda
      // router.push('/forbidden');
    } else {
      // reject promise error
      return Promise.reject(error)
    }
  }
)

export default api
