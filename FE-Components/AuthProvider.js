import React, { createContext, useState, useEffect } from 'react';
import axios from 'axios';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [userID, setUserID] = useState(0);
  useEffect(() => {
    const checkSession = () => {
        axios.get(`http://localhost/bb-mangadb/backend/checkSession.php`, { withCredentials: true })
        .then(response => {setIsLoggedIn(response.data.loggedIn); setUserID(response.data.UserID)})
        .catch(error => {setIsLoggedIn(false);});
    };

    checkSession();
  }, []);

  return (
    <AuthContext.Provider value={{ isLoggedIn, setIsLoggedIn, userID, setUserID }}>
      {children}
    </AuthContext.Provider>
  );
};