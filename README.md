# Circuleather - Leather Inventory & Order Management System

Een PHP-gebaseerd inventarisatie- en bestellingssysteem voor leerproducten, gebouwd met Docker, MySQL en Apache.

## 📋 Overzicht

Circuleather is een webapplicatie voor het beheer van leerinventaris en klantbestellingen. Het systeem ondersteunt twee gebruikersrollen:
- **Uitpakken**: Invoeren van nieuwe leerontvangsten in het systeem
- **Inpakken**: Beheren van klantbestellingen

## 🏗️ Projectstructuur

```
circuleather/
├── src/                          # PHP-broncode en front-end
│   ├── partials/                # Herbruikbare PHP-modules
│   │   ├── dbconnection.php      # Database-verbindingslogica
│   │   └── session_check.php     # Sessie- en time-out-controle
│   ├── css/
│   │   ├── style.css             # Alle stijlen
│   │   └── png/                  # Afbeeldingen (achtergronden, logo's)
│   ├── png/                      # Applicatie-logo's en titels
│   ├── login.php                 # Login-pagina
│   ├── registreer.php            # Registratiepagina
│   ├── logout.php                # Logout-functie
│   ├── vooraad_beheer.php        # Hoofdpagina (inventarisoverzicht)
│   ├── insert.php                # Ontvangstinvoer
│   └── orders.php                # Bestellingenbeheer
├── docker-compose.yml            # Docker-services (PHP, MySQL, phpMyAdmin)
├── Dockerfile                    # PHP-containerimage
├── php.ini                       # PHP-configuratie
├── init.sql                      # Database-initialisatiescript
├── circuleather-tables.sql       # Volledige databaseschema
├── gebruikers_setup.sql          # Gebruikersinstellingsscript
└── README.md                     # Deze file
```

## 📁 Bestandsbeschrijvingen

### `src/partials/dbconnection.php`
**Doel**: Centraliseert de MySQL/MariaDB-databaseverbinding.

```php
// Maakt een MySQLi-verbinding met de database
// Connectieparameters:
// - Host: "mysql" (Docker-servicenaam)
// - Gebruiker: "root"
// - Wachtwoord: "password"
// - Database: "circuleather"
```
**Functionaliteit**:
- Bouwt de verbindingsstring
- Vangt fouten op en logt deze
- Retourneert het verbindingsobject ($conn)

### `src/partials/session_check.php`
**Doel**: Waarborgt dat gebruikers ingelogd zijn en beheert sessiontime-outs.

```php
// Time-out-instelling: 54.000 seconden (90 minuten)
// - Controleert of $_SESSION['ingelogd'] is ingesteld
// - Stuurt door naar login.php als niet ingelogd
// - Verbreekt de sessie na 90 minuten inactiviteit
// - Werkt de 'last_activity' telkens bij
```
**Functionaliteit**:
- Sessies worden automatisch beëindigd na 90 minuten inactiviteit
- Voorkont ongeautoriseerde toegang

### `src/login.php`
**Doel**: Biedt verificatiefunctionaliteit met gebruikersnaam/wachtwoordformulier.

**Logica**:
1. Controleert of gebruiker al ingelogd is → doorsturen naar voorraad_beheer.php
2. Als POST-verzoek met action='inloggen':
   - Haalt gebruikersnaam en wachtwoord op
   - Zoekt gebruiker in database (prepared statement)
   - Verifieert wachtwoord met `password_verify()`
   - Slaat machtigingen op in $_SESSION (mag_insert, mag_orders)
3. Toont foutmeldingen of success-bericht

**Sessievariabelen**:
- `$_SESSION['ingelogd']`: true als succesvol ingelogd
- `$_SESSION['username']`: ingelogde gebruiker
- `$_SESSION['mag_insert']`: mag ontvangsten invoeren
- `$_SESSION['mag_orders']`: mag bestellingen beheren

### `src/registreer.php`
**Doel**: Registratie van nieuwe gebruikersaccounts met rolkeuze.

**Logica**:
1. Haalt gebruikersnaam, wachtwoord en rol ("uitpakken"/"inpakken") op
2. Valideert input (niet leeg, rol moet geldig zijn)
3. Controleert dubbele gebruikersnamen
4. Hashelt wachtwoord met `password_hash()`
5. Wijst rechten toe op basis van gekozen rol:
   - **uitpakken**: mag_insert = 1, mag_orders = 0
   - **inpakken**: mag_insert = 0, mag_orders = 1
6. Voegt gebruiker in de database in

### `src/logout.php`
**Doel**: Vernietigt de sessie en stuurt door naar login.php.

```php
session_start();
session_destroy();           // Vernietigt alle sessiegegevens
header("Location: login.php");
exit();
```

### `src/vooraad_beheer.php`
**Doel**: Hoofdpagina; toont inventaris met geavanceerde filtrering en paginering.

**Kernfunctionaliteiten**:

1. **Filterlogica** (4 filters; onafhankelijk werken):
   - Leertype (bijv. "kalfsleer")
   - Kleur (bijv. "bruin")
   - Dikte (bijv. "2.5 mm")
   - Maat (A: 23-40 cm, B: 40-60 cm, C: 60+ cm)
   
   Elk filter toont alleen beschikbare opties (telt tellingen opnieuw per filter).

2. **Bestelmodusintegratie**:
   - Als gebruiker mag_orders = true en start een bestelling → bestelModus activeren
   - Toont checkbox per product
   - Slaat geselecteerde product-ID's op in $_SESSION['mandje']

3. **Paginering** (12 items per pagina):
   - Toon max. 5 paginaknoppen tegelijk
   - Gecentreerd rond huidige pagina

4. **Databasequery's**:
   - 5 voorbereid geparameriseerde query's (1 per filter + hoofd-query)
   - Filtert altijd status != 'besteld' (alleen beschikbare items)

5. **HTML-layout**:
   - Header: logo, titel, actieknoppen (insert/orders), gebruikersinformatie
   - Zijbalk: filters, paginering
   - Inhoud: productgrid (kaarten met specs en prijs)
   - Bestelbalk: toont aantal geselecteerde items (als in bestelmode)

### `src/insert.php`
**Doel**: Invoer van nieuwe leerontvangsten in inventaris.

**Werkstroom**:
1. Controleert mag_insert-machtiging
2. Haalt formuliergegevens op: herkomst (tekst), datum, tot 6 stukken
3. Per stuk: leertype, dikte, lengte, breedte, kleur, prijs, gewicht, bruikbaarheid

**Databaselogica**:
1. Voeg Ontvangst-record in (herkomst, datum)
2. Voor elk stuk:
   - Voeg voorraad-record in (leertype, afmetingen, kleur, prijs)
   - Voeg ontvangst_items-record in (link tussen Ontvangst en voorraad)
3. Na succes: doorsturen naar vooraad_beheer.php met succesbericht

**Validatie**:
- Herkomst verplicht
- Minstens 1 stuk met leertype vereist

**JavaScript**:
- Button "+ Stuk toevoegen" klont het laatste stuk-blok
- Nummering werkt mee (Stuk 1, Stuk 2, enz.)

### `src/orders.php`
**Doel**: Beheer bestellingen; samenstellen en opslaan.

**Twee modes**:

1. **Normale modus** (bestelModus = false):
   - Toont historische bestellingen in tabelvorm
   - Button "+ Nieuwe bestelling aanmaken" activeert bestelModus

2. **Bestelmode** (bestelModus = true):
   - Gebruiker is op vooraad_beheer.php en selecteert producten
   - Voigt hier in: locatie (bijv. adres), email, besteldatum
   - Valideert:
     - Locatie en email verplicht
     - Minstens 1 product geselecteerd
     - Alle products nog beschikbaar (server-side hecheck)
   - **Transactie**:
     - Selecteer producten met lock (FOR UPDATE)
     - Als beschikbaarheid gewijzigd → rollback, error tonen
     - Anders: maak Bestelling-record, voeg bestelling_items in, update status naar 'besteld'

**Bestellingen-overzicht**:
- Toont alle bestellingen (nieuwste eerst)
- Per bestelling: details (locatie, email, datums), status, tabel met producten
- Totaalbedrag berekend uit sum(aantal × prijs)

**Sessie**:
- `$_SESSION['bestel_modus']`: Geeft aan of in bestelmode
- `$_SESSION['mandje']`: Array van product-ID's

## 🗄️ Databaseschema

### Tabellen:

**gebruikers**
```
id (INT, PK)
username (VARCHAR)
password (VARCHAR, gehashed)
mag_insert (TINYINT) - Mag ontvangsten invoeren
mag_orders (TINYINT) - Mag bestellingen beheren
```

**voorraad**
```
id (INT, PK, AUTO_INCREMENT)
leertype (VARCHAR)
dikteMM (INT)
lengteCM (INT)
breedteCM (INT)
gewichtG (FLOAT)
kleur (VARCHAR)
prijs (DECIMAL)
status (ENUM: 'beschikbaar', 'besteld')
```

**Ontvangst**
```
id (INT, PK, AUTO_INCREMENT)
herkomst (VARCHAR)
datum (DATE)
```

**ontvangst_items**
```
id (INT, PK)
ontvangst_id (INT, FK → Ontvangst.id)
voorraad_id (INT, FK → voorraad.id)
gewichtG (FLOAT)
bruikbaarheid (FLOAT)
```

**bestellingen**
```
ID (INT, PK, AUTO_INCREMENT)
locatie (VARCHAR)
email (VARCHAR)
status (VARCHAR: 'in behandeling', etc.)
besteldatum (DATE)
verstuurdatum (DATE, nullable)
```

**bestelling_items**
```
id (INT, PK)
bestelling_id (INT, FK → bestellingen.ID)
voorraad_id (INT, FK → voorraad.id)
aantal (INT, default 1)
prijs (DECIMAL)
```

## 🚀 Installatie & Setup

### Vereisten
- Docker & Docker Compose
- Terminal/CLI

### Stappen

1. **Repository clonen**:
   ```bash
   git clone <repo-url>
   cd circuleather
   ```

2. **Docker-containers starten**:
   ```bash
   docker-compose up -d
   ```

3. **Database initialiseren** (gebeurt automatisch via `init.sql`):
   - PHP-container: `http://localhost`
   - phpMyAdmin: `http://localhost:8080`

4. **Eerste gebruiker aanmaken**:
   - Ga naar `http://localhost/registreer.php`
   - Vul gebruikersnaam en wachtwoord in
   - Kies rol: "Uitpakken" (mag_insert) of "Inpakken" (mag_orders)

5. **Inloggen**:
   - Ga naar `http://localhost/login.php`
   - Voer credentials in

### Configuratie

**`php.ini`**: PHP-instellingen
- `session.gc_maxlifetime`: Bepaalt hoe lang sessiefuncties beschikbaar zijn (standaard 1440 seconden)

**`docker-compose.yml`**: Services en volumekoppelingen
- PHP container mappen `./src` naar `/var/www/html`
- MySQL accepteert verbindingen op poort 3306
- phpMyAdmin op poort 8080

## 🔒 Beveiliging

1. **Wachtwoordhashing**: Alle wachtwoorden worden gehashed met `password_hash()` (PASSWORD_DEFAULT)
2. **SQL-Injection-preventie**: Alle database-queries gebruiken prepared statements met `bind_param()`
3. **XSS-preventie**: Output wordt ge-escaped met `htmlspecialchars()`
4. **Sessiebeheer**: 90-minuten time-out, 'last_activity' tracking
5. **Transactioneel beheer**: Bestellingen gebruiken MySQL-transacties (begin_transaction, commit, rollback) en rij-locking (FOR UPDATE)

## 💡 Workflow-voorbeelden

### Scenario 1: Ontvangst invoeren (Rol: Uitpakken)

1. Log in → Ga naar voorraad_beheer.php
2. Klik "insert" → Ga naar insert.php
3. Vul herkomst in (bijv. "Fabrikant A")
4. Voeg 3 stukken leer toe (per stuk: type, maat, kleur, prijs)
5. Klik "Ontvangst opslaan" → Opgeslagen in DB, teruggestuurd naar voorraad_beheer

### Scenario 2: Bestelling maken (Rol: Inpakken)

1. Log in → Ga naar voorraad_beheer.php
2. Klik "orders" → Ga naar orders.php
3. Klik "+ Nieuwe bestelling aanmaken" → Teruggestuurd naar voorraad_beheer **in bestelmode**
4. Filter op gewenste leertype/kleur/maat
5. Vink gewenste stukken aan → Opgeslagen in $_SESSION['mandje']
6. Klik "Bestelling afronden" → Terug naar orders.php
7. Vul locatie/email in, controleer totaalbedrag
8. Klik "Bestelling opslaan" → Transactie uitvoerd, status → "besteld", mail verzonden (toekomstig)

### Scenario 3: Filtering en paginering

1. Ga naar voorraad_beheer.php
2. Filter op "kalfsleer" en kleur "bruin" → Dropdown's onthouden filters
3. Klik paginanummer 3 → URL behoud filters in query string
4. Elk product toont leertype, dikte, maat, gewicht, kleur, prijs

## 🛠️ Technische details

### PHP-versie: 8.3
### Database: MariaDB (latest)
### Webserver: Apache met mod_php

### Gebruikte PHP-functies

| Functie | Doel |
|---------|------|
| `session_start()` | Sessie starten |
| `password_hash()` | Wachtwoord hashen |
| `password_verify()` | Wachtwoord verifiëren |
| `htmlspecialchars()` | XSS-preventie |
| `mysqli::prepare()` | Prepared statements |
| `bind_param()` | Parametersbinding |
| `execute()` | Query uitvoeren |
| `header()` | Redirects |

### Databasequery-patronen

1. **Filtered SELECT met COUNT (filters)**:
   ```sql
   SELECT leertype, COUNT(*) FROM voorraad WHERE status != 'besteld' AND kleur = ? GROUP BY leertype
   ```

2. **Transactioneel INSERT (bestellingen)**:
   ```sql
   BEGIN;
   SELECT id, prijs FROM voorraad WHERE id IN (...) AND status = 'beschikbaar' FOR UPDATE;
   INSERT INTO bestellingen VALUES (...);
   INSERT INTO bestelling_items VALUES (...);
   COMMIT;
   ```

## 📝 Licentie

Schoolproject (OOP PHP/MySQL cursus)

## 🤝 Bijdragen

Dit project maakt deel uit van een eduactief curriculum. Suggesties zijn welkom via pull requests.
