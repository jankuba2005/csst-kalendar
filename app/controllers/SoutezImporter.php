<?php

namespace controllers;


class SoutezImporter {

    public function import(\Base $base) {
        $year = date('Y');
        $csvUrl = 'https://docs.google.com/spreadsheets/d/16P77oSBNgA5FARpZTaC3Jyv5FqyEJKxH/export?format=csv';
        $importCount = 0;
        $errorCount = 0;
        $duplicateCount = 0; // Počítadlo duplicitních záznamů

        try {
            $csvData = file_get_contents($csvUrl);
            if ($csvData === false) {
                // Tiché logování chyby
                if ($base->exists('LOGGER')) {
                    $base->get('LOGGER')->error("Nepodařilo se získat CSV data");
                }
                throw new \Exception("Nepodařilo se získat CSV data");
            }

            $rows = array_map('str_getcsv', explode("\n", $csvData));
            array_shift($rows); // Vynecháme hlavičku

            $disciplineMap = [
                'trojboj' => 'trojboj',
                'benčpres' => 'benchpress',
                'mrtvý tah' => 'mrtvy tah'
            ];

            foreach ($rows as $row) {
                if (count($row) < 3) continue;

                try {
                    // Parsování data s lepším ošetřením chyb
                    $datePart = explode(';', $row[0])[0];
                    $dateStr = trim(explode('-', $datePart)[0], " .");
                    $date = \DateTime::createFromFormat('d.m', $dateStr);

                    if ($date === false) {
                        // Pokud první formát selže, zkusíme alternativní formáty
                        $date = \DateTime::createFromFormat('j.n', $dateStr);
                        if ($date === false) {
                            throw new \Exception("Nelze rozpoznat formát data: '$dateStr'");
                        }
                    }

                    $date->setDate($year, $date->format('m'), $date->format('d'));

                    // Parsování disciplín
                    $discipliny = [];
                    if (strpos($row[0], ';') !== false) {
                        $disciplinyRaw = explode(';', $row[0])[1];
                        foreach (explode(',', $disciplinyRaw) as $d) {
                            $key = trim(mb_strtolower($d));
                            if (isset($disciplineMap[$key])) {
                                $discipliny[] = $disciplineMap[$key];
                            }
                        }
                    }

                    // Parsování kategorie a vybavení
                    preg_match('/(.*?)\s*\((\w+)\)$/', $row[1], $matches);
                    $vybaveni = isset($matches[2]) && strtoupper($matches[2]) === 'EQ' ? 'equipped' : 'raw';

                    $kategorie = $this->detectKategorie(isset($matches[1]) ? $matches[1] : '');
                    $nazev = trim(explode('(', $row[1])[0]);

                    // Ukládám celý obsah jako pořadatele, místo nastavuji jako prázdné
                    $poradatel = trim($row[2]);
                    $misto = '';  // Prázdné místo - není uvedeno

                    // Kontrola duplicitního záznamu
                    if ($this->isDuplicate($nazev, $date->format('Y-m-d'), $poradatel)) {
                        $duplicateCount++;
                        continue;
                    }

                    // Uložení záznamu
                    $soutez = new \models\soutez();
                    $soutez->nazev = $nazev;
                    $soutez->datum = $date->format('Y-m-d');
                    $soutez->misto = $misto;
                    $soutez->poradatel = $poradatel;
                    $soutez->vybaveni = $vybaveni;
                    $soutez->kategorie = $kategorie;
                    $soutez->discipliny = $discipliny;

                    $soutez->save();
                    $importCount++;

                } catch (\Exception $e) {
                    // Pouze tiché logování chyby, nezobrazujeme uživateli
                    $errorCount++;
                    if ($base->exists('LOGGER')) {
                        $base->get('LOGGER')->error("Chyba při importu řádku: " . $e->getMessage());
                    }
                }
            }

            // Flash zpráva o úspěchu - s informací o přeskočených duplicitách
            $message = "Import dokončen. Importováno {$importCount} soutěží";
            if ($duplicateCount > 0) {
                $message .= ", přeskočeno {$duplicateCount} duplicitních záznamů";
            }
            \Flash::instance()->addMessage($message, 'success');

        } catch (\Exception $e) {
            // Tiché logování hlavní chyby
            if ($base->exists('LOGGER')) {
                $base->get('LOGGER')->error("Hlavní chyba importu: " . $e->getMessage());
            }

            // Obecná zpráva bez detailů chyby
            \Flash::instance()->addMessage("Import nemohl být dokončen.", 'info');
        }

        // Přesměrování zpět na seznam soutěží
        $base->reroute('/');
    }

    /**
     * Zkontroluje zda soutěž již existuje v databázi
     *
     * @param string $nazev Název soutěže
     * @param string $datum Datum soutěže ve formátu Y-m-d
     * @param string $poradatel Pořadatel soutěže
     * @return bool True pokud duplicitní záznam existuje
     */
    private function isDuplicate($nazev, $datum, $poradatel) {
        $soutez = new \models\soutez();
        $existujici = $soutez->find([
            'datum = ? AND nazev = ?',
            $datum, $nazev
        ]);

        // Check if $existujici is countable before using count()
        if (is_array($existujici) || $existujici instanceof \Countable) {
            return count($existujici) > 0;
        }

        // If find() returned false or another non-countable value
        return false;
    }

    private function detectKategorie($text) {
        $keywords = [
            'jednotlivci' => 'jednotlivci',
            'družstva' => 'druzstva',
            'univerzitní' => 'univerzitni'
        ];

        foreach ($keywords as $key => $value) {
            if (stripos($text, $key) !== false) {
                return $value;
            }
        }
        return 'jednotlivci'; // Výchozí hodnota
    }

}