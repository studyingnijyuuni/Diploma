import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from './AuthProvider';
import axios from 'axios';
import SideBar from './SideBar'

const Follows = () =>{
    const [mangas, setMangas] = useState([]);
    const { userID } = useContext(AuthContext);
    const [unfSuccess, setUnfSuccess] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        if(userID!=0) fetchSavedMangas();
    }, [userID]);

    const fetchSavedMangas = () =>{
        axios.get(`http://localhost/bb-mangadb/backend/getSaved.php?userID=${userID}`,  { withCredentials: true })
        .then(response => {
            if(response.data.error) {setError(response.data.error);}
            else{setMangas(response.data.mangas); setError(null); }
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    const unfollowManga = (MangaID) =>{
        setError(null);
        setUnfSuccess(null);
        axios.post(`http://localhost/bb-mangadb/backend/remSavedForUserID.php`, {"MangaID": MangaID},  { withCredentials: true })
        .then(response => {
          if (response.data.error)
            setError(response.data.error);
          else {setUnfSuccess(response.data.message); fetchSavedMangas();};
        })
    }

    const setNotifications = (MangaID, isNotify) =>
    {
        console.warn(`Changing MangaID(${MangaID})'s Notifications to (${isNotify})`);
        setError(null);
        setUnfSuccess(null);
        axios.post(`http://localhost/bb-mangadb/backend/setNotificationsForUserID.php`, {"MangaID": MangaID, "isNotify": isNotify},  { withCredentials: true })
        .then(response => {
          if (response.data.error)
            setError(response.data.error);
          else {setUnfSuccess(response.data.message); fetchSavedMangas();};
        })
    }

    return(
    <div className="main-container">
        <SideBar Chosen={"Saved"}/>

        <div className="content">
            <h2>Your Follows</h2>
            {error && <p style={{ color: 'red' }}>{error}</p>}
            <p>Timeline of currently followng sorted by Next Release: {unfSuccess && <span  style={{ color: 'green' }}>{unfSuccess}</span>}</p>
            <div className="manga-list-wrapper">
                <ul className="manga-list">
                {mangas.map((manga) => (
                    <li key={manga.MangaID} className="manga-item">
                    <a href={manga.SourceLink}><img src={manga.Image} alt={manga.Title} className="manga-image" /></a>
                    <div className="manga-info">
                        <a href={manga.SourceLink}><h4 className="manga-title">{manga.Title}</h4></a>
                        <p className="last-updated">
                            Date of the last Release: {manga.LastReleaseDate}
                        </p>
                        <p className="last-updated">
                            Date of the next Release: {manga.UpcomingReleaseDate}
                        </p>
                    </div>
                    <button
                    className={`follow-btn ${manga.IsEmailNotificationsOn ? 'unfollow': ''}`}
                    onClick={() => setNotifications(manga.MangaID, !manga.IsEmailNotificationsOn)}>
                        {manga.IsEmailNotificationsOn? 'Do not notify': 'Notify'}
                    </button>
                    <button
                        className={`follow-btn unfollow`}
                        onClick={()=> unfollowManga(manga.MangaID)}>
                        Unfollow
                    </button>
                    </li>
                ))}
                </ul>
            </div>
        </div>
    </div>
);
}

export default Follows;