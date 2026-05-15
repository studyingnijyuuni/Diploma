import mysql.connector
import requests
import argparse
import time
import re
import sys
from datetime import datetime
from deep_translator import GoogleTranslator
from bs4 import BeautifulSoup
import base64

from translation_utils import transliterate_en, transliterate_uk, translate_tags

#translator = GoogleTranslator()

locales = ['ja','en','uk']

BASE_URL = "http://localhost:8000/api"
API_TOKEN = "#"

auth_headers = {
    "Authorization": f"Bearer {API_TOKEN}",
    "Accept": "application/json"
}

scrape_headers = {'User-Agent': 'BB_Manga'}

def main():
    sys.stdout.reconfigure(encoding='utf-8')

    parser = argparse.ArgumentParser(description="Manga Scraper")
    parser.add_argument("scrapeTarget", choices=["all","manga", "favorites", "journals", "initJournals", "initMangas"],
                        help="'all' for updating the whole list,\n'manga' for the amount of mangas,\n'favorites' for titles which have subscribed users")
    parser.add_argument("--amount", default=5, type=int, help="Amount of latest mangas to update from every Journal (e.g type 5 for - 5 from #1, 5 from #2 etc..")
    args = parser.parse_args()
    
    if args.scrapeTarget == "initJournals":
        print("This will overwrite existing journals, do you want to proceed? Y/any")
        answer = input()
        if(answer=="Y"):
            initializeJournals()                #Makes POST on Journals
    elif args.scrapeTarget == "journals":
        print("This will overwrite existing mangas, do you want to proceed? Y/any")
        answer = input()
        if(answer=="Y"):
            scrapeJournals()                    #Makes POST on Mangas (scrapes Mangas from Journal pages)
    elif args.scrapeTarget == "manga":
        scrapeMangasInit(amount=args.amount)    #Makes PUT (update) on Mangas
    else:
        print("Nothing here!")
            
def initializeJournals():
    startingPage = "https://comic-walker.com/label"
    request_resp = requests.get(startingPage, headers=scrape_headers)

    soup = BeautifulSoup(request_resp.content, 'html.parser')

    journals_soup = soup.find('ul', class_='Tiles_root__pRxBE')
    journals_li = journals_soup.find_all('li')                  #All journals

    for li in journals_li:
        li_name = li.find(class_='LabelLogoThumbnail_labelName__Q7_Qm').get_text() #journal Name
        li_src = f"https://comic-walker.com{li.div.a.get('href')}"                 #journal SourceLink        
        print(f"Working on {li_name}...")                   

        time.sleep(4)
        inside_request = requests.get(li_src, headers=scrape_headers)
        inside_soup = BeautifulSoup(inside_request.content, 'html.parser')

        inside_img_src = inside_soup.find(class_='ImageWithAspectRatio_imageWithAspectRatio__kfeqX').img.get('src') #journal image src
        description = inside_soup.find(class_="LabelDetailHeader_labelDescription__HNu2h").get_text() #journal description
        print(description)   

        title_en = ""
        title_uk = ""
        desc_en = ""
        desc_uk = "" 
        try:
            desc_en = GoogleTranslator(source="ja", target="en").translate(text=description) #locale 2
            desc_uk = GoogleTranslator(source="ja", target="uk").translate(text=description) #locale 3
            print(desc_en)
            print(desc_uk)
            time.sleep(2)
            title_en = GoogleTranslator(source="ja", target="en").translate(text=li_name) #locale 2
            title_uk = GoogleTranslator(source="ja", target="uk").translate(text=li_name) #locale 3
            print(title_en)
            print(title_uk)
        except Exception as e:
            print(f'Uncaught translation exception: {e}')

        time.sleep(2)
        img_response = requests.get(inside_img_src).content     #journal image
        base64_encoded_bytes = base64.b64encode(img_response)
        image_base64_string = base64_encoded_bytes.decode('utf-8')

        print("Setting up payload and sending...")
        payload = {
            "Name": li_name,
            "Description": description,
            "SourceLink": li_src,
            "Image": image_base64_string, # The raw base64 string
            "translations": [
                {"LocaleID": 2, "Title": f"{title_en}", "Description": f"{desc_en}"},  # English
                {"LocaleID": 3, "Title": f"{title_uk}", "Description": f"{desc_uk}"}   # Ukrainian
            ]
        }
        response = requests.post(f"{BASE_URL}/journals", json=payload, headers=auth_headers)
        print(response.content)


def scrapeJournals():
    journals = requests.get(f"{BASE_URL}/scraper/journals", headers=auth_headers)
    for journal in journals.json():
        journalID=journal['JournalID']
        journalSourceLink=journal['SourceLink']

        print(f"{journalID} - {journalSourceLink}")
        
        time.sleep(4)
        journal_response = requests.get(journalSourceLink, headers=auth_headers) #gets the first page of the journal
        page_soup = BeautifulSoup(journal_response.content, 'html.parser')
        scrapeJournalPage(page_soup, journalID)
        
        paginator = page_soup.find(class_="Pagination_paginationList__BzNcc")   # finds the amount of pages
        pages_dirty = paginator.find_all("li")[-2]                              #
        for span in pages_dirty.find_all("span"):                               #
            span.decompose()                                                    #
        pages_num=int(pages_dirty.get_text())                                   #

        for i in range(2, pages_num+1):                                         # crawler (kinda)
            time.sleep(2)
            journal_response = requests.get(f"{journalSourceLink}?p={i}", headers=auth_headers) #gets the {i} page of the journal
            page_soup = BeautifulSoup(journal_response.content, 'html.parser')
            scrapeJournalPage(page_soup, journalID)


def scrapeJournalPage(page_soup, journalID): #scrapes mangas from the journal's page
    page_mangalist = page_soup.find(class_="Tiles_root__pRxBE")
    manga_li = page_mangalist.find_all("li")

    for manga in manga_li:
        manga_name = manga.find(class_="WorkThumbnail_title__EmZ6E").get_text()
        manga_img = manga.find(class_="WorkThumbnail_image__p0wC_").img.get("src")
        manga_src = f"https://comic-walker.com{manga.find(class_="WorkThumbnail_link__LWlLk").get("href")}" # ?episodeType=latest needed to get the correct dates
        
        time.sleep(2)
        img_response = requests.get(manga_img).content     
        base64_encoded_bytes = base64.b64encode(img_response)
        image_base64_string = base64_encoded_bytes.decode('utf-8')

        title_en = transliterate_en(manga_name)
        title_uk = transliterate_uk(manga_name)

        print("Setting up payload and sending...")
        payload = {
            "Title": manga_name,
            "JournalID": journalID,
            "SourceLink": manga_src,
            "Image": image_base64_string, # The raw base64 string
            "translations": [
                {"LocaleID": 2, "Title": f"{title_en}"},  # English
                {"LocaleID": 3, "Title": f"{title_uk}"}   # Ukrainian
            ]
        }
        response = requests.post(f"{BASE_URL}/mangas", json=payload, headers=auth_headers)
        print(response.content)

def scrapeMangasInit(amount):
    journals = requests.get(f"{BASE_URL}/scraper/journals", headers=auth_headers)
    for journal in journal.json():
        journalID=journal['JournalID']
        scrapeMangas(amount=amount, journalID=journalID)

def scrapeMangas(amount, journalID): #scrapes manga info from the amount of mangas by their pages
    mangas = requests.get(f"{BASE_URL}/scraper/journals/{journalID}/mangas/{amount}", headers=auth_headers)
    for manga in mangas.json():
        mangaID = manga['MangaID']
        mangaSourceLink = manga['SourceLink']
        
        time.sleep(4)
        response = requests.get(f"{mangaSourceLink}?episodeType=latest",headers=scrape_headers)

        manga_soup = BeautifulSoup(response.content, 'html.parser')

        episodes_ul=manga_soup.find(class_="EpisodesTabContents_episodeList__cIDQz")
        last_episode=episodes_ul.find("li") # gets the first li, make sure ?episodeType=latest is set during the request

        last_release_date = last_episode.find("time")["datetime"]
        last_release_name = last_episode.find(class_="EpisodeThumbnail_title__G1eWj").get_text().strip()

        upcoming_release_date = ""
        upcoming_text=manga_soup.find(class_="EpisodesTabContents_nextUpdateDate__YDQiC").get_text()  #UpcomingReleaseDate tag
        date_split_1 = upcoming_text.split("：")[1]
        if "/" in date_split_1:
            datetime_date = datetime.strptime(date_split_1[1], "%Y/%m/%d").date()
            upcoming_release_date = datetime_date.strftime("%Y-%m-%d")
        
        tags=manga_soup.find_all(class_="TinyTagButton_sm__g1ktp")
        tags_arr = []
        for tag in tags:
            tags_arr.append(tag.find(class_="TinyTagButton_text__Tq4q1").get_text())

        tags_en = translate_tags(tags_arr)
    
        print("Setting up payload and sending...")
        payload = {
            "LastReleaseDate": last_release_date,
            "UpcomingReleaseDate": upcoming_release_date,
            "LastChapterName": last_release_name,
            "ReleasesInfo": "",
            "tags": tags_en
        }
        response = requests.put(f"{BASE_URL}/mangas/{mangaID}", json=payload, headers=auth_headers)
        print(response.content)

if __name__ == "__main__":
    #try:
    main()
    #except Exception as e:
        #print(f'Uncaught exception: {e}')