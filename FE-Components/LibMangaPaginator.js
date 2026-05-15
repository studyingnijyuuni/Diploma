import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from './AuthProvider';
import axios from 'axios';

const LibMangaPaginator = ({JournalID}) => {
  const [mangas, setMangas] = useState([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalMangas, setTotalMangas] = useState(0);
  const [itemsPerPage, setItemsPerPage] = useState(5);
  const [success, setSuccess] = useState(null);
  const [isChanged, setIsChanged] = useState(true);
  const [error, setError] = useState(null);
  const { isLoggedIn } = useContext(AuthContext);

  useEffect(() => {
    if(isChanged) fetchMangas(currentPage);
  }, [currentPage, isChanged]);

  const fetchMangas = (page) => {
    axios
      .get(`http://localhost/bb-mangadb/backend/getMangasPagination.php?journalID=${JournalID}&page=${page}`,  { withCredentials: true })
      .then(response => {
        if (response.data.error) {
          setError(response.data.error);
          setMangas([]);
        } else {
          setMangas(response.data.mangas);
          setTotalMangas(response.data.totalMangas);
          setItemsPerPage(response.data.itemsPerPage);
          setError(null);
        }
      })
      .catch(err => {
        setError('Error fetching data: ' + err.message);
        setMangas([]);
    });
  }
  const followManga = (MangaID) =>{
    setIsChanged(false);
    setError(null);
    axios.post(`http://localhost/bb-mangadb/backend/addSavedForUserID.php`, {"MangaID": MangaID},  { withCredentials: true })
    .then(response => {
      if (response.data.error)
        setError(response.data.error);
      else {setSuccess(response.data.message); setIsChanged(true);}});
  }
  const unfollowManga = (MangaID) =>{
    setIsChanged(false);
    setError(null);
    axios.post(`http://localhost/bb-mangadb/backend/remSavedForUserID.php`, {"MangaID": MangaID},  { withCredentials: true })
    .then(response => {
      if (response.data.error)
        setError(response.data.error);
      else {setSuccess(response.data.message); setIsChanged(true);}});
  }
  const totalPages = Math.ceil(totalMangas / itemsPerPage);

  const handleFirst = () => setCurrentPage(1);
  const handlePrevious = () => setCurrentPage((prev) => Math.max(prev - 1, 1));
  const handleNext = () => setCurrentPage((prev) => Math.min(prev + 1, totalPages));
  const handleLast = () => setCurrentPage(totalPages);
  
  return (
    <div>
      <h3>Manga:</h3>
      {error && <p style={{ color: 'red' }}>{error}</p>}
      {success && <p style={{ color: 'green' }}>{success}</p>}
      <ul className="manga-list">
      {mangas.map((manga) => (
        <li key={manga.MangaID} className="manga-item">
          <a href={manga.SourceLink}><img src={manga.Image} alt={manga.Title} className="manga-image" /></a>
          <div className="manga-info">
            <a href={manga.SourceLink}><h4 className="manga-title">{manga.Title}</h4></a>
            <p className="manga-description">
            </p>
            <p className="last-updated">
              Date of the last Release: {manga.LastReleaseDate}
            </p>
          </div>
          {isLoggedIn?
            <button
              className={`follow-btn ${manga.isFollowed? 'unfollow':''}`}
              onClick={()=> manga.isFollowed? unfollowManga(manga.MangaID):followManga(manga.MangaID)}>
                {manga.isFollowed? 'Unfollow':'Follow'}
            </button>
          :
            <></>}
        </li>
      ))}
      </ul>

      <div>
        <button onClick={handleFirst} disabled={currentPage === 1}>First</button>
        <button onClick={handlePrevious} disabled={currentPage === 1}>Previous</button>
        <span> Page {currentPage} of {totalPages} </span>
        <button onClick={handleNext} disabled={currentPage === totalPages}>Next</button>
        <button onClick={handleLast} disabled={currentPage === totalPages}>Last</button>
      </div>
    </div>
  );
}

export default LibMangaPaginator;