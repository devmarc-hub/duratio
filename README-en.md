# 🕒 duratio

An **immutable**, **ultra-precise**, and **multilingual** PHP class for representing, manipulating, and formatting time durations.  
Inspired by the designs of **Go** (`time.Duration`) and **Java** (`java.time.Duration`).

> ✨ **Zero dependencies • Nanosecond precision • Thread-safe • ISO 8601 • 5 built-in languages**
---
> 🇫🇷 [Version française disponible ici](README.md)

---

## 📖 Interactive Documentation
[![Documentation](https://img.shields.io/badge/docs-live-brightgreen)](https://devmarc-hub.github.io/duratio/)

The full documentation is included in the project as:
- 📚 **Detailed usage guide**
- 🔍 **Exhaustive API reference**
- 💡 **Real-world examples**
- 🌍 **Built-in multilingual support** (EN, FR, ES, DE, IT)
- ⚡ **Interactive CLI demos** via test suite

> 👉 Run `php tests/DurationTest.php` for a colorful, visual exploration in your terminal.

---

## ✨ Features

| Category | Capability |
|----------|------------|
| ⚡ **Precision** | Internal storage in **nanoseconds** (signed 64-bit integer) |
| 🌍 **Internationalization** | Human-readable output in **English, French, Spanish, German, Italian** |
| 🔒 **Immutability** | Fully **thread-safe** — no state mutation after creation |
| 📐 **Standards** | Full **ISO 8601** support (`PT1H30M`) + simple format (`1h30m`) |
| ➕ **Operations** | Addition, subtraction, multiplication, division, absolute value, negation |
| 🎨 **Formatting** | `humanize()`, `toShortString()`, `toISO8601()`, custom `format()` |
| 🔄 **Interoperability** | Bidirectional conversion with `\DateInterval` |
| 🧪 **Robustness** | Strict validation, clear exceptions, secure parsing |
| 📦 **Lightweight** | Single class, **zero dependencies**, compatible with PHP 8.0+ |

---

## 🚀 Installation

### Via Composer (recommended)

composer require devmarc-hub/duratio:dev-main

---

### Manually

    Download Utils/Duration.php
    Include it in your project:
        require_once 'path/to/Utils/Duration.php';
        use Utils\Duration;

---

## 📦 Quick Usage
    
    require_once 'vendor/autoload.php';
    use Utils\Duration;

    // Creation
    $duration1 = Duration::hours(2)->add(Duration::minutes(30)); // 2h30
    $duration2 = Duration::parse('1h45m');                      // 1h45

    // Multilingual formatting
    echo $duration1->humanize('en');  // "2 hours and 30 minutes"
    echo $duration1->humanize('fr');  // "2 heures et 30 minutes"
    echo $duration1->toShortString(); // "2h30m"

    // Arithmetic operations
    $total = $duration1->add($duration2);    // 4h15
    $half = $duration1->divide(2);           // 1h15

    // Comparisons
    if ($duration1->greaterThan($duration2)) {
        echo "First duration is longer.";
    }

    // Numeric conversions
    echo $duration1->totalMinutes(); // 150.0 (float)
    echo $duration1->inSeconds();    // 9000 (int, rounded)

---

## 🎯 Advanced Examples
### 🔧 Custom Formatting

    $duration = Duration::parse('2d5h30m15s250ms');

    echo $duration->format('%d days %h:%m:%s');     // "2 days 05:30:15"
    echo $duration->format('%h:%m:%s.%ms');        // "53:30:15.250"
    echo $duration->humanize('en', ['compact' => true]); // "2d 5h"
    echo $duration->toISO8601();                   // "P2DT5H30M15.25S"

---

## ⏱️ Robust Timeout Management

    class APIClient {
        private Duration $timeout;
        private float $startTime;

        public function __construct(string $timeout) {
            $this->timeout = Duration::parse($timeout);
            $this->startTime = microtime(true);
        }

        public function remainingTime(): string {
            $elapsed = Duration::microseconds((int)((microtime(true) - $this->startTime) * 1_000_000));
            $remaining = $this->timeout->subtract($elapsed);

            return $remaining->isPositive()
                ? $remaining->humanize('en', ['precision' => 2, 'compact' => true])
                : '❌ Timeout exceeded';
        }
    }

---

## 🌐 Dynamic Multilingual UI

    class Application {
        private string $lang;

        public function __construct(string $lang = 'en') {
            $this->lang = in_array($lang, ['fr','en','es','de','it']) ? $lang : 'en';
        }

        public function renderDuration(Duration $duration): string {
            return $duration->humanize($this->lang, [
                'precision' => 2,
                'compact' => false
            ]);
        }
    }

---

## 📁 Project Structure

    duratio/
    ├── Utils/
    │   └── Duration.php                  # Main class
    ├── tests/
    │   ├── DurationTest.php              # Visual CLI tests (FR)
    │   └── DurationTest-en.php           # Visual CLI tests (EN)
    ├── docs/
    │   ├── index.html                    # Documentation (French)
    │   └── index-en.html                 # Documentation (English)
    ├── composer.json                     # Composer config
    ├── LICENSE                           # MIT License
    ├── README-en.md                      # This file (English)
    └── README.md                         # French version

---

## 📊 Performance & Compatibility

    PHP ≥ 8.0 required (declare(strict_types=1))
    Storage: 64-bit integer → supports ±292 years in nanosecond precision
    Operations: direct arithmetic, no unnecessary allocations
    Parsing: optimized regex, strict validation
    Memory: lightweight object (~80 bytes per instance)

    ✅ Benchmarked at millions of operations per second with no slowdown.

---

## ⚠️ Best Practices & Gotchas

### Terminology

This class handles time intervals, not financial "duration" (Macaulay duration).
✅ Recommended      
    $delay = Duration::hours(2);  
    $timeout = Duration::days(1);
    $intervalle = Duration::days(1);
    
❌ Avoid
    $duration = Duration::years(5); (ambiguous)
    Mixing with financial contexts
    
### Known Limitations

    DateInterval objects containing months or years are rejected (variable length → not convertible to fixed duration).
    Nanosecond precision is stored as an integer, but floating-point operations (multiply, divide) may introduce rounding.

---

## ❓ FAQ

    Q: Why are months/years rejected in fromDateInterval()?
    A: Because a month can be 28–31 days — it’s not a fixed duration. This class prioritizes deterministic precision.

    Q: Can I add more languages?
    A: Not directly (the class is final), but you can create a wrapper or contribute via a PR.

    Q: Is it compatible with Carbon or DateTime?
    A: Yes! Use toDateInterval() to integrate with DateTime::add() or Carbon::add().

    Q: Why no sleep() method?
    A: To keep the class pure (no side effects). But you can do:  usleep($duration->inMicroseconds());

---

## 📄 License

    MIT License  
    © 2026 devmarc-hub

    Permission is hereby granted, free of charge, to any person obtaining a copy  
    of this software and associated documentation files, to deal in the Software  
    without restriction, including without limitation the rights to use, copy,  
    modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,  
    subject to the conditions stated in the license terms.

    See the LICENSE file for full details.

---

## 🙏 Acknowledgements

    🌀 Go Team – for time.Duration
    ☕ Java Time API – for its elegance and rigor
    🐘 PHP Community – for PSR standards and best practices
    🧪 PHPUnit & TDD – for inspiration on robust testing