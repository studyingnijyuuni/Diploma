import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import './ChosenJournal.css';
import './LibMangaPaginator.css';

import GetJournal from './GetJournalByID';
import LibMangaPaginator from './LibMangaPaginator';
import SideBar from './SideBar'

const ChosenJournal = () => {
    const { journalID } = useParams();
      
    return (
      <div className="main-container">
        <SideBar Chosen={"Library"}/>
  
        <div className="content">
            <GetJournal JournalID={journalID}/>
            <LibMangaPaginator JournalID={journalID}/>
        </div>
    </div>
)}

export default ChosenJournal;