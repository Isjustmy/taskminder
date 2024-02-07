// auth.js
import Cookies from 'js-cookie';
import api from '@/services/api.js';

export const isAuthenticated = () => {
  const authData = Cookies.get('authData');
  return !!authData; // Returns true if the authData exists, indicating the user is authenticated
};

export const checkAuthStatus = async () => {
  if (!isAuthenticated()) {
    throw new Error('User is not authenticated');
  } else {
    try {
      // Check if token is still valid by making a request to the API
      await api.get('/api/check-token-validity');
    } catch (error) {
      // Token is invalid
      throw new Error('Token expired or invalid');
    }
  }
};
