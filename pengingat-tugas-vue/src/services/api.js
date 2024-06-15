// import axios
import axios from 'axios'

// import js cookie
import Cookies from 'js-cookie'

const api = axios.create({
  // set endpoint API
  baseURL: 'https://backendtaskminder.pplgsmkn1ciomas.my.id',

  // set header axios
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json'
  }
})

api.interceptors.request.use(
  (config) => {
    // Tambahkan Authorization Bearer (token JWT) jika tersedia di SessionStorage
    const token = sessionStorage.getItem('tokenJWT')
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

api.interceptors.response.use(
  function (response) {
    // return response
    return response
  },
  function (error) {
    // check if response unauthenticated
    if (401 === error.response.status) {
      // remove token
      Cookies.remove('userData')
      sessionStorage.removeItem('isLoggedIn')
      sessionStorage.removeItem('tokenJWT')
    } else if (403 === error.response.status) {
      // redirect "/forbidden"
      // store.dispatch('auth/forbidden'); // gantilah 'auth/forbidden' dengan action untuk halaman forbidden di store Anda
      // router.push('/forbidden');
      Cookies.remove('userData')
      sessionStorage.removeItem('isLoggedIn')
      sessionStorage.removeItem('tokenJWT')
    } else {
      // reject promise error
      return Promise.reject(error)
    }
  }
)

export default api
