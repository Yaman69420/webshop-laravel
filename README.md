# NOVA Webshop

Een volwaardige webshop gebouwd in Laravel 13 met Livewire 4, Flux UI en Stripe betalingen.

---

## Cursisten

| Naam | Rol |
|---|---|
| Yaman Terkawi | Frontend — Livewire componenten, views, admin CRUD, feature tests, README |
| Jan De Smet | Backend — migraties, models, Actions, Services, Stripe, Policies, middleware, unit tests |

---

## Technologieën

| Pakket | Versie |
|---|---|
| PHP | 8.4 |
| Laravel | 13.6 |
| Livewire | 4.1 |
| Flux UI | 2.13 (free tier) |
| Tailwind CSS | 4 |
| Stripe PHP SDK | 20 |
| Pest | 4 |
| MySQL | 8 |

---

## Installatie

### 1. Repository clonen

```bash
git clone https://github.com/Yaman69420/webshop-laravel.git
cd webshop-laravel
```

### 2. Dependencies installeren

```bash
composer install
npm install
```

### 3. Environment configureren

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` en pas aan:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webshop_laravel
DB_USERNAME=root
DB_PASSWORD=jouw_wachtwoord
```

### 4. Database aanmaken

Maak een database aan met de naam `webshop_laravel` via phpMyAdmin of MySQL CLI:

```sql
CREATE DATABASE webshop_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Migraties en testdata

```bash
php artisan migrate --seed
```

Dit maakt alle tabellen aan en vult de database met:
- 5 categorieën
- 20 producten
- 1 admin gebruiker
- 2 testklanten

### 6. Storage symlink

```bash
php artisan storage:link
```

### 7. Frontend bouwen

```bash
npm run build
```

Of voor development met hot reload:

```bash
npm run dev
```

### 8. Site bekijken

De applicatie is bereikbaar via **Laravel Herd** op `http://webshop-laravel.test`.

Zonder Herd: `php artisan serve` → `http://localhost:8000`

---

## Admin credentials

```
E-mail:     admin@nova.test
Wachtwoord: password
```

## Testklanten

```
E-mail:     jan@example.com
Wachtwoord: password

E-mail:     marie@example.com
Wachtwoord: password
```

---

## Stripe testen

Voeg je Stripe test keys toe in `.env`:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

Testkaarten:

| Scenario | Kaartnummer |
|---|---|
| Succesvolle betaling | `4242 4242 4242 4242` |
| Geweigerde betaling | `4000 0000 0000 0002` |

Vervaldatum: elke datum in de toekomst. CVC: 3 willekeurige cijfers.

---

## Social login

Social login is geïmplementeerd via **Laravel Socialite** met Google en GitHub.

Voeg toe aan `.env`:

```env
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=http://webshop-laravel.test/auth/google/callback

GITHUB_CLIENT_ID=...
GITHUB_CLIENT_SECRET=...
GITHUB_REDIRECT_URI=http://webshop-laravel.test/auth/github/callback
```

---

## Architectuurkeuzes

**Actions boven dikke controllers**
Alle business logic zit in `app/Actions/{Domain}/` met één `handle()` of `execute()` methode. Controllers en Livewire componenten zijn dun en delegeren naar Actions.

**CartService via sessie en database**
De winkelwagen wordt opgeslagen in de sessie voor gasten en gekoppeld aan de gebruiker na inloggen.

**Stripe redirect flow**
We gebruiken de Stripe hosted checkout met redirect. Na betaling verifiëren we de sessie via de Stripe API — nooit blind vertrouwen op een redirect parameter.

**Snapshots in order_details**
Bij het plaatsen van een bestelling kopiëren we de productnaam en prijs op dat moment. Zo blijft de orderhistorie correct, ook als het product later wordt aangepast.

**SoftDeletes op producten, categorieën en orders**
Verwijderen is altijd soft — data gaat nooit verloren.

---

## Bekende beperkingen

- Productafbeeldingen zijn niet inbegrepen in de seeders. Upload via het admin paneel.
- Social login vereist eigen OAuth credentials (zie sectie hierboven).
