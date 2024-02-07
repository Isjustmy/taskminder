// auth.js
import Cookies from 'js-cookie';

export const isAuthenticated = () => {
  const authData = Cookies.get('authData');
  return !!authData; // Returns true if the authData exists, indicating the user is authenticated
};
