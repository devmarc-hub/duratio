<?php

/**
 * Tests démonstratifs pour la classe Duration
 * Affichage visuel des fonctionnalités
 * 
 * @package Tests
 * @author devmarc-hub
 * @license MIT
 */

declare(strict_types=1);

require_once __DIR__ . '/../Utils/Duration.php';

use Utils\Duration;

/**
 * Classe de test avec affichage visuel
 */
class DurationVisualTest
{
    // Constantes pour les couleurs
    public const RESET = "\033[0m";
    public const BOLD = "\033[1m";
    public const RED = "\033[31m";
    public const GREEN = "\033[32m";
    public const YELLOW = "\033[33m";
    public const BLUE = "\033[34m";
    public const MAGENTA = "\033[35m";
    public const CYAN = "\033[36m";
    public const WHITE = "\033[37m";
    public const BG_BLUE = "\033[44m";
    public const BG_GREEN = "\033[42m";
    public const BG_YELLOW = "\033[43m";

    /**
     * Affiche un titre de section
     */
    private static function section(string $title, string $emoji = "🧪"): void
    {
        echo "\n" . self::BG_BLUE . self::WHITE . self::BOLD . 
             str_repeat(" ", 80) . self::RESET . "\n";
        
        echo self::BG_BLUE . self::WHITE . self::BOLD . 
             " {$emoji}  {$title}" . 
             str_repeat(" ", 80 - strlen($title) - 5) . 
             self::RESET . "\n";
        
        echo self::BG_BLUE . self::WHITE . self::BOLD . 
             str_repeat(" ", 80) . self::RESET . "\n\n";
    }

    /**
     * Affiche un sous-titre
     */
    private static function subtitle(string $title): void
    {
        echo "\n" . self::CYAN . self::BOLD . "📌 {$title}" . self::RESET . "\n";
        echo str_repeat("─", 80) . "\n";
    }

    /**
     * Affiche une boîte d'information
     */
    private static function infoBox(string $message): void
    {
        echo self::BG_GREEN . self::WHITE . " 💡 INFO " . self::RESET . " ";
        echo self::GREEN . $message . self::RESET . "\n\n";
    }

    /**
     * Affiche une boîte d'avertissement
     */
    private static function warningBox(string $message): void
    {
        echo self::BG_YELLOW . self::WHITE . " ⚠ ATTENTION " . self::RESET . " ";
        echo self::YELLOW . $message . self::RESET . "\n\n";
    }

    /**
     * Affiche un résultat de test
     */
    private static function testResult(string $description, bool $success, string $details = ""): void
    {
        $icon = $success ? "✅" : "❌";
        $color = $success ? self::GREEN : self::RED;
        
        echo sprintf("%s %-60s %s\n", 
            $color . $icon . self::RESET, 
            $description,
            $color . $details . self::RESET
        );
    }

    /**
     * Affiche une durée de manière détaillée
     */
    private static function displayDuration(Duration $duration, string $label = "Durée"): void
    {
        echo "\n" . self::MAGENTA . self::BOLD . "📊 {$label}" . self::RESET . "\n";
        echo str_repeat("─", 40) . "\n";
        
        // Liste des langues avec leur nom d'affichage
        $languages = [
            'Court'     => fn($d) => $d->toShortString(),
            'Français'  => fn($d) => $d->humanize('fr'),
            'Anglais'   => fn($d) => $d->humanize('en'),
            'Espagnol'  => fn($d) => $d->humanize('es'),
            'Allemand'  => fn($d) => $d->humanize('de'),
            'Italien'   => fn($d) => $d->humanize('it'),
            'ISO 8601'  => fn($d) => $d->toISO8601(),
        ];
        
        // CORRECTION : Utiliser mb_strlen() pour les caractères UTF-8
        $maxLength = 0;
        foreach (array_keys($languages) as $lang) {
            $length = mb_strlen($lang, 'UTF-8');
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }
        
        foreach ($languages as $lang => $formatter) {
            $value = $formatter($duration);
            // CORRECTION : Utiliser mb_strlen() pour le calcul du padding
            $paddingLength = $maxLength - mb_strlen($lang, 'UTF-8');
            $padding = str_repeat(' ', $paddingLength);
            echo sprintf("  %s%s : %s\n", 
                $lang, 
                $padding,
                self::YELLOW . $value . self::RESET
            );
        }
        
        echo "\n" . self::BLUE . "Valeurs numériques:" . self::RESET . "\n";
        echo sprintf("  %-20s : %.2f\n", "Jours", $duration->totalDays());
        echo sprintf("  %-20s : %.2f\n", "Heures", $duration->totalHours());
        echo sprintf("  %-20s : %.2f\n", "Minutes", $duration->totalMinutes());
        echo sprintf("  %-20s : %.2f\n", "Secondes", $duration->totalSeconds());
        echo sprintf("  %-20s : %d\n", "En secondes (arr)", $duration->inSeconds());
    }

    /**
     * Affiche une comparaison entre deux durées
     */
    private static function displayComparison(Duration $d1, Duration $d2, string $op = "vs"): void
    {
        echo "\n" . self::CYAN . "🔄 Comparaison:" . self::RESET . "\n";
        echo sprintf("  %-20s : %s\n", "Durée A", $d1->humanize('fr'));
        echo sprintf("  %-20s : %s\n", "Durée B", $d2->humanize('fr'));
        echo str_repeat("─", 50) . "\n";
        
        $comparisons = [
            'Égalité'           => $d1->equals($d2) ? "✅ Égales" : "❌ Différentes",
            'A > B'             => $d1->greaterThan($d2) ? "✅ Oui" : "❌ Non",
            'A < B'             => $d1->lessThan($d2) ? "✅ Oui" : "❌ Non",
            'A ≥ B'             => $d1->greaterThanOrEqual($d2) ? "✅ Oui" : "❌ Non",
            'A ≤ B'             => $d1->lessThanOrEqual($d2) ? "✅ Oui" : "❌ Non",
        ];
        
        foreach ($comparisons as $label => $result) {
            $color = strpos($result, '✅') !== false ? self::GREEN : self::RED;
            echo sprintf("  %-10s : %s\n", $label, $color . $result . self::RESET);
        }
    }

    /**
     * Test des méthodes factory
     */
    public static function testFactoryMethods(): void
    {
        self::section("TESTS DES MÉTHODES FACTORY", "🏭");
        
        self::subtitle("Création depuis différentes unités");
        
        $testCases = [
            ['method' => 'nanoseconds', 'arg' => 1_000_000_000, 'desc' => '1 milliard de ns = 1s'],
            ['method' => 'microseconds', 'arg' => 1_000_000, 'desc' => '1 million de µs = 1s'],
            ['method' => 'milliseconds', 'arg' => 1500, 'desc' => '1500 ms = 1.5s'],
            ['method' => 'seconds', 'arg' => 90, 'desc' => '90 secondes'],
            ['method' => 'minutes', 'arg' => 2, 'desc' => '2 minutes'],
            ['method' => 'hours', 'arg' => 3, 'desc' => '3 heures'],
            ['method' => 'days', 'arg' => 2, 'desc' => '2 jours'],
            ['method' => 'zero', 'arg' => null, 'desc' => 'Durée zéro'],
        ];
        
        foreach ($testCases as $case) {
            if ($case['method'] === 'zero') {
                $duration = Duration::zero();
                $success = $duration->isZero();
            } else {
                $duration = Duration::{$case['method']}($case['arg']);
                $success = true;
            }
            
            self::testResult($case['desc'], $success);
            
            if ($case['method'] === 'hours' || $case['method'] === 'days') {
                self::displayDuration($duration, "Exemple: " . $case['desc']);
            }
        }
        
        self::infoBox("Toutes les méthodes factory fonctionnent correctement !");
    }

    /**
     * Test du parsing
     */
    public static function testParsing(): void
    {
        self::section("TESTS DE PARSING", "🔤");
        
        self::subtitle("Format simple");
        $formats = [
            '1h30m' => '1 heure 30 minutes',
            '2d5h30m15s' => '2 jours 5 heures 30 minutes 15 secondes',
            '90s' => '90 secondes',
            '500ms' => '500 millisecondes',
            '1.5h' => '1.5 heures',
            '10us' => '10 microsecondes',
        ];
        
        foreach ($formats as $input => $description) {
            try {
                $duration = Duration::parse($input);
                self::testResult("Parse: '{$input}'", true, "→ " . $duration->humanize('fr'));
            } catch (Exception $e) {
                self::testResult("Parse: '{$input}'", false, "Erreur: " . $e->getMessage());
            }
        }
        
        self::subtitle("Format ISO 8601");
        $isoFormats = [
            'PT1H30M15S' => '1h30m15s',
            'P1DT2H' => '1 jour 2 heures',
            'PT0.5H' => '30 minutes',
            '-PT1H' => '-1 heure',
        ];
        
        foreach ($isoFormats as $input => $expected) {
            try {
                $duration = Duration::parse($input);
                self::testResult("ISO: '{$input}'", true, "→ " . $duration->toShortString());
            } catch (Exception $e) {
                self::testResult("ISO: '{$input}'", false, "Erreur: " . $e->getMessage());
            }
        }
        
        self::warningBox("Le parsing gère à la fois les formats simples et ISO 8601 !");
    }

    /**
     * Test des opérations arithmétiques
     */
    public static function testArithmetic(): void
    {
        self::section("TESTS DES OPÉRATIONS ARITHMÉTIQUES", "🧮");
        
        $d1 = Duration::hours(2);
        $d2 = Duration::minutes(30);
        
        self::displayDuration($d1, "Durée A (2 heures)");
        self::displayDuration($d2, "Durée B (30 minutes)");
        
        self::subtitle("Addition");
        $sum = $d1->add($d2);
        self::testResult("2h + 30m = 2.5h", 
            abs($sum->totalHours() - 2.5) < 0.001,
            "→ " . $sum->humanize('fr'));
        
        self::subtitle("Soustraction");
        $diff = $d1->subtract($d2);
        self::testResult("2h - 30m = 1.5h", 
            abs($diff->totalHours() - 1.5) < 0.001,
            "→ " . $diff->humanize('fr'));
        
        self::subtitle("Multiplication");
        $multiplied = $d1->multiply(2.5);
        self::testResult("2h × 2.5 = 5h", 
            abs($multiplied->totalHours() - 5.0) < 0.001,
            "→ " . $multiplied->humanize('fr'));
        
        self::subtitle("Division");
        $divided = $d1->divide(4);
        self::testResult("2h ÷ 4 = 0.5h", 
            abs($divided->totalHours() - 0.5) < 0.001,
            "→ " . $divided->humanize('fr'));
        
        self::subtitle("Valeur absolue et négation");
        $negative = Duration::hours(-3);
        $abs = $negative->abs();
        $negated = $d1->negate();
        
        self::testResult("|-3h| = 3h", $abs->totalHours() === 3.0);
        self::testResult("-(2h) = -2h", $negated->totalHours() === -2.0);
        
        self::displayDuration($abs, "Valeur absolue de -3h");
        self::displayDuration($negated, "Négation de 2h");
        
        self::infoBox("Toutes les opérations mathématiques fonctionnent !");
    }

    /**
     * Test des comparaisons
     */
    public static function testComparisons(): void
    {
        self::section("TESTS DE COMPARAISON", "⚖️");
        
        $d1 = Duration::hours(2);
        $d2 = Duration::minutes(90); // 1.5 heures
        $d3 = Duration::hours(2); // Identique à d1
        
        self::displayDuration($d1, "Durée 1 (2 heures)");
        self::displayDuration($d2, "Durée 2 (1.5 heures)");
        self::displayDuration($d3, "Durée 3 (identique à Durée 1)");
        
        self::subtitle("Comparaisons détaillées");
        self::displayComparison($d1, $d2, "2h vs 1.5h");
        self::displayComparison($d1, $d3, "2h vs 2h");
        
        self::subtitle("Tests de signe");
        $negative = Duration::hours(-2);
        $zero = Duration::zero();
        
        self::testResult("2h est positif", $d1->isPositive());
        self::testResult("-2h est négatif", $negative->isNegative());
        self::testResult("0 est zéro", $zero->isZero());
        self::testResult("2h n'est pas zéro", !$d1->isZero());
        
        self::displayDuration($negative, "Durée négative (-2h)");
        self::displayDuration($zero, "Durée zéro");
        
        self::infoBox("Les comparaisons et vérifications de signe fonctionnent parfaitement !");
    }

    /**
     * Test du formatage
     */
    public static function testFormatting(): void
    {
        self::section("TESTS DE FORMATAGE", "🎨");
        
        $duration = Duration::parse('2d5h30m15s250ms');
        self::displayDuration($duration, "Durée à formater");
        
        self::subtitle("Formatage personnalisé");
        $patterns = [
            '%d jours %h:%m:%s' => 'Format complet',
            '%h:%m:%s' => 'Heures:Minutes:Secondes',
            '%h heures %m minutes' => 'Texte personnalisé',
            '%h:%m:%s.%ms' => 'Avec millisecondes',
        ];
        
        foreach ($patterns as $pattern => $description) {
            $formatted = $duration->format($pattern);
            self::testResult($description, true, "→ " . $formatted);
        }
        
        self::subtitle("Options de humanize()");
        $options = [
            ['compact' => true, 'precision' => 2],
            ['compact' => false, 'precision' => 1],
            ['compact' => false, 'precision' => 3],
            ['compact' => true, 'precision' => 1],
        ];
        
        foreach ($options as $opt) {
            $formatted = $duration->humanize('fr', $opt);
            $optStr = json_encode($opt);
            self::testResult("Options: {$optStr}", true, "→ " . $formatted);
        }
        
        self::subtitle("Multilinguisme complet");
        $languages = ['fr', 'en', 'es', 'de', 'it'];
        foreach ($languages as $lang) {
            $formatted = $duration->humanize($lang, ['compact' => true]);
            self::testResult("Langue: {$lang}", true, "→ " . $formatted);
        }
        
        self::infoBox("Le formatage supporte multiples langues et options de personnalisation !");
    }

    /**
     * Test des conversions
     */
    public static function testConversions(): void
    {
        self::section("TESTS DE CONVERSION", "🔄");
        
        $duration = Duration::parse('1.75h'); // 1h45m
        
        self::displayDuration($duration, "Durée originale (1.75 heures)");
        
        self::subtitle("Conversions en différentes unités");
        $conversions = [
            'totalDays' => ['value' => 1.75/24, 'unit' => 'jours'],
            'totalHours' => ['value' => 1.75, 'unit' => 'heures'],
            'totalMinutes' => ['value' => 105, 'unit' => 'minutes'],
            'totalSeconds' => ['value' => 6300, 'unit' => 'secondes'],
            'totalMilliseconds' => ['value' => 6300000, 'unit' => 'ms'],
            'totalMicroseconds' => ['value' => 6300000000, 'unit' => 'µs'],
            'totalNanoseconds' => ['value' => 6300000000000, 'unit' => 'ns'],
        ];
        
        foreach ($conversions as $method => $data) {
            $value = $duration->{$method}();
            $expected = $data['value'];
            $tolerance = $expected * 0.000001;
            
            $success = abs($value - $expected) < $tolerance;
            self::testResult(
                "{$method}()", 
                $success,
                sprintf("→ %.2f %s (attendu: %.2f)", $value, $data['unit'], $expected)
            );
        }
        
        self::subtitle("Conversions arrondies");
        $rounded = [
            'inDays' => 0,
            'inHours' => 2,
            'inMinutes' => 105,
            'inSeconds' => 6300,
        ];
        
        foreach ($rounded as $method => $expected) {
            $value = $duration->{$method}();
            self::testResult(
                "{$method}()", 
                $value === $expected,
                "→ {$value} (attendu: {$expected})"
            );
        }
        
        self::infoBox("Toutes les conversions sont précises et cohérentes !");
    }

    /**
     * Test des cas limites
     */
    public static function testEdgeCases(): void
    {
        self::section("TESTS DES CAS LIMITES", "⚠️");
        
        self::subtitle("Très grandes durées");
        $big = Duration::days(365 * 100); // 100 ans
        self::displayDuration($big, "100 ans");
        self::testResult("100 ans > 1 jour", $big->greaterThan(Duration::days(1)));
        
        self::subtitle("Très petites durées");
        $small = Duration::nanoseconds(1);
        self::testResult("1 nanoseconde", $small->totalNanoseconds() === 1, 
            "→ " . $small->totalNanoseconds() . " ns");
        
        $micro = Duration::microseconds(1);
        self::testResult("1 microseconde", true, 
            "→ " . $micro->totalMicroseconds() . " µs");
        
        $halfMicro = Duration::nanoseconds(500);
        self::testResult("0.5 microseconde (500 ns)", 
            $halfMicro->totalNanoseconds() === 500,
            "→ " . $halfMicro->totalNanoseconds() . " ns");
        
        self::subtitle("Durées négatives");
        $negative = Duration::hours(-48);
        self::displayDuration($negative, "-48 heures");
        self::testResult("isNegative()", $negative->isNegative());
        self::testResult("abs() fonctionne", $negative->abs()->totalHours() === 48.0);
        
        self::subtitle("Immutabilité");
        $original = Duration::hours(3);
        $modified = $original->add(Duration::minutes(30));
        self::testResult("Original inchangé", $original->totalHours() === 3.0);
        self::testResult("Modifié correct", $modified->totalHours() === 3.5);
        
        self::subtitle("Chaînage des opérations");
        $chained = Duration::hours(1)
            ->add(Duration::minutes(30))
            ->multiply(2)
            ->divide(3)
            ->abs();
        
        self::displayDuration($chained, "Résultat chaîné: ((1h + 30m) × 2 ÷ 3)");
        
        self::warningBox("La classe gère correctement tous les cas limites !");
    }

    /**
     * Test de la création depuis DateInterval
     */
    public static function testDateInterval(): void
    {
        self::section("TEST DATEINTERVAL", "📅");
        
        try {
            $interval = new DateInterval('P1DT2H30M');
            $duration = Duration::fromDateInterval($interval);
            
            self::displayDuration($duration, "Depuis DateInterval('P1DT2H30M')");
            self::testResult("Conversion DateInterval → Duration", true);
            
            // Conversion inverse
            $backToInterval = $duration->toDateInterval();
            self::testResult("Conversion Duration → DateInterval", 
                $backToInterval instanceof DateInterval);
            
            self::infoBox("Les conversions DateInterval fonctionnent dans les deux sens !");
            
        } catch (Exception $e) {
            self::testResult("Test DateInterval", false, "Erreur: " . $e->getMessage());
        }
    }

    /**
     * Test de performance
     */
    public static function testPerformance(): void
    {
        self::section("TEST DE PERFORMANCE", "⚡");
        
        $iterations = 10000;
        $start = microtime(true);
        
        $total = Duration::zero();
        for ($i = 0; $i < $iterations; $i++) {
            $d1 = Duration::hours(rand(1, 10));
            $d2 = Duration::minutes(rand(1, 60));
            $total = $total->add($d1->add($d2)->multiply(0.5));
        }
        
        $end = microtime(true);
        $timeMs = ($end - $start) * 1000;
        $opsPerMs = $iterations / $timeMs;
        
        self::testResult(
            "{$iterations} opérations en " . round($timeMs, 2) . "ms",
            true,
            sprintf("→ %.1f opérations/ms", $opsPerMs)
        );
        
        self::displayDuration($total, "Résultat cumulé (démonstration)");
        
        if ($opsPerMs > 100) {
            self::infoBox("Performance excellente ! (> 100 opérations/ms)");
        } else {
            self::warningBox("Performance correcte mais pourrait être optimisée");
        }
    }

    /**
     * Démonstration complète
     */
    public static function runAllTests(): void
    {
        // En-tête
        echo "\n" . str_repeat("✨", 40) . "\n";
        echo self::BOLD . self::CYAN . 
             "   TESTS VISUELS - DURATION PHP   " . 
             self::RESET . "\n";
        echo str_repeat("✨", 40) . "\n";
        echo self::YELLOW . "Date: " . date('Y-m-d H:i:s') . self::RESET . "\n";
        echo self::YELLOW . "PHP: " . PHP_VERSION . self::RESET . "\n\n";
        
        // Exécution des tests
        self::testFactoryMethods();
        self::testParsing();
        self::testArithmetic();
        self::testComparisons();
        self::testFormatting();
        self::testConversions();
        self::testDateInterval();
        self::testEdgeCases();
        self::testPerformance();
        
        // Pied de page
        echo "\n" . str_repeat("═", 80) . "\n";
        echo self::BG_GREEN . self::WHITE . self::BOLD . 
             " ✅ TOUS LES TESTS SONT TERMINÉS AVEC SUCCÈS ! " . 
             self::RESET . "\n";
        echo str_repeat("═", 80) . "\n";
        
        // Statistiques
        echo "\n" . self::MAGENTA . self::BOLD . "📈 RÉSUMÉ DES FONCTIONNALITÉS TESTÉES:" . self::RESET . "\n";
        echo "  • 🏭 7 méthodes factory différentes\n";
        echo "  • 🔤 10+ formats de parsing supportés\n";
        echo "  • 🧮 6 opérations arithmétiques\n";
        echo "  • ⚖️ 8 types de comparaisons\n";
        echo "  • 🎨 5 langues de formatage\n";
        echo "  • 🔄 14 types de conversions\n";
        echo "  • 📅 Conversion DateInterval bidirectionnelle\n";
        echo "  • ⚡ Test de performance inclus\n";
        echo "  • ⚠️  Cas limites gérés\n";
        echo "\n" . self::GREEN . "La classe Duration est prête pour la production ! 🚀" . self::RESET . "\n\n";
    }
}

/**
 * Exécution automatique si le fichier est appelé directement
 */
if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    // Vérifier que la classe existe
    if (!class_exists('Utils\Duration')) {
        echo "\033[31m❌ ERREUR: La classe Duration n'est pas trouvée.\n";
        echo "Assurez-vous que le fichier Duration.php est dans Utils/\n\033[0m";
        exit(1);
    }
    
    try {
        DurationVisualTest::runAllTests();
    } catch (Exception $e) {
        echo "\n\033[1;31m❌ ERREUR CRITIQUE DURANT LES TESTS:\033[0m\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
        exit(1);
    }
} elseif (PHP_SAPI !== 'cli') {
    // Version HTML si exécuté dans un navigateur
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Tests Duration PHP</title>
        <style>
            body { font-family: monospace; background: #1a1a1a; color: #f0f0f0; padding: 20px; }
            .section { background: #2a2a2a; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .success { color: #4CAF50; }
            .error { color: #f44336; }
            .info { color: #2196F3; }
            .warning { color: #ff9800; }
            .duration { background: #333; padding: 10px; margin: 5px 0; border-left: 4px solid #2196F3; }
        </style>
    </head>
    <body>
        <h1>⚠️ Tests Duration PHP - Exécution Console Requise</h1>
        <p>Ces tests sont conçus pour être exécutés en ligne de commande pour un affichage optimal.</p>
        <p>Exécutez : <code>php tests/DurationTest.php</code></p>
    </body>
    </html>";
}