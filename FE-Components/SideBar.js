import React, { useEffect, useContext } from 'react';
import { Link } from 'react-router-dom';
import BB_Logo from './bb_logo2.jpg'
import axios from "axios";
import { AuthContext  } from './AuthProvider';

const SideBar = ({Chosen}) =>{
  const { isLoggedIn } = useContext(AuthContext);

  return (
      <aside className="sidebar">
        <ul>
          <img src={BB_Logo} className="BB_Logo"/>
          <Link to="/Library"><li className = {Chosen === "Library" ? "active" : ""}>Library</li></Link>
          {isLoggedIn ? (
            <>
              <Link to="/Saved"><li className = {Chosen === "Saved" ? "active" : ""}>Saved</li></Link>
              <Link to="/Settings"><li className = {Chosen === "Settings" ? "active" : ""}>Settings</li></Link>
              <Link to="/Check"><li className = {Chosen === "Check" ? "active" : ""}>Check</li></Link>
            </>
          ) : (
            <>
              <Link to="/Registration"><li className = {Chosen === "Registration" ? "active" : ""}>Registration</li></Link>
              <Link to="/Login"><li className = {Chosen === "Login" ? "active" : ""}>Login</li></Link>
            </>  
            ) }
        </ul>
      </aside>
  );
}

export default SideBar;