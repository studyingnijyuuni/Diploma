import React, { useContext } from 'react';
import axios from 'axios';
import SideBar from '../SideBar';
import { AuthContext } from '../AuthProvider';

function SessionCheck() {
  const { isLoggedIn } = useContext(AuthContext);

  return (
    <div className="main-container">
        <SideBar Chosen={"Check"}/>
        <div className="content">
            <h2>Check</h2>
            <div>{isLoggedIn ? 'You are logged in' : 'Not logged in'}</div>
        </div>
    </div>
);
}

export default SessionCheck;