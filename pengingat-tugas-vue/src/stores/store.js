const store = {
  setUser(user) {
    sessionStorage.setItem('user', JSON.stringify(user));
  },
  setToken(token) {
    sessionStorage.setItem('token', token);
  },
  getUser() {
    return JSON.parse(sessionStorage.getItem('user'));
  },
  getToken() {
    return sessionStorage.getItem('token');
  }
};

export default store;