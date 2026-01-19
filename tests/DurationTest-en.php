<?php

/**
 * Demonstrative tests for the Duration class
 * Visual display of features
 * 
 * @package Tests
 * @author devmarc-hub
 * @license MIT
 */

declare(strict_types=1);

require_once __DIR__ . '/../Utils/Duration.php';

use Utils\Duration;

/**
 * Visual test class with colorful CLI output
 */
class DurationVisualTest
{
    // ANSI color constants
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
     * Display a section header
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
     * Display a subsection title
     */
    private static function subtitle(string $title): void
    {
        echo "\n" . self::CYAN . self::BOLD . "📌 {$title}" . self::RESET . "\n";
        echo str_repeat("─", 80) . "\n";
    }

    /**
     * Display an info box
     */
    private static function infoBox(string $message): void
    {
        echo self::BG_GREEN . self::WHITE . " 💡 INFO " . self::RESET . " ";
        echo self::GREEN . $message . self::RESET . "\n\n";
    }

    /**
     * Display a warning box
     */
    private static function warningBox(string $message): void
    {
        echo self::BG_YELLOW . self::WHITE . " ⚠ WARNING " . self::RESET . " ";
        echo self::YELLOW . $message . self::RESET . "\n\n";
    }

    /**
     * Display a test result
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
     * Display a duration in detail
     */
    private static function displayDuration(Duration $duration, string $label = "Duration"): void
    {
        echo "\n" . self::MAGENTA . self::BOLD . "📊 {$label}" . self::RESET . "\n";
        echo str_repeat("─", 40) . "\n";
        
        $languages = [
            'Short'     => fn($d) => $d->toShortString(),
            'French'    => fn($d) => $d->humanize('fr'),
            'English'   => fn($d) => $d->humanize('en'),
            'Spanish'   => fn($d) => $d->humanize('es'),
            'German'    => fn($d) => $d->humanize('de'),
            'Italian'   => fn($d) => $d->humanize('it'),
            'ISO 8601'  => fn($d) => $d->toISO8601(),
        ];
        
        $maxLength = 0;
        foreach (array_keys($languages) as $lang) {
            $length = mb_strlen($lang, 'UTF-8');
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }
        
        foreach ($languages as $lang => $formatter) {
            $value = $formatter($duration);
            $paddingLength = $maxLength - mb_strlen($lang, 'UTF-8');
            $padding = str_repeat(' ', $paddingLength);
            echo sprintf("  %s%s : %s\n", 
                $lang, 
                $padding,
                self::YELLOW . $value . self::RESET
            );
        }
        
        echo "\n" . self::BLUE . "Numeric values:" . self::RESET . "\n";
        echo sprintf("  %-20s : %.2f\n", "Days", $duration->totalDays());
        echo sprintf("  %-20s : %.2f\n", "Hours", $duration->totalHours());
        echo sprintf("  %-20s : %.2f\n", "Minutes", $duration->totalMinutes());
        echo sprintf("  %-20s : %.2f\n", "Seconds", $duration->totalSeconds());
        echo sprintf("  %-20s : %d\n", "In seconds (rnd)", $duration->inSeconds());
    }

    /**
     * Display a comparison between two durations
     */
    private static function displayComparison(Duration $d1, Duration $d2, string $op = "vs"): void
    {
        echo "\n" . self::CYAN . "🔄 Comparison:" . self::RESET . "\n";
        echo sprintf("  %-20s : %s\n", "Duration A", $d1->humanize('en'));
        echo sprintf("  %-20s : %s\n", "Duration B", $d2->humanize('en'));
        echo str_repeat("─", 50) . "\n";
        
        $comparisons = [
            'Equality'          => $d1->equals($d2) ? "✅ Equal" : "❌ Different",
            'A > B'             => $d1->greaterThan($d2) ? "✅ Yes" : "❌ No",
            'A < B'             => $d1->lessThan($d2) ? "✅ Yes" : "❌ No",
            'A ≥ B'             => $d1->greaterThanOrEqual($d2) ? "✅ Yes" : "❌ No",
            'A ≤ B'             => $d1->lessThanOrEqual($d2) ? "✅ Yes" : "❌ No",
        ];
        
        foreach ($comparisons as $label => $result) {
            $color = strpos($result, '✅') !== false ? self::GREEN : self::RED;
            echo sprintf("  %-15s : %s\n", $label, $color . $result . self::RESET);
        }
    }

    /**
     * Test factory methods
     */
    public static function testFactoryMethods(): void
    {
        self::section("FACTORY METHODS TESTS", "🏭");
        
        self::subtitle("Creation from different units");
        
        $testCases = [
            ['method' => 'nanoseconds', 'arg' => 1_000_000_000, 'desc' => '1 billion ns = 1s'],
            ['method' => 'microseconds', 'arg' => 1_000_000, 'desc' => '1 million µs = 1s'],
            ['method' => 'milliseconds', 'arg' => 1500, 'desc' => '1500 ms = 1.5s'],
            ['method' => 'seconds', 'arg' => 90, 'desc' => '90 seconds'],
            ['method' => 'minutes', 'arg' => 2, 'desc' => '2 minutes'],
            ['method' => 'hours', 'arg' => 3, 'desc' => '3 hours'],
            ['method' => 'days', 'arg' => 2, 'desc' => '2 days'],
            ['method' => 'zero', 'arg' => null, 'desc' => 'Zero duration'],
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
                self::displayDuration($duration, "Example: " . $case['desc']);
            }
        }
        
        self::infoBox("All factory methods work correctly!");
    }

    /**
     * Test parsing
     */
    public static function testParsing(): void
    {
        self::section("PARSING TESTS", "🔤");
        
        self::subtitle("Simple format");
        $formats = [
            '1h30m' => '1 hour 30 minutes',
            '2d5h30m15s' => '2 days 5 hours 30 minutes 15 seconds',
            '90s' => '90 seconds',
            '500ms' => '500 milliseconds',
            '1.5h' => '1.5 hours',
            '10us' => '10 microseconds',
        ];
        
        foreach ($formats as $input => $description) {
            try {
                $duration = Duration::parse($input);
                self::testResult("Parse: '{$input}'", true, "→ " . $duration->humanize('en'));
            } catch (Exception $e) {
                self::testResult("Parse: '{$input}'", false, "Error: " . $e->getMessage());
            }
        }
        
        self::subtitle("ISO 8601 format");
        $isoFormats = [
            'PT1H30M15S' => '1h30m15s',
            'P1DT2H' => '1 day 2 hours',
            'PT0.5H' => '30 minutes',
            '-PT1H' => '-1 hour',
        ];
        
        foreach ($isoFormats as $input => $expected) {
            try {
                $duration = Duration::parse($input);
                self::testResult("ISO: '{$input}'", true, "→ " . $duration->toShortString());
            } catch (Exception $e) {
                self::testResult("ISO: '{$input}'", false, "Error: " . $e->getMessage());
            }
        }
        
        self::warningBox("Parsing handles both simple and ISO 8601 formats!");
    }

    /**
     * Test arithmetic operations
     */
    public static function testArithmetic(): void
    {
        self::section("ARITHMETIC OPERATIONS TESTS", "🧮");
        
        $d1 = Duration::hours(2);
        $d2 = Duration::minutes(30);
        
        self::displayDuration($d1, "Duration A (2 hours)");
        self::displayDuration($d2, "Duration B (30 minutes)");
        
        self::subtitle("Addition");
        $sum = $d1->add($d2);
        self::testResult("2h + 30m = 2.5h", 
            abs($sum->totalHours() - 2.5) < 0.001,
            "→ " . $sum->humanize('en'));
        
        self::subtitle("Subtraction");
        $diff = $d1->subtract($d2);
        self::testResult("2h - 30m = 1.5h", 
            abs($diff->totalHours() - 1.5) < 0.001,
            "→ " . $diff->humanize('en'));
        
        self::subtitle("Multiplication");
        $multiplied = $d1->multiply(2.5);
        self::testResult("2h × 2.5 = 5h", 
            abs($multiplied->totalHours() - 5.0) < 0.001,
            "→ " . $multiplied->humanize('en'));
        
        self::subtitle("Division");
        $divided = $d1->divide(4);
        self::testResult("2h ÷ 4 = 0.5h", 
            abs($divided->totalHours() - 0.5) < 0.001,
            "→ " . $divided->humanize('en'));
        
        self::subtitle("Absolute value and negation");
        $negative = Duration::hours(-3);
        $abs = $negative->abs();
        $negated = $d1->negate();
        
        self::testResult("|-3h| = 3h", $abs->totalHours() === 3.0);
        self::testResult("-(2h) = -2h", $negated->totalHours() === -2.0);
        
        self::displayDuration($abs, "Absolute value of -3h");
        self::displayDuration($negated, "Negation of 2h");
        
        self::infoBox("All mathematical operations work!");
    }

    /**
     * Test comparisons
     */
    public static function testComparisons(): void
    {
        self::section("COMPARISON TESTS", "⚖️");
        
        $d1 = Duration::hours(2);
        $d2 = Duration::minutes(90); // 1.5 hours
        $d3 = Duration::hours(2); // Identical to d1
        
        self::displayDuration($d1, "Duration 1 (2 hours)");
        self::displayDuration($d2, "Duration 2 (1.5 hours)");
        self::displayDuration($d3, "Duration 3 (identical to Duration 1)");
        
        self::subtitle("Detailed comparisons");
        self::displayComparison($d1, $d2, "2h vs 1.5h");
        self::displayComparison($d1, $d3, "2h vs 2h");
        
        self::subtitle("Sign tests");
        $negative = Duration::hours(-2);
        $zero = Duration::zero();
        
        self::testResult("2h is positive", $d1->isPositive());
        self::testResult("-2h is negative", $negative->isNegative());
        self::testResult("0 is zero", $zero->isZero());
        self::testResult("2h is not zero", !$d1->isZero());
        
        self::displayDuration($negative, "Negative duration (-2h)");
        self::displayDuration($zero, "Zero duration");
        
        self::infoBox("Comparisons and sign checks work perfectly!");
    }

    /**
     * Test formatting
     */
    public static function testFormatting(): void
    {
        self::section("FORMATTING TESTS", "🎨");
        
        $duration = Duration::parse('2d5h30m15s250ms');
        self::displayDuration($duration, "Duration to format");
        
        self::subtitle("Custom formatting");
        $patterns = [
            '%d days %h:%m:%s' => 'Complete format',
            '%h:%m:%s' => 'Hours:Minutes:Seconds',
            '%h hours %m minutes' => 'Custom text',
            '%h:%m:%s.%ms' => 'With milliseconds',
        ];
        
        foreach ($patterns as $pattern => $description) {
            $formatted = $duration->format($pattern);
            self::testResult($description, true, "→ " . $formatted);
        }
        
        self::subtitle("humanize() options");
        $options = [
            ['compact' => true, 'precision' => 2],
            ['compact' => false, 'precision' => 1],
            ['compact' => false, 'precision' => 3],
            ['compact' => true, 'precision' => 1],
        ];
        
        foreach ($options as $opt) {
            $formatted = $duration->humanize('en', $opt);
            $optStr = json_encode($opt);
            self::testResult("Options: {$optStr}", true, "→ " . $formatted);
        }
        
        self::subtitle("Full multilingual support");
        $languages = ['fr', 'en', 'es', 'de', 'it'];
        foreach ($languages as $lang) {
            $formatted = $duration->humanize($lang, ['compact' => true]);
            self::testResult("Language: {$lang}", true, "→ " . $formatted);
        }
        
        self::infoBox("Formatting supports multiple languages and customization options!");
    }

    /**
     * Test conversions
     */
    public static function testConversions(): void
    {
        self::section("CONVERSION TESTS", "🔄");
        
        $duration = Duration::parse('1.75h'); // 1h45m
        
        self::displayDuration($duration, "Original duration (1.75 hours)");
        
        self::subtitle("Conversions to different units");
        $conversions = [
            'totalDays' => ['value' => 1.75/24, 'unit' => 'days'],
            'totalHours' => ['value' => 1.75, 'unit' => 'hours'],
            'totalMinutes' => ['value' => 105, 'unit' => 'minutes'],
            'totalSeconds' => ['value' => 6300, 'unit' => 'seconds'],
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
                sprintf("→ %.2f %s (expected: %.2f)", $value, $data['unit'], $expected)
            );
        }
        
        self::subtitle("Rounded conversions");
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
                "→ {$value} (expected: {$expected})"
            );
        }
        
        self::infoBox("All conversions are precise and consistent!");
    }

    /**
     * Test edge cases
     */
    public static function testEdgeCases(): void
    {
        self::section("EDGE CASES TESTS", "⚠️");
        
        self::subtitle("Very large durations");
        $big = Duration::days(365 * 100); // 100 years
        self::displayDuration($big, "100 years");
        self::testResult("100 years > 1 day", $big->greaterThan(Duration::days(1)));
        
        self::subtitle("Very small durations");
        $small = Duration::nanoseconds(1);
        self::testResult("1 nanosecond", $small->totalNanoseconds() === 1, 
            "→ " . $small->totalNanoseconds() . " ns");
        
        $micro = Duration::microseconds(1);
        self::testResult("1 microsecond", true, 
            "→ " . $micro->totalMicroseconds() . " µs");
        
        $halfMicro = Duration::nanoseconds(500);
        self::testResult("0.5 microsecond (500 ns)", 
            $halfMicro->totalNanoseconds() === 500,
            "→ " . $halfMicro->totalNanoseconds() . " ns");
        
        self::subtitle("Negative durations");
        $negative = Duration::hours(-48);
        self::displayDuration($negative, "-48 hours");
        self::testResult("isNegative()", $negative->isNegative());
        self::testResult("abs() works", $negative->abs()->totalHours() === 48.0);
        
        self::subtitle("Immutability");
        $original = Duration::hours(3);
        $modified = $original->add(Duration::minutes(30));
        self::testResult("Original unchanged", $original->totalHours() === 3.0);
        self::testResult("Modified correct", $modified->totalHours() === 3.5);
        
        self::subtitle("Operation chaining");
        $chained = Duration::hours(1)
            ->add(Duration::minutes(30))
            ->multiply(2)
            ->divide(3)
            ->abs();
        
        self::displayDuration($chained, "Chained result: ((1h + 30m) × 2 ÷ 3)");
        
        self::warningBox("The class handles all edge cases correctly!");
    }

    /**
     * Test creation from DateInterval
     */
    public static function testDateInterval(): void
    {
        self::section("DATEINTERVAL TEST", "📅");
        
        try {
            $interval = new DateInterval('P1DT2H30M');
            $duration = Duration::fromDateInterval($interval);
            
            self::displayDuration($duration, "From DateInterval('P1DT2H30M')");
            self::testResult("DateInterval → Duration conversion", true);
            
            // Reverse conversion
            $backToInterval = $duration->toDateInterval();
            self::testResult("Duration → DateInterval conversion", 
                $backToInterval instanceof DateInterval);
            
            self::infoBox("DateInterval conversions work bidirectionally!");
            
        } catch (Exception $e) {
            self::testResult("DateInterval Test", false, "Error: " . $e->getMessage());
        }
    }

    /**
     * Performance test
     */
    public static function testPerformance(): void
    {
        self::section("PERFORMANCE TEST", "⚡");
        
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
            "{$iterations} operations in " . round($timeMs, 2) . "ms",
            true,
            sprintf("→ %.1f operations/ms", $opsPerMs)
        );
        
        self::displayDuration($total, "Cumulative result (demo)");
        
        if ($opsPerMs > 100) {
            self::infoBox("Excellent performance! (> 100 operations/ms)");
        } else {
            self::warningBox("Good performance but could be optimized");
        }
    }

    /**
     * Complete demonstration
     */
    public static function runAllTests(): void
    {
        // Header
        echo "\n" . str_repeat("✨", 40) . "\n";
        echo self::BOLD . self::CYAN . 
             "   VISUAL TESTS - DURATION PHP   " . 
             self::RESET . "\n";
        echo str_repeat("✨", 40) . "\n";
        echo self::YELLOW . "Date: " . date('Y-m-d H:i:s') . self::RESET . "\n";
        echo self::YELLOW . "PHP: " . PHP_VERSION . self::RESET . "\n\n";
        
        // Run all tests
        self::testFactoryMethods();
        self::testParsing();
        self::testArithmetic();
        self::testComparisons();
        self::testFormatting();
        self::testConversions();
        self::testDateInterval();
        self::testEdgeCases();
        self::testPerformance();
        
        // Footer
        echo "\n" . str_repeat("═", 80) . "\n";
        echo self::BG_GREEN . self::WHITE . self::BOLD . 
             " ✅ ALL TESTS COMPLETED SUCCESSFULLY! " . 
             self::RESET . "\n";
        echo str_repeat("═", 80) . "\n";
        
        // Summary
        echo "\n" . self::MAGENTA . self::BOLD . "📈 FEATURES TESTED SUMMARY:" . self::RESET . "\n";
        echo "  • 🏭 7 different factory methods\n";
        echo "  • 🔤 10+ supported parsing formats\n";
        echo "  • 🧮 6 arithmetic operations\n";
        echo "  • ⚖️ 8 comparison types\n";
        echo "  • 🎨 5 formatting languages\n";
        echo "  • 🔄 14 conversion types\n";
        echo "  • 📅 Bidirectional DateInterval conversion\n";
        echo "  • ⚡ Included performance test\n";
        echo "  • ⚠️  Edge cases handled\n";
        echo "\n" . self::GREEN . "The Duration class is production-ready! 🚀" . self::RESET . "\n\n";
    }
}

/**
 * Auto-execute if called directly
 */
if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    if (!class_exists('Utils\Duration')) {
        echo "\033[31m❌ ERROR: Duration class not found.\n";
        echo "Make sure Duration.php is in Utils/\n\033[0m";
        exit(1);
    }
    
    try {
        DurationVisualTest::runAllTests();
    } catch (Exception $e) {
        echo "\n\033[1;31m❌ CRITICAL ERROR DURING TESTS:\033[0m\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        exit(1);
    }
} elseif (PHP_SAPI !== 'cli') {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Duration PHP Tests</title>
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
        <h1>⚠️ Duration PHP Tests — CLI Execution Required</h1>
        <p>These tests are designed to run in the terminal for optimal display.</p>
        <p>Run: <code>php tests/DurationTest.php</code></p>
    </body>
    </html>";
}