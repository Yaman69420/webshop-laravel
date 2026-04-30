# NOVA Webshop

Een volwaardige webshop gebouwd in Laravel 13 met Livewire 4, Flux UI en Stripe betalingen. Bezoekers kunnen producten bekijken, een winkelmandje beheren en betalen via Stripe. Admins beheren producten, categorieën en bestellingen via een afgeschermd paneel.

---

## Cursisten

| Naam | Rol |
|---|---|
| Yaman Terkawi | Frontend — Livewire componenten, Flux UI views, admin CRUD, social login, feature tests, README |
| Jan De Smet | Backend — migraties, models, Actions, Services, Stripe, Policies, middleware, unit tests, seeders |

---

## Technologieën

| Pakket | Versie |
|---|---|
| PHP | 8.4 |
| Laravel | 13 |
| Livewire / Volt | 4 / 1 |
| Flux UI | 2 (free tier) |
| Tailwind CSS | 4 |
| Laravel Socialite | 5 |
| Stripe PHP SDK | latest |
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

Open `.env` en pas de database aan:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webshop_laravel
DB_USERNAME=root
DB_PASSWORD=jouw_wachtwoord
```

### 4. Database aanmaken

```sql
CREATE DATABASE webshop_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Migraties en testdata

```bash
php artisan migrate:fresh --seed
```

Dit maakt alle tabellen aan en vult de database met:
- 5 categorieën
- 20 producten met afbeeldingen
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

### 8. Applicatie starten

```bash
php artisan serve
```

De applicatie is bereikbaar op `http://localhost:8000`.

> Via Laravel Herd is de applicatie ook bereikbaar op `http://webshop-laravel.test`.

---

## Inloggegevens

### Admin

```
E-mail:     admin@nova.test
Wachtwoord: password
```

Admin paneel: `http://localhost:8000/admin`

### Testklanten

```
E-mail:     jan@example.com
Wachtwoord: password

E-mail:     marie@example.com
Wachtwoord: password
```

---

## Stripe configureren

Maak een gratis account aan op [dashboard.stripe.com](https://dashboard.stripe.com) en haal je test API keys op via **Developers → API keys**.

Voeg toe aan `.env`:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

Daarna:

```bash
php artisan config:clear
```

### Testkaarten

| Scenario | Kaartnummer |
|---|---|
| Succesvolle betaling | `4242 4242 4242 4242` |
| Geweigerde betaling | `4000 0000 0000 0002` |

Vervaldatum: elke datum in de toekomst. CVC: 3 willekeurige cijfers.

> **Belangrijk:** de applicatie verifieert de betaling via de Stripe API op de success pagina. Een order wordt pas op `paid` gezet na bevestiging van Stripe — nooit blind op basis van de redirect URL.

---

## Social login configureren

Social login is geïmplementeerd via **Laravel Socialite** (Optie B) met Google en GitHub.

> **Let op:** gebruik `php artisan serve` voor social login. Het `.test` domein van Herd wordt niet geaccepteerd als redirect URI door Google en GitHub.

### GitHub

1. Ga naar [github.com/settings/developers](https://github.com/settings/developers) → **New OAuth App**
2. Vul in:
   - Homepage URL: `http://localhost:8000`
   - Callback URL: `http://localhost:8000/auth/github/callback`
3. Kopieer Client ID en Client Secret

### Google

1. Ga naar [console.cloud.google.com](https://console.cloud.google.com) → nieuw project
2. Ga naar **APIs & Services → Credentials → Create OAuth client ID**
3. Application type: Web application
4. Authorized redirect URI: `http://localhost:8000/auth/google/callback`
5. Kopieer Client ID en Client Secret

### `.env` aanvullen

```env
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...

GITHUB_CLIENT_ID=...
GITHUB_CLIENT_SECRET=...
```

### Accountkoppeling

Als een gebruiker zich eerst registreert met e-mail/wachtwoord en daarna inlogt met een social provider op hetzelfde e-mailadres, wordt de bestaande account hergebruikt via `firstOrCreate()` op het e-mailadres. De `social_provider` en `social_id` worden dan bijgewerkt. De gebruiker verliest geen data en hoeft geen nieuw account aan te maken.

---

## Tests uitvoeren

```bash
php artisan test
```

Alle tests zijn groen op een verse installatie met `migrate:fresh --seed`.

---

## Architectuurkeuzes

**Actions boven dikke controllers**
Alle business logic zit in `app/Actions/{Domain}/` met één `execute()` methode. Livewire componenten zijn dun en delegeren altijd naar een Action of Service.

**CartService**
De winkelwagen wordt beheerd via `app/Services/CartService.php` en opgeslagen in de sessie. Dit houdt de Livewire componenten vrij van cart-logica.

**Stripe hosted checkout met verificatie**
We gebruiken de Stripe hosted checkout redirect flow. Na betaling verifieert de success pagina de sessie via `StripeService::retrieveSession()` voordat de order op `paid` wordt gezet. Dit voorkomt manipulatie van de redirect URL.

**Snapshots in order_items**
Bij het aanmaken van een bestelling kopiëren we `product_name` en `product_price_in_cents` op dat moment. Zo blijft de orderhistorie correct, ook als het product later wordt aangepast of verwijderd.

**SoftDeletes op Product, Category en Order**
Verwijderen is altijd soft — data gaat nooit permanent verloren en kan worden hersteld.

**Enums voor statussen**
`OrderStatus` en `UserRole` zijn PHP backed enums in `app/Enums/`. Ze bevatten `label()` en `color()` methodes voor consistente weergave in de UI.

---

## Bekende beperkingen

- Social login vereist eigen OAuth credentials (zie sectie hierboven) — gedeelde test credentials worden niet meegeleverd in de repo.
- Stripe webhooks zijn niet geïmplementeerd (niet vereist). De betaalverificatie gebeurt via de redirect flow.

---

## QR-code login

Gebruikers kunnen inloggen op desktop door een QR-code te scannen met een mobiel toestel waarop ze al ingelogd zijn.

### Flow

1. Klik op **"Login met QR-code"** op de loginpagina
2. Scan de QR-code met je telefoon (je moet al ingelogd zijn)
3. Bevestig of weiger de login op je telefoon
4. Na bevestiging wordt de desktop automatisch ingelogd

### Architectuur

De QR-login volgt dezelfde patronen als de rest van de applicatie:

- **Actions:** `app/Actions/QrLogin/` — `StartQrSessionAction`, `ConfirmQrLoginAction`, `DenyQrLoginAction`, `ConsumeQrLoginAction`
- **Service:** `app/Services/QrLoginService.php` — token generatie, hashing, lookup
- **Enum:** `app/Enums/QrLoginStatus.php` — status values met `label()` en `color()`
- **Model:** `app/Models/QrLoginSession.php` — Eloquent model met scopes en state transitions
- **Controller:** `app/Http/Controllers/Auth/QrLoginController.php` — dunne controller die delegeert naar Actions

