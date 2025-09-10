import requests
from bs4 import BeautifulSoup
import re
import time

def scrape_facebook(query):
    headers = {
        'User-Agent': 'Mozilla/5.0 (Android 10; Mobile; rv:91.0) Gecko/91.0 Firefox/91.0',
        'Accept-Language': 'tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7'
    }
    url = f"https://m.facebook.com/search/top/?q={query}"
    response = requests.get(url, headers=headers)
    soup = BeautifulSoup(response.text, 'html.parser')
    numbers = set()
    for link in soup.find_all('a', href=True):
        text = link.get_text()
        if re.match(r'\+90\s?\d{3}\s?\d{3}\s?\d{4}', text):
            numbers.add(text)
    with open('numbers.txt', 'w') as f:
        for num in numbers:
            f.write(num + '\n')
    print("Numaralar numbers.txt'ye kaydedildi.")
    time.sleep(30)

scrape_facebook("iletişim")
