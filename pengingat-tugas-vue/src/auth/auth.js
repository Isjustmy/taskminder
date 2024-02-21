// auth.js
const setLoggedIn = () => {
  sessionStorage.setItem('isLoggedIn', 'true');
};

const setLoggedOut = () => {
  sessionStorage.removeItem('isLoggedIn');
};

const isLoggedIn = () => {
  const loggedIn = sessionStorage.getItem('isLoggedIn')
  if(loggedIn === 'true' || loggedIn === true){
    return true
  } else{
    return false
  }
};

export { setLoggedIn, setLoggedOut, isLoggedIn }