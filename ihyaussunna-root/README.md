# JAMIA IHYA-US-SUNNA - WEB SETUP GUIDE
**Domain:** `ihyaussunna.in`  
**Food Counter:** `ihyaussunna.in/foodcounter`  
**Festival Portal:** `quaf.ihyaussunna.in`  

---

## എന്തുകൊണ്ട് `ihyaussunna.in`-ൽ ഫുഡ് കൗണ്ടർ കാണിച്ചു?
Hostinger-ൽ `public_html` ഫോൾഡറിലേക്ക് ഫുഡ് കൗണ്ടറിന്റെ ഫയലുകൾ അപ്‌ലോഡ് ചെയ്തപ്പോൾ `public_html/index.php` എന്നത് ഫുഡ് കൗണ്ടർ പേജായി മാറിയതുകൊണ്ടാണ് മെയിൻ ഡൊമെയ്ൻ അടിച്ചാൽ കൗണ്ടർ വന്നിരുന്നത്.

ഇനി `ihyaussunna.in`-ൽ വരുന്നത് ഇഹ്‌യാഉസ്സുന്നയുടെ മനോഹരമായ **"Coming Soon"** പേജ് ആയിരിക്കും. ഫുഡ് കൗണ്ടർ താഴെ പറയുന്ന ലിങ്കുകളിൽ മാത്രമായിരിക്കും ലഭ്യമാകുക:

1. `https://ihyaussunna.in/foodcounter/index.php`
2. `https://ihyaussunna.in/foodcounter`
3. `https://ihyaussunna.in/foodcounter/admin`
4. `https://ihyaussunna.in/foodcounter/admin.php`

---

## 1 മിനിറ്റ് കൊണ്ട് Hostinger-ൽ ചെയ്യേണ്ട ലളിതമായ കാര്യങ്ങൾ:

### ഘട്ടം 1: Hostinger File Manager തുറക്കുക
1. Hostinger hPanel തുറക്കുക -> **File Manager** (Files -> File Manager) ക്ലിക്ക് ചെയ്യുക.
2. `public_html` ഫോൾഡർ തുറക്കുക.

### ഘട്ടം 2: റൂട്ട് ഫയലുകൾ മാറ്റുക
1. `ihyaussunna-root/index.php` ഫയൽ ഡൗൺലോഡ് ചെയ്ത് Hostinger-ലെ `public_html/index.php` ആയി അപ്‌ലോഡ് ചെയ്യുക (പഴയ ഫയൽ Replace ചെയ്യുക).
2. `ihyaussunna-root/.htaccess` ഫയൽ `public_html/.htaccess` ആയി അപ്‌ലോഡ് ചെയ്യുക.

### ഘട്ടം 3: `foodcounter` ഫോൾഡർ പരിശോധിക്കുക
1. `public_html` ഫോൾഡറിനുള്ളിൽ `foodcounter` എന്ന സബ്-ഫോൾഡർ ഉണ്ടെന്ന് ഉറപ്പാക്കുക.
2. അതിനുള്ളിൽ താഴെ പറയുന്ന ഫയലുകൾ ഉണ്ടെന്ന് ഉറപ്പുവരുത്തുക:
   - `index.php` (അപ്‌ഡേറ്റ് ചെയ്ത പുതിയ റൂട്ടിംഗ് ഉള്ളത്)
   - `admin.php` (അഡ്മിൻ മാനേജ്‌മെന്റ്)
   - `api.php` (സ്കാൻ എപിഐ)
   - `db.php` (ഡാറ്റാബേസ് കണക്ഷൻ)
   - `.htaccess` (അഡ്മിൻ പേജ് 404 വരാതിരിക്കാനുള്ള റീറൈറ്റ് റൂൾസ്)

---

## ലിങ്കുകൾ പരിശോധിക്കാം:
- **മെയിൻ വെബ്സൈറ്റ്:** `https://ihyaussunna.in/` (Coming Soon പേജ് മാത്രം കാണിക്കും).
- **കൗണ്ടർ സ്കാനർ:** `https://ihyaussunna.in/foodcounter` അല്ലെങ്കിൽ `https://ihyaussunna.in/foodcounter/index.php`
- **കൗണ്ടർ അഡ്മിൻ:** `https://ihyaussunna.in/foodcounter/admin` അല്ലെങ്കിൽ `https://ihyaussunna.in/foodcounter/admin.php`
