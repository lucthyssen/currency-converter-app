# Currency Converter App

## Overzicht
Deze applicatie is een currency converter met admin dashboard voor het beheren van:
- Currencies
- Historical exchange rates
- IP whitelist

Functies:
- Inloggen met "Remember me"
- Rates importeren (per valuta of alle tegelijk) via dashboard of console
- IP whitelist voor toegangscontrole

## Vereisten
- PHP 8.2+
- Composer
- MariaDB 10+

## Installatie

1. Clone de repo
```bash
git clone https://github.com/yourusername/currency-converter-app.git
cd currency-converter-app
```
2. Kopieer `.env.example` naar `.env` en pas de database credentials aan
```bash
cp .env.example .env
```

3. Installeer dependencies
```bash
composer install
```

4. Maak de database aan en voer de migraties uit
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Fixtures inladen (optioneel, voor testdata)
```bash
php bin/console doctrine:fixtures:load
```

6. Start de Symfony server
```bash
symfony serve
```

## Importeren van exchange rates
Je kunt exchange rates importeren via het dashboard of via de command line:
```bash
php bin/console app:import-exchange-rates [baseCurrency]
```
- `baseCurrency` is optioneel, standaard is EUR. Hiermee worden de rates van de opgegeven base currency geïmporteerd. Zonder argument worden alle beschikbare rates geïmporteerd.

## Tijd besteed
- In totaal heb ik 4:30 uur besteed aan deze opdracht, inclusief het schrijven van deze readme en het inleveren van de opdracht