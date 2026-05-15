import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from './AuthProvider';
import axios from 'axios';

const GetJournal = ({JournalID}) =>{
    const [journalData, setJournalData] = useState([]);
    const { isLoggedIn } = useContext(AuthContext);

    useEffect(() => {
        axios.get(`http://localhost/bb-mangadb/backend/getJournal.php?journalID=${JournalID}`)
            .then(response => {
                if (response.data.error) {
                    console.log(response.data.error);
                }
                else {
                    setJournalData(response.data);
                }
            })
            .catch(error => console.error('Error fetching data:', error));
    }, []);

    return(
        <div className="journal-info">
            <div className="journal-left">
                <a href={journalData.SourceLink}>
                    <img src={journalData.Image} alt={journalData.Name} className="journal-image" />
                </a>
            </div>
            <div className="journal-details">
                <a href={journalData.SourceLink}><h3>{journalData.Name}</h3></a>
                <p className="last-updated">Last Update: {journalData.LastUpdated}</p>
                <p>{journalData.Description}</p>
            </div>
        </div>
    );
}

export default GetJournal;