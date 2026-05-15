import React, { useState, useContext } from 'react';
import SideBar from "../SideBar";
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { AuthContext } from '../AuthProvider';

const Login = () =>{
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const { setIsLoggedIn } = useContext(AuthContext);
    const { setUserID } = useContext(AuthContext);
    const navigate = useNavigate();

    const handleLogin = () => {
      axios
      .post(`http://localhost/bb-mangadb/backend/login.php`, { "username": username, "password": password }, { withCredentials: true }) 
      .then(response => {
        if(response.data.error)
            setError(response.data.error);
        else
        {
            setSuccess(response.data.message);
            setUserID(response.data.UserID);
            setIsLoggedIn(true);
            navigate('/Check');
        }
      })
      .catch(err => {
        setError("Connection error: "+ err);
      });
    };
    
    return(
        <div className="main-container">
            <SideBar Chosen={"Login"}/>
            <div className="content">
                <h2>Log In</h2>
                <input type="text" placeholder="Username" onChange={e => setUsername(e.target.value)} />
                <input type="password" placeholder="Password" onChange={e => setPassword(e.target.value)} />
                <button onClick={handleLogin}>Login</button>
                {error && <p style={{ color: 'red' }}>{error}</p>}
                {success && <p style={{ color: 'green' }}>{success}</p>}
            </div>
        </div>
    )
}

export default Login;