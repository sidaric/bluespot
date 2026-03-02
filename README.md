# Időnyilvántartó Webalkalmazás

Ez egy egyszerű időnyilvántartó webalkalmazás Laravel alapokon.  
A felhasználók havi bontásban rögzíthetik a ledolgozott óráikat, több bejegyzést is felvehetnek egy napra, valamint módosíthatják és törölhetik azokat.

A rendszer backend–frontend kommunikációja kizárólag AJAX segítségével történik.

---

## Funkciók

- Egyszerű bejelentkezés és regisztráció
- Minden felhasználó csak a saját adatait látja
- Havi nézet
- Több bejegyzés egy napra
- Órák havi összesítése
- Bejegyzések létrehozása
- Bejegyzések módosítása
- Bejegyzések törlése
- Toast értesítések mentéskor és hiba esetén
- AJAX alapú működés
- Migrációs script az adatbázishoz

---

## Technológiák

- PHP 8+
- Laravel
- SQLite adatbázis
- Composer
- Node.js + NPM
- Vite
- Vanilla JavaScript
- Tailwind CSS

---

## Gyors telepítés (ajánlott)

A projekt tartalmaz egy automatikus telepítő scriptet.

A script:

- telepíti a függőségeket
- létrehozza a .env fájlt
- létrehozza az adatbázist
- lefuttatja a migrációkat
- elindítja a szervert

Futtatás:

chmod +x install-and-run.sh

./install-and-run.sh

Az alkalmazás ezután elérhető:

http://127.0.0.1:8000

Leállítás:

Ctrl + C

Kézi telepítés

1. Repository klónozása
git clone https://github.com/sidaric/bluespot.git
cd bluespot
2. PHP függőségek telepítése
composer install
3. Frontend függőségek telepítése
npm install
4. .env fájl létrehozása
cp .env.example .env
5. Alkalmazás kulcs generálása
php artisan key:generate
6. SQLite adatbázis létrehozása
touch database/database.sqlite

A .env fájlban:

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
7. Migráció futtatása
php artisan migrate
8. Szerver indítása

Terminál 1:

php artisan serve

Terminál 2:

npm run dev

Alkalmazás:

http://127.0.0.1:8000
Használat
Regisztráció

Új felhasználó létrehozása:

/register
Bejelentkezés
/login
Időnyilvántartás
/time

Itt lehet:

hónapot váltani

új bejegyzést rögzíteni

bejegyzést szerkeszteni

bejegyzést törölni

Adatbázis struktúra
users

Laravel alapértelmezett felhasználó tábla.

time_entries

Mezők:

id

user_id

entry_date

minutes

description

created_at

updated_at

API végpontok

Az alkalmazás AJAX hívásokat használ.

Bejegyzések listázása
GET /api/time-entries?month=YYYY-MM
Új bejegyzés
POST /api/time-entries
Bejegyzés módosítása
PUT /api/time-entries/{id}
Bejegyzés törlése
DELETE /api/time-entries/{id}