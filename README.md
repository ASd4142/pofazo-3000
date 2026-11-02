# Pofázó 3000

Egyszerű, közös webes társalgó HTML, CSS, JavaScript, PHP és MySQL használatával. Nem kell hozzá csomagtelepítés vagy buildelés.

## Indítás XAMPP alatt

1. Másold ezt a mappát `C:\xampp\htdocs\pofazo3000` néven a XAMPP könyvtárába.
2. A XAMPP Control Panelben indítsd el az **Apache** és a **MySQL** szolgáltatást.
3. Nyisd meg a `http://localhost/phpmyadmin` oldalt. Az **Importálás** fülön válaszd a projekt `database.sql` fájlját, majd indítsd az importot. A fájl létrehozza a `pofazo3000` adatbázist és a két táblát. Nem törli a már meglévő adatokat.
4. A `config.php` elején ellenőrizd az adatbázis-kapcsolatot. Az alapértékek: `127.0.0.1`, `pofazo3000`, `root`, üres jelszó.
5. Nyisd meg a `http://localhost/pofazo3000/` címet, és adj meg egy 2–50 karakteres nevet.

PHP 7.3 vagy újabb, PDO és pdo_mysql szükséges; a jelenlegi XAMPP PHP 8 változatai is használhatók. Ne fájlként, dupla kattintással nyisd meg a PHP-oldalakat.

## Mit tud?

- Jelszó nélküli vendégbelépés PHP-sessionnel és kijelentkezés.
- Közös társalgó, adatbázisba mentett előzményekkel, küldővel és helyi időponttal.
- 2,5 másodpercenkénti frissítés, oldalújratöltés nélküli küldés.
- Legfeljebb 500 Unicode-karakteres üzenetek; Enter küld, Shift + Enter új sort ír.
- Online/offline névsor: 30 másodpercen belüli aktivitás számít online állapotnak. A böngésző bezárása után legfeljebb körülbelül 33 másodperc múlva frissül az állapot másoknál. Háttérben a böngésző ritkíthatja a frissítést.
- Automatikus görgetés, ha már az alján olvasol; feljebb olvasáskor újüzenet-gomb.
- Mobilon megnyitható oldalsáv, kapcsolati állapot és hibaüzenetek.

A név nem hitelesített azonosító. Minden új belépés külön vendégprofilt hoz létre, azonos névvel is; a régi profil és üzenetei megmaradnak. Ugyanannak a böngészőprofilnak a lapjai közös sessiont használnak.

## Fájlok

| Fájl | Feladat |
| --- | --- |
| `index.php` | Belépésvédett chatfelület |
| `login.php`, `logout.php` | Vendégbelépés és kijelentkezés |
| `config.php` | PDO-kapcsolat, session, közös segédfüggvények |
| `get_messages.php` | Üzenetek és névsor JSON formátumban |
| `send_message.php` | Üzenetellenőrzés és mentés |
| `database.sql` | Importálható adatbázisséma |
| `script.js`, `style.css`, `logo.svg` | Működés, reszponzív megjelenés és logó |

A szerver UTC-időpontokat tárol, a böngésző a saját időzónájában jeleníti meg őket. A lekérés egyszerre 100 üzenetet hoz; további lapokkal az összes előzményt betölti.

## Kipróbálás két felhasználóval

1. Lépj be normál böngészőablakban, majd egy privát ablakban másik névvel.
2. Küldj üzenetet mindkét irányba: legkésőbb néhány másodperc múlva mindkét oldalon megjelenik.
3. Próbálj üres vagy csak szóközös üzenetet, valamint 500 és 501 karaktert küldeni.
4. Küldj ékezeteket, emojikat és `<script>alert(1)</script>` szöveget: utóbbi szövegként jelenik meg.
5. Frissítsd az oldalt: megmarad a belépés és az előzmény. Görgess feljebb, és a másik ablakból küldj üzenetet: az olvasási helyed megmarad.
6. Jelentkezz ki az egyik ablakból; a másikban ellenőrizd az offline állapotot. 375 px szélességen próbáld a mobilmenüt.
7. Állítsd le a MySQL-t: hiba jelenik meg. Újraindítás után a chat automatikusan újracsatlakozik; a sikertelenül küldött szöveg a beviteli mezőben marad.

## Ellenőrzési állapot

A JavaScript szintaxisellenőrzése sikeres volt a VS Code beépített futtatójával (`--check script.js`). Automatikus ellenőrzés igazolta, hogy a JavaScript által használt HTML-azonosítók és a helyi fájlhivatkozások léteznek, a logó érvényes SVG XML. A kliens–szerver adatmezők és a biztonsági ellenőrzések statikus átvizsgálása is megtörtént.

A XAMPP telepítése után mind a hat PHP-fájl szintaxisellenőrzése sikeres volt. A `database.sql` importja létrehozta a két táblát, a helyi PHP-kiszolgálón pedig a belépőoldal és a stíluslap HTTP 200 választ adott. Teljes böngészős és kétfelhasználós végpontteszt még nem történt; a fenti lépések helyben futtatható elfogadási próbák.

Ez egy helyi iskolai projekt: nincs jelszavas személyazonosítás, moderálás vagy üzenettörlés. Nagy adatmennyiségnél az összes előzmény és felhasználó betöltése lassulhat.
