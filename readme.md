# Projekt: URL Shortener

## Instalacja

1. Sklonuj repozytorium:

   ```bash
   git clone https://github.com/juchej/SI-Projekt.git
   cd SI-Projekt

2. Wejdź do katalogu app:

   ```bash
   cd app
   ```
3. Zainstaluj zależności PHP:

   ```bash
   composer install
   ```
   
4. Skonfiguruj plik `.env`, ustawiając dane do bazy danych i inne ustawienia:

   ```bash
   DATABASE_URL="mysql://nazwa_uzytkownika:haslo@host:port/nazwa_bazy?serverVersion=8.0"
   ```
   
5. Utwórz bazę danych, wykonaj migracje i załaduj dane przykładowe:

   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load
   ```
