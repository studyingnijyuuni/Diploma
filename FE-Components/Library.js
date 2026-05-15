import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import './Library.css';

import JournalCard from './JournalCard';
import SideBar from './SideBar'

const Library = () => {
    const [data, setData] = useState([]);
  
    useEffect(() => {
      axios.get('http://localhost/bb-mangadb/backend/getLibrary.php')
        .then(response => setData(response.data))
        .catch(error => console.error('Error fetching data:', error));
    }, []);
  
    return (
      <div className="main-container">
        <SideBar Chosen={"Library"}/>
  
        <div className="content">
          <h2>Library</h2>
          <p>
            Please, select a magazine to browse further.
          </p>
          <div className="journal-list">
            {data.map((data, index) => (
              <JournalCard key={index} JournalID={data.JournalID} Image={data.Image} Name={data.Name} Description={data.Description} SourceLink={data.SourceLink}/>
            ))}
          </div>
        </div>
      </div>
    );
  }
  
  export default Library;
  