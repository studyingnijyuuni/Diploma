import React, { useEffect, useState, useContext } from 'react';
import SideBar from './SideBar';
import { AuthContext  } from './AuthProvider';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';

const Settings = () => {
    const { setIsLoggedIn } = useContext(AuthContext);
    const { userID } = useContext(AuthContext);
    const { setUserID } = useContext(AuthContext);

    const [ Username, setUsername] = useState('');

    const [ emailAddress, setEmail] = useState('');
    const [ targetEmail, setTargetEmail ] = useState('');

    const [ success, setSuccess] = useState(null);
    const [ error, setError ] = useState(null);
    const [ isChanged, setIsChanged] = useState(false);
    const [ isChangingMail, setIsChangingMail] = useState(false);

    const [ telegramID, setTelegramID] = useState(null);
    const [ telegramConfirmation, setTelegramConfirmation] = useState(null);

    const navigate = useNavigate();

    const handleLogout = () => {
        axios.get('http://localhost/bb-mangadb/backend/logout.php', { withCredentials: true })
        .then( () => {
            setIsLoggedIn(false);
            setUserID(0);
            navigate('/Library');
        });
    };

    useEffect(() => {
        if(userID!=0 && !isChangingMail) setIsChanged(true);
    }, [userID, isChangingMail]);

    useEffect(() => {
        if(isChanged) getUserInfo(userID);
    }, [isChanged])

    const getUserInfo = (neededUserID) =>{
        setIsChanged(false);
        axios.post(`http://localhost/bb-mangadb/backend/getUserInfoByID.php`, { "UserID": neededUserID }, { withCredentials: true })
            .then(response => {
                if (response.data.error) {
                    setError(response.data.error);
                }
                else {
                    setEmail(response.data.Email);
                    setUsername(response.data.Username);
                    if(response.data.TelegramID) setTelegramID(response.data.TelegramID);
                    if(response.data.TelegramConfirmationCode) setTelegramConfirmation(response.data.TelegramConfirmationCode);
                }
            })
            .catch(error => setError('Uncaught error: ', error));
    }

    const setUserEmail = (emailAddress) =>{
        setIsChanged(false);
        setError(null);
        setSuccess(null);
        axios.post(`http://localhost/bb-mangadb/backend/setUserEmail.php`, { "emailAddress": emailAddress }, { withCredentials: true })
            .then(response => {
                if (response.data.error) {
                    setError(response.data.error);
                }
                else {
                    setSuccess(response.data.message);
                    setEmail(emailAddress);
                }
            })
            .catch(error => {setError('Uncaught error: ', error);});
    }
    
    const linkTelegram = () =>{
        setError(null);
        setSuccess(null);
        axios.get(`http://localhost/bb-mangadb/backend/addUserTelegramToLink.php`, {withCredentials: true})
            .then(response =>{
                if (response.data.error) {
                    setError(response.data.error);
                }
                else {
                    setSuccess(response.data.message);
                    setTelegramConfirmation(response.data.code);
                }
            })
    }

    const unlinkTelegram = () =>{
        setError(null);
        setSuccess(null);
        axios.get(`http://localhost/bb-mangadb/backend/unLinkTelegram.php`, {withCredentials: true})
        .then(response =>{
            if (response.data.error) {
                setError(response.data.error);
            }
            else {
                setSuccess(response.data.message);
                setTelegramID(null);
            }
        })
    }
    return(
        <div className="main-container">
            <SideBar Chosen={"Settings"}/>
            <div className="content">
                <h2>Settings 
                    {error && <span style={{ color: 'red' }}> {error}</span>}
                    {success && <span style={{ color: 'green' }}> {success}</span>}
                </h2>
                <p>User ID: {userID}</p>
                <p>Username: {Username}</p>
                <p>User Email address: {emailAddress} <button onClick={() => setIsChangingMail(true)}>Change Email</button></p>
                {isChangingMail?
                    <p>Write your new Email: <input type="text" placeholder="email" onChange= {e => setTargetEmail(e.target.value)} />
                    <button onClick={() => {setUserEmail(targetEmail);setIsChangingMail(false)}}>Confirm</button></p>
                :
                    <></>}
                <p>TelegramID: {telegramID?
                    <>{telegramID} <button onClick={() => unlinkTelegram()}>Remove</button></>
                    :
                    <>not set. {!telegramConfirmation?
                        <button onClick={() => linkTelegram()}>Add</button>
                        :
                        <></>}
                    </>}
                </p>
                <p>{telegramConfirmation?
                    'Send this to t.me/BB_mangadb_bot: /link '+ telegramConfirmation
                    :
                    <></>}
                </p>
                <button onClick={handleLogout}>Logout</button>
            </div>
        </div>
    );
}

export default Settings;