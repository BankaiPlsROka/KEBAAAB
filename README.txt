KEBAB404 — instrukcja uruchomienia

WYMAGANIA
- Windows z XAMPP (Apache, PHP, MariaDB/MySQL)
- Przeglądarka internetowa
- Edytor kodu (opcjonalnie, np. Visual Studio Code)

URUCHOMIENIE W XAMPP
1. Uruchom XAMPP Control Panel i włącz moduły Apache oraz MySQL.
2. Skopiuj cały folder KEBAB404 do C:\xampp\htdocs\KEBAB404.
   (Albo utwórz skrót/junction; adres aplikacji zależy od położenia w htdocs.)
3. Otwórz http://localhost/phpmyadmin/.
4. Wybierz Importuj, wskaż plik baza.sql z folderu projektu i zatwierdź.
   Import tworzy bazę kebab404, tabele, pięć produktów i trzy przykładowe zamówienia.
5. Otwórz http://localhost/KEBAB404/.

KONFIGURACJA BAZY
Domyślnie index.php łączy się z MySQL na 127.0.0.1, użytkownikiem root,
bez hasła — tak działa typowa lokalna instalacja XAMPP. Jeżeli ustawiono
inne dane, zmień zmienne $host, $dbUser oraz $dbPassword na początku index.php.
W PHP musi być włączone rozszerzenie pdo_mysql.

CO MOŻNA SPRAWDZIĆ
- Menu pobiera produkty z tabeli produkty.
- Formularz zapisuje klienta, produkt, ilość i sos; ilość musi wynosić 1–5.
- Cena jest wyliczana w PHP z aktualnej ceny produktu w bazie.
- Kalkulator JavaScript pokazuje cenę bez przeładowania strony.
- Panel zamówień pobiera dane przez JOIN i umożliwia zmianę NOWE na GOTOWE.
- W pliku zapytania.sql znajdują się wymagane zapytania SELECT, JOIN, ORDER BY,
  GROUP BY i funkcje agregujące.
- Ukryty przycisk „404” w prawym dolnym rogu wyświetla komunikat ERROR 418.

Pliki screeny/strona.png, formularz.png, zamowienia.png i baza.png należy
uzupełnić zrzutami ekranu po uruchomieniu aplikacji na komputerze. Dla
screeny/baza.png można użyć widoku tabel w phpMyAdmin.

© 2026 KEBAB.EXE — Powered by głodni programiści
