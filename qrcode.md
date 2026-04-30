Je werkt in een reeds bestaande webshop-applicatie. Implementeer een veilige QR-code login flow waarmee een gebruiker op desktop kan inloggen door een QR-code te scannen met een mobiel toestel waarop hij/zij al ingelogd is.

Doel:
Voeg een production-ready QR-code login mogelijkheid toe zonder bestaande login flows te breken.

Functionele flow:
1. Desktopgebruiker klikt op “Login met QR-code”.
2. Backend maakt een tijdelijke QR-login sessie aan met:
   - unieke random token
   - status: pending
   - expiry van maximaal 2 minuten
   - gekoppelde browser/session identifier
3. Frontend toont een QR-code met een URL zoals:
   /qr-login/scan?token=<secure-token>
4. Mobiele gebruiker scant de QR-code.
5. Mobiele gebruiker moet al ingelogd zijn.
6. Backend herkent de gebruiker via bestaande auth/session/JWT.
7. Mobiele gebruiker krijgt een bevestigingsscherm:
   “Wil je inloggen op dit apparaat?”
   Toon indien mogelijk browser/device info.
8. Na bevestiging wordt de QR-login sessie gemarkeerd als approved en gekoppeld aan de user_id.
9. Desktop frontend detecteert dit via WebSocket, Server-Sent Events of veilige polling.
10. Backend maakt vervolgens een normale login/session aan voor de desktopgebruiker.
11. Token wordt onmiddellijk ongeldig gemaakt na gebruik.

Belangrijke security-eisen:
- QR-token mag geen user data bevatten.
- Token moet cryptografisch random zijn.
- Token mag maar één keer gebruikt worden.
- Token moet verlopen na korte tijd.
- Alleen ingelogde mobiele gebruikers mogen een QR-login bevestigen.
- Voeg CSRF-bescherming toe waar relevant.
- Gebruik rate limiting op QR-login endpoints.
- Controleer statusovergangen strikt:
  pending → approved
  pending → expired
  approved → consumed
- Voorkom dat iemand een reeds approved token opnieuw kan gebruiken.
- Log verdachte of mislukte pogingen.
- Gebruik bestaande auth/session architectuur van de applicatie.
- Breek bestaande email/password login niet.

Technische opdracht:
1. Analyseer eerst de bestaande projectstructuur, auth-flow, routing, database en frontend stack.
2. Bepaal waar deze QR-login best geïntegreerd wordt.
3. Maak daarna een concreet implementatieplan.
4. Implementeer de nodige backend endpoints, database model/migration, services en frontend schermen.
5. Voeg tests toe voor:
   - token creatie
   - token expiry
   - scan zonder login
   - scan met login
   - goedkeuren
   - weigeren
   - dubbel gebruik
   - desktop login na approval
6. Voeg duidelijke comments toe op security-kritische plaatsen.
7. Gebruik bestaande coding style, naming conventions en architectuur van het project.

Gewenste endpoints, pas namen aan aan de bestaande conventies:
- POST /auth/qr/start
  Maakt QR-login sessie aan en geeft token + QR URL terug.

- GET /auth/qr/status/:token
  Geeft status terug voor desktop polling, of vervang dit door WebSocket/SSE indien het project dat al gebruikt.

- GET /auth/qr/scan?token=<token>
  Mobiele scanpagina. Vereist ingelogde user.

- POST /auth/qr/confirm
  Bevestigt login vanaf mobiel.

- POST /auth/qr/deny
  Weigert login vanaf mobiel.

- POST /auth/qr/consume
  Desktop consumeert approved token en krijgt normale authenticated session.

Database/model:
Maak een tabel/model vergelijkbaar met:

qr_login_sessions:
- id
- token_hash
- status
- user_id nullable
- browser_session_id nullable
- ip_address nullable
- user_agent nullable
- expires_at
- approved_at nullable
- consumed_at nullable
- created_at
- updated_at

Belangrijk:
Sla bij voorkeur niet de raw token op, maar een hash van de token.

Frontend:
- Voeg een “Login met QR-code” optie toe op de bestaande loginpagina.
- Toon QR-code en countdown.
- Toon status:
  - wachten op scan
  - gescand, wacht op bevestiging
  - goedgekeurd
  - verlopen
  - geweigerd
- Na succesvolle login redirect naar de normale account/dashboard pagina.
- Mobiele scanpagina moet duidelijk tonen welk apparaat probeert in te loggen.

Output:
- Werk rechtstreeks in de codebase.
- Pas bestaande conventies toe.
- Maak geen grote refactor buiten scope.
- Toon na implementatie:
  1. welke bestanden gewijzigd zijn
  2. welke endpoints toegevoegd zijn
  3. hoe de flow getest kan worden
  4. eventuele resterende aandachtspunten