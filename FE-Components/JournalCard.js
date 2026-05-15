import React from 'react';
import { Link } from 'react-router-dom';

const JournalCard = ({ Image, Name, Description, SourceLink, JournalID }) => {
  return (
    <Link to={`/Library/${JournalID}`}>
        <div className="journal-item">
            <a href={SourceLink} onClick={(e) => e.stopPropagation()}><img src={Image} alt={Name} className="journal-image"/></a>
            <h3>{Name}</h3>
            <p>{Description}</p>
        </div>
    </Link>
  );
};

export default JournalCard;