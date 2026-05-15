import React, { useState } from 'react';
import SideBar from '../SideBar';
import axios from 'axios';
import { Navigate, redirect, useNavigate } from 'react-router-dom';

function Register() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);
  const navigate = useNavigate();

  const handleRegister = () => {
    axios
      .post(`http://localhost/bb-mangadb/backend/register.php`, { "username": username, "password": password })
      .then(response => {
        if(response.data.error)
            setError(response.data.error);
        else
        {
          setSuccess(response.data.message);
          navigate('/Login');
        }
      })
      .catch(err => {
        setError("Connection error: "+ err);
      });
  };

  return (
    <div className="main-container">
      <SideBar Chosen={"Registration"}/>
      <div className="content">
        <h2>Register</h2>
        <input type="text" placeholder="Username" onChange={e => setUsername(e.target.value)} />
        <input type="password" placeholder="Password" onChange={e => setPassword(e.target.value)} />
        <button onClick={handleRegister}>Register</button>
        {error && <p style={{ color: 'red' }}>{error}</p>}
        {success && <p style={{ color: 'green' }}>{success}</p>}
      </div>
    </div>
  );
}

export default Register;