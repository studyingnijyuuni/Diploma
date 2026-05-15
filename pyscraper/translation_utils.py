import json
import os
import pykakasi
from transliterate import translit, get_available_language_codes
from deep_translator import GoogleTranslator

kks = pykakasi.kakasi()
CACHE_FILE = "tag_translation_cache.json"

def transliterate_en(input_jp):
    result = kks.convert(input_jp)
    res_en = ""
    for item in result:
        res_en.append(item['hepburn'])
    return " ".join(res_en)

def transliterate_uk(input_jp):
    result = kks.convert(input_jp)
    res_uk = ""
    for item in result:
        res_uk.append(translit(item['hepburn'], 'uk'))
    return " ".join(res_uk).title()

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, 'r', encoding='utf-8') as file:
            return json.load(file)
    return {}

def save_cache(cache_dict):
    with open(CACHE_FILE, 'w', encoding='utf-8') as file:
        json.dump(cache_dict, file, ensure_ascii=False, indent=4)

def get_translated_tag(japanese_tag, cache):
    clean_tag = japanese_tag.strip()
    
    if clean_tag in cache:
        return cache[clean_tag]
    
    print(f"New tag found! Translating: {clean_tag}...")
    
    # ---  API CALL  ---
    tag_en = GoogleTranslator(source="ja", target="en").translate(text=clean_tag)
    # -----------------------------------------------------------------

    print (f"Saved as {tag_en}")
    
    cache[clean_tag] = tag_en
    save_cache(cache)
    
    return tag_en

def translate_tags(tags):
    translation_cache = load_cache()
    
    english_tags = []
    for tag in tags:
        translated = get_translated_tag(tag, translation_cache)
        english_tags.append(translated)
        
    return english_tags
