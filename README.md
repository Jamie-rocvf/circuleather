# Circuleather - Voorraad- & Bestelbehersysteem

Een PHP-webapplicatie ontwikkeld voor **Circuleather** om de inkomende voorraad van leer te beheren, bestellingen in te zien en gebruikerstoegang te reguleren op basis van specifieke rollen (*uitpakken* en *inpakken*).

---

## 📋 Inhoudsopgave
1. [Over het Project](#-over-het-project)
2. [Systeemarchitectuur & Rollen](#-systeemarchitectuur--rollen)
3. [Pagina- en Bestandsbeschrijvingen](#-pagina--en-bestandsbeschrijvingen)
4. [Database Structuur](#-database-structuur)
5. [Installatie & Vereisten](#-installatie--vereisten)

---

## 🍃 Over het Project

Circuleather is een voorraadbeheersysteem dat speciaal is ontworpen om restpartijen of specifieke stukken leer efficiënt te registreren, te filteren en te verwerken. 

### Belangrijkste functionaliteiten:
* **Dynamische filtering:** Filter voorraad op leertype, kleur, dikte en specifieke maatcategorieën (A, B, C).
* **Faceted Search / Dynamische tellingen:** De tellingen in de filters passen zich automatisch aan op basis van de reeds geselecteerde criteria.
* **Geauthenticeerd beheer:** Rolgebaseerde toegang (RBAC) voor medewerkers.
* **Batch-invoer van ontvangsten:** Voeg tot 5 stukken leer tegelijk toe gekoppeld aan één specifieke levering/ontvangst.
* **Orderoverzicht:** Bekijk geplaatste bestellingen inclusief gedetailleerde items en totaalprijzen.

---

## 🔑 Systeemarchitectuur & Rollen

Het systeem maakt gebruik van PHP-sessies en rechten (`mag_insert` en `mag_orders`) om functionaliteiten af te schermen.

| Rol (Klasse) | `mag_insert` | `mag_orders` | Toegang & Rechten |
| :--- | :---: | :---: | :--- |
| **Uitpakken** | `true` | `false` | Kan de voorraad bekijken en nieuwe ontvangsten van leer invoeren (`insert.php`). |
| **Inpakken** | `false` | `true` | Kan de voorraad bekijken en openstaande/verwerkte bestellingen bekijken (`orders.php`). |

---

## 📄 Pagina- en Bestandsbeschrijvingen

### 1. `vooraad_beheer.php` (Hoofdpagina / Dashboard)
Het centrale dashboard waar de actieve voorraad getoond wordt.
* **Sessie & Beveiliging:** Vereist dat een gebruiker is ingelogd (via `partials/session_check.php`).
* **Filtering & Pagina-indeling:**
  * **Status-filter:** Toont alleen items waarbij de status **niet** `'besteld'` is.
  * **Maatcategorieën:** 
    * **A:** 23 tot 40 cm
    * **B:** 40 tot 60 cm
    * **C:** 60+ cm
  * **Paginering:** Toont maximaal 12 items per pagina met automatische paginageneratie.
* **Dynamische navigatie:** Toont alleen knoppen naar `insert.php` of `orders.php` als de ingelogde gebruiker hier de juiste rechten voor heeft.

---

### 2. `insert.php` (Ontvangst Invoeren)
Pagina voor de afdeling *Uitpakken* om nieuwe binnenkomende partijen leer te registreren.
* **Toegangscontrole:** Alleen toegankelijk met `mag_insert = true`.
* **Werking:**
  1. Gebruiker vult de **Herkomst** (leverancier/bron) en **Datum** in.
  2. Biedt de mogelijkheid om tot **5 stukken leer** in één keer in te voeren.
  3. Lege rijen (zonder ingevuld leertype) worden automatisch genegeerd.
* **Database Transactie:**
  * Maakt eerst een record aan in de tabel `Ontvangst`.
  * Voegt per ingevuld stuk een record toe aan de tabel `voorraad`.
  * Koppelt het voorraad-item en de ontvangst in de koppeltabel `ontvangst_items` (inclusief gewicht en bruikbaarheidsscore).

---

### 3. `orders.php` (Bestellingenoverzicht)
Pagina voor de afdeling *Inpakken* om overzicht te houden over klantbestellingen.
* **Toegangscontrole:** Alleen toegankelijk met `mag_orders = true`.
* **Werking:**
  * Haalt alle bestellingen op, gesorteerd op meest recente besteldatum.
  * Haalt gekoppelde artikelen op uit `bestelling_items` en `voorraad`.
  * **Opmerking:** Hier worden voorraaditems **wel** getoond als ze de status `'besteld'` hebben, om de bestellingsinhoud correct weer te geven.
  * Berekent automatisch subtotalen per item en het eindtotaal per bestelling.

---

### 4. `login.php` & `logout.php` (Authenticatie)
* **`login.php`:**
  * Verifieert de gebruikersnaam en het gehashte wachtwoord via `password_verify()`.
  * Slaat bij succes de gebruikersgegevens en de specifieke rechten (`mag_insert`, `mag_orders`) op in de `$_SESSION`.
  * Bevat afhandeling voor verlopen sessies (`?timeout=1`) en succesvolle registratie (`?geregistreerd=1`).
* **`logout.php`:**
  * Vernietigt de actieve PHP-sessie en stuurt de gebruiker direct terug naar de inlogpagina.

---

### 5. `registreer.php` (Account Aanmaken)
* Stelt nieuwe gebruikers in staat een account aan te maken.
* Verplicht het kiezen van een rol (**Klasse**):
  * **Uitpakken:** Kent automatisch `mag_insert = 1` en `mag_orders = 0` toe.
  * **Inpakken:** Kent automatisch `mag_insert = 0` en `mag_orders = 1` toe.
* Wachtwoorden worden veilig gehasht via `password_hash()` met het `PASSWORD_DEFAULT` algoritme.

---

## 🗄️ Database Structuur

Hieronder staat het relationele schema op basis van de SQL-queries in de code:

```
+------------------+       +-----------------------+       +-------------------+
|    gebruikers    |       |       Ontvangst       |       |     voorraad      |
+------------------+       +-----------------------+       +-------------------+
| id (PK)          |       | id (PK)               |       | id (PK)           |
| username         |       | herkomst              |       | leertype          |
| password         |       | datum                 |       | dikteMM           |
| mag_insert (bool)|       +-----------+-----------+       | lengteCM          |
| mag_orders (bool)|                   | 1                 | breedteCM         |
+------------------+                   |                   | gewichtG          |
                                       | N                 | kleur             |
                           +-----------v-----------+       | prijs             |
                           |    ontvangst_items    |       | status            |
                           +-----------------------+       +---------+---------+
                           | id (PK)               |                 | 1
                           | ontvangst_id (FK)     |                 |
                           | voorraad_id (FK)  <---+-----------------+
                           | gewichtG              |                 | N
                           | bruikbaarheid         |       +---------v---------+
                           +-----------------------+       |  bestelling_items |
                                                           +-------------------+
                                                           | id (PK)           |
+------------------+                                       | bestelling_id(FK) |<---+
|   bestellingen   |                                       | voorraad_id (FK)  |    |
+------------------+                                       | aantal            |    |
| ID (PK)          |<--------------------------------------| prijs             |    |
| locatie           | 1                                   +-------------------+    |
| email            |                                                                |
| status           |                                                                |
| besteldatum      |                                                                |
| verstuurdatum    |                                                                |
+------------------+                                                                |
        |                                                                           |
        +---------------------------------------------------------------------------+
```

---

## 🛠️ Installatie & Vereisten

1. **Serververeisten:**
   * Webserver (zoals Apache of Nginx)
   * PHP 7.4 of hoger
   * MySQL / MariaDB database

2. **Vereiste Hulpbestanden (Partials):**
   Zorg ervoor dat de map `partials/` de volgende bestanden bevat:
   * `partials/dbconnection.php`: Bevat de databaseverbinding (retourneert een MySQLi-object).
   * `partials/session_check.php`: Start de sessie en controleert of `$_SESSION['ingelogd']` gezet is (stuur anders door naar `login.php`).

3. **Mappenstructuur:**
   ```text
   ├── css/
   │   └── style.css
   ├── png/
   │   ├── logo.png
   │   └── titel.png
   ├── partials/
   │   ├── dbconnection.php
   │   └── session_check.php
   ├── insert.php
   ├── login.php
   ├── logout.php
   ├── orders.php
   ├── registreer.php
   ├── vooraad_beheer.php
   └── README.md
   ```