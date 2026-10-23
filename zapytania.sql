-- Wszystkie produkty, od najtańszego:
SELECT id, nazwa, cena
FROM produkty
ORDER BY cena ASC;

-- Zamówienia wraz z nazwami produktów:
SELECT z.id, z.klient, p.nazwa AS produkt, z.ilosc, z.sos, z.status,
       p.cena * z.ilosc AS laczna_cena, z.data_zamowienia
FROM zamowienia AS z
JOIN produkty AS p ON p.id = z.produkt_id
ORDER BY z.data_zamowienia DESC;

-- Liczba zamówień w poszczególnych statusach:
SELECT status, COUNT(*) AS liczba_zamowien
FROM zamowienia
GROUP BY status;

-- Łączna liczba sprzedanych sztuk każdego produktu:
SELECT p.id, p.nazwa, COALESCE(SUM(z.ilosc), 0) AS sprzedane_sztuki
FROM produkty AS p
LEFT JOIN zamowienia AS z ON z.produkt_id = p.id
GROUP BY p.id, p.nazwa
ORDER BY sprzedane_sztuki DESC, p.nazwa ASC;
