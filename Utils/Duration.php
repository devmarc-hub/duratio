<?php

/**
 * Duration PHP - Classe immuable pour la gestion des durées
 * 
 * Copyright (c) 2026 marc.dev@gmx.fr
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Utils;

use InvalidArgumentException;
use Stringable;

/**
 * Représente une durée de temps de façon immuable et opérable.
 * Multilingue : supporte l'anglais, le français et d'autres langues.
 *
 * Inspiré de Go (time.Duration) et Java (java.time.Duration).
 *
 * Exemples :
 *   $d1 = Duration::parse('5m30s');
 *   $d2 = Duration::minutes(2)->add(Duration::seconds(15));
 *   echo $d1->humanize('fr'); // "5 minutes et 30 secondes"
 *   echo $d1->humanize('en'); // "5 minutes and 30 seconds"
 */
final class Duration implements Stringable
{
    private int $nanoseconds;

    private const NANOS_PER_SECOND = 1_000_000_000;
    private const NANOS_PER_MICROSECOND = 1_000;
    private const NANOS_PER_MILLISECOND = 1_000_000;
    private const NANOS_PER_MINUTE = 60 * self::NANOS_PER_SECOND;
    private const NANOS_PER_HOUR = 60 * self::NANOS_PER_MINUTE;
    private const NANOS_PER_DAY = 24 * self::NANOS_PER_HOUR;

    private const TRANSLATIONS = [
        'en' => [
            'units' => [
                'day' => ['day', 'days'],
                'hour' => ['hour', 'hours'],
                'minute' => ['minute', 'minutes'],
                'second' => ['second', 'seconds'],
                'millisecond' => ['millisecond', 'milliseconds'],
                'microsecond' => ['microsecond', 'microseconds'],
                'nanosecond' => ['nanosecond', 'nanoseconds'],
            ],
            'conjunctions' => ['and', ', '],
            'less_than' => 'less than',
            'now' => 'now',
        ],
        'fr' => [
            'units' => [
                'day' => ['jour', 'jours'],
                'hour' => ['heure', 'heures'],
                'minute' => ['minute', 'minutes'],
                'second' => ['seconde', 'secondes'],
                'millisecond' => ['milliseconde', 'millisecondes'],
                'microsecond' => ['microseconde', 'microsecondes'],
                'nanosecond' => ['nanoseconde', 'nanosecondes'],
            ],
            'conjunctions' => ['et', ', '],
            'less_than' => 'moins de',
            'now' => 'maintenant',
        ],
        'es' => [
            'units' => [
                'day' => ['día', 'días'],
                'hour' => ['hora', 'horas'],
                'minute' => ['minuto', 'minutos'],
                'second' => ['segundo', 'segundos'],
                'millisecond' => ['milisegundo', 'milisegundos'],
                'microsecond' => ['microsegundo', 'microsegundos'],
                'nanosecond' => ['nanosegundo', 'nanosegundos'],
            ],
            'conjunctions' => ['y', ', '],
            'less_than' => 'menos de',
            'now' => 'ahora',
        ],
        'de' => [
            'units' => [
                'day' => ['Tag', 'Tage'],
                'hour' => ['Stunde', 'Stunden'],
                'minute' => ['Minute', 'Minuten'],
                'second' => ['Sekunde', 'Sekunden'],
                'millisecond' => ['Millisekunde', 'Millisekunden'],
                'microsecond' => ['Mikrosekunde', 'Mikrosekunden'],
                'nanosecond' => ['Nanosekunde', 'Nanosekunden'],
            ],
            'conjunctions' => ['und', ', '],
            'less_than' => 'weniger als',
            'now' => 'jetzt',
        ],
        'it' => [
            'units' => [
                'day' => ['giorno', 'giorni'],
                'hour' => ['ora', 'ore'],
                'minute' => ['minuto', 'minuti'],
                'second' => ['secondo', 'secondi'],
                'millisecond' => ['millisecondo', 'millisecondi'],
                'microsecond' => ['microsecondo', 'microsecondi'],
                'nanosecond' => ['nanosecondo', 'nanosecondi'],
            ],
            'conjunctions' => ['e', ', '],
            'less_than' => 'meno di',
            'now' => 'adesso',
        ],
    ];

    private function __construct(int $nanoseconds)
    {
        $this->nanoseconds = $nanoseconds;
    }

    // ——————— FACTORIES STATIQUES ———————

    public static function nanoseconds(int $ns): self
    {
        return new self($ns);
    }

    public static function microseconds(int $us): self
    {
        return new self($us * self::NANOS_PER_MICROSECOND);
    }

    public static function milliseconds(int $ms): self
    {
        return new self($ms * self::NANOS_PER_MILLISECOND);
    }

    public static function seconds(int $s): self
    {
        return new self($s * self::NANOS_PER_SECOND);
    }

    public static function minutes(int $m): self
    {
        return new self($m * self::NANOS_PER_MINUTE);
    }

    public static function hours(int $h): self
    {
        return new self($h * self::NANOS_PER_HOUR);
    }

    public static function days(int $d): self
    {
        return new self($d * self::NANOS_PER_DAY);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public static function fromDateInterval(\DateInterval $interval): self
    {
        // Refuser les durées avec mois ou années (non convertibles de façon exacte)
        if ($interval->m !== 0 || $interval->y !== 0) {
            throw new InvalidArgumentException(
                'DateInterval with months or years cannot be converted to Duration (ambiguous duration).'
            );
        }

        $seconds = $interval->s
            + ($interval->i * 60)
            + ($interval->h * 3600)
            + ($interval->d * 86400);

        $nanoseconds = (int)($seconds * self::NANOS_PER_SECOND)
            + (int)round($interval->f * self::NANOS_PER_SECOND);

        if ($interval->invert) {
            $nanoseconds = -$nanoseconds;
        }

        return new self($nanoseconds);
    }

    public static function parse(string $input): self
    {
        $input = trim($input);
        if ($input === '') {
            return self::zero();
        }

        if (strtoupper($input[0]) === 'P') {
            return self::parseISO8601($input);
        }

        return self::parseSimple($input);
    }

    private static function parseISO8601(string $input): self
    {
        try {
            $interval = new \DateInterval($input);
            return self::fromDateInterval($interval);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                "Invalid ISO 8601 duration format: '$input'. " . $e->getMessage()
            );
        }
    }

    private static function parseSimple(string $input): self
	{
		// Normaliser µs → us
		$input = str_replace(['µs', 'µ'], 'us', $input);

		// IMPORTANT : ordonner les unités de la plus longue à la plus courte pour éviter les conflits (ex: "ms" vs "m")
		if (!preg_match_all('/(\d+(?:\.\d+)?)\s*(ms|us|ns|d|h|m|s)/i', $input, $matches, PREG_SET_ORDER)) {
			throw new InvalidArgumentException(
				"Invalid duration format: '$input'. Expected format like '1h30m', '90s', '2d5h30m', '1.5h'"
			);
		}

		$totalNanos = 0.0;
		$seen = [];

		foreach ($matches as $match) {
			$value = (float)$match[1];
			$unit = strtolower($match[2]);

			if (isset($seen[$unit])) {
				throw new InvalidArgumentException("Duplicate unit '$unit' in '$input'");
			}
			$seen[$unit] = true;

			switch ($unit) {
				case 'd': $totalNanos += $value * self::NANOS_PER_DAY; break;
				case 'h': $totalNanos += $value * self::NANOS_PER_HOUR; break;
				case 'm': $totalNanos += $value * self::NANOS_PER_MINUTE; break;
				case 's': $totalNanos += $value * self::NANOS_PER_SECOND; break;
				case 'ms': $totalNanos += $value * self::NANOS_PER_MILLISECOND; break;
				case 'us': $totalNanos += $value * self::NANOS_PER_MICROSECOND; break;
				case 'ns': $totalNanos += $value; break;
				default:
					throw new InvalidArgumentException("Unknown unit '$unit' in '$input'");
			}
		}

		return new self((int)round($totalNanos));
	}

    // ——————— ACCESSEURS ———————

    public function totalNanoseconds(): int { return $this->nanoseconds; }
    public function totalMicroseconds(): float { return $this->nanoseconds / self::NANOS_PER_MICROSECOND; }
    public function totalMilliseconds(): float { return $this->nanoseconds / self::NANOS_PER_MILLISECOND; }
    public function totalSeconds(): float { return $this->nanoseconds / self::NANOS_PER_SECOND; }
    public function totalMinutes(): float { return $this->nanoseconds / self::NANOS_PER_MINUTE; }
    public function totalHours(): float { return $this->nanoseconds / self::NANOS_PER_HOUR; }
    public function totalDays(): float { return $this->nanoseconds / self::NANOS_PER_DAY; }

    public function inNanoseconds(): int { return $this->nanoseconds; }
    public function inMicroseconds(): int { return (int)round($this->totalMicroseconds()); }
    public function inMilliseconds(): int { return (int)round($this->totalMilliseconds()); }
    public function inSeconds(): int { return (int)round($this->totalSeconds()); }
    public function inMinutes(): int { return (int)round($this->totalMinutes()); }
    public function inHours(): int { return (int)round($this->totalHours()); }
    public function inDays(): int { return (int)round($this->totalDays()); }

    // ——————— OPÉRATIONS ———————

    public function add(self $other): self { return new self($this->nanoseconds + $other->nanoseconds); }
    public function subtract(self $other): self { return new self($this->nanoseconds - $other->nanoseconds); }
    public function multiply(float $factor): self { return new self((int)round($this->nanoseconds * $factor)); }
    public function divide(float $divisor): self {
        if (abs($divisor) < PHP_FLOAT_EPSILON) {
            throw new InvalidArgumentException('Division by zero');
        }
        return new self((int)round($this->nanoseconds / $divisor));
    }
    public function abs(): self { return new self(abs($this->nanoseconds)); }
    public function negate(): self { return new self(-$this->nanoseconds); }

    // ——————— COMPARAISONS ———————

    public function equals(self $other): bool { return $this->nanoseconds === $other->nanoseconds; }
    public function greaterThan(self $other): bool { return $this->nanoseconds > $other->nanoseconds; }
    public function lessThan(self $other): bool { return $this->nanoseconds < $other->nanoseconds; }
    public function greaterThanOrEqual(self $other): bool { return $this->nanoseconds >= $other->nanoseconds; }
    public function lessThanOrEqual(self $other): bool { return $this->nanoseconds <= $other->nanoseconds; }
    public function compare(self $other): int { return $this->nanoseconds <=> $other->nanoseconds; }

    public function isZero(): bool { return $this->nanoseconds === 0; }
    public function isPositive(): bool { return $this->nanoseconds > 0; }
    public function isNegative(): bool { return $this->nanoseconds < 0; }

    // ——————— CONVERSION ———————

    public function toDateInterval(): \DateInterval
    {
        $absNanos = abs($this->nanoseconds);
        $secondsTotal = (int)($absNanos / self::NANOS_PER_SECOND);
        $fractionalNanos = $absNanos % self::NANOS_PER_SECOND;

        $interval = new \DateInterval('PT0S');
        $interval->d = (int)($secondsTotal / 86400);
        $secondsRemaining = $secondsTotal % 86400;
        $interval->h = (int)($secondsRemaining / 3600);
        $secondsRemaining %= 3600;
        $interval->i = (int)($secondsRemaining / 60);
        $interval->s = $secondsRemaining % 60;

        // Microsecondes (f = fraction de seconde)
        $microseconds = (int)round($fractionalNanos / 1000);
        $interval->f = $microseconds / 1_000_000;

        if ($this->isNegative()) {
            $interval->invert = 1;
        }

        return $interval;
    }

    // ——————— FORMATAGE ———————

    public function __toString(): string
    {
        return $this->toShortString();
    }

    public function toShortString(): string
    {
        if ($this->nanoseconds === 0) {
            return '0s';
        }

        $parts = [];
        $remaining = abs($this->nanoseconds);

        if ($days = intdiv($remaining, self::NANOS_PER_DAY)) {
            $parts[] = $days . 'd';
            $remaining -= $days * self::NANOS_PER_DAY;
        }
        if ($hours = intdiv($remaining, self::NANOS_PER_HOUR)) {
            $parts[] = $hours . 'h';
            $remaining -= $hours * self::NANOS_PER_HOUR;
        }
        if ($minutes = intdiv($remaining, self::NANOS_PER_MINUTE)) {
            $parts[] = $minutes . 'm';
            $remaining -= $minutes * self::NANOS_PER_MINUTE;
        }
        if ($seconds = intdiv($remaining, self::NANOS_PER_SECOND)) {
            $parts[] = $seconds . 's';
            $remaining -= $seconds * self::NANOS_PER_SECOND;
        }
        if ($milliseconds = intdiv($remaining, self::NANOS_PER_MILLISECOND)) {
            $parts[] = $milliseconds . 'ms';
            $remaining -= $milliseconds * self::NANOS_PER_MILLISECOND;
        }
        if ($microseconds = intdiv($remaining, self::NANOS_PER_MICROSECOND)) {
            $parts[] = $microseconds . 'µs';
            $remaining -= $microseconds * self::NANOS_PER_MICROSECOND;
        }
        if ($remaining > 0) {
            $parts[] = $remaining . 'ns';
        }

        $result = implode('', $parts);
        return $this->isNegative() ? '-' . $result : $result;
    }

    public function toISO8601(): string
    {
        $interval = $this->toDateInterval();
        $spec = 'P';

        if ($interval->d) $spec .= $interval->d . 'D';
        $timePart = '';
        if ($interval->h) $timePart .= $interval->h . 'H';
        if ($interval->i) $timePart .= $interval->i . 'M';
        if ($interval->s || $interval->f) {
            $s = $interval->s + $interval->f;
            $timePart .= sprintf("%.6F", $s) . 'S';
            // Supprimer les zéros superflus en fin de décimale
            $timePart = rtrim(rtrim($timePart, '0'), '.');
        }
        if ($timePart !== '') {
            $spec .= 'T' . $timePart;
        }

        if ($spec === 'P') {
            $spec = 'PT0S';
        }

        return $this->isNegative() ? '-' . $spec : $spec;
    }

    public function humanize(string $lang = 'en', array $options = []): string
    {
        $options = array_merge([
            'precision' => 2,
            'compact' => false,
            'show_zero' => false,
        ], $options);

        if (!isset(self::TRANSLATIONS[$lang])) {
            $lang = 'en';
        }

        $t = self::TRANSLATIONS[$lang];

        if ($this->nanoseconds === 0) {
            return $options['compact'] ? '0' : $t['now'];
        }

        $isNegative = $this->isNegative();
        $remaining = abs($this->nanoseconds);

        $units = [];
        if ($days = intdiv($remaining, self::NANOS_PER_DAY)) {
            $units['day'] = $days;
            $remaining -= $days * self::NANOS_PER_DAY;
        }
        if ($hours = intdiv($remaining, self::NANOS_PER_HOUR)) {
            $units['hour'] = $hours;
            $remaining -= $hours * self::NANOS_PER_HOUR;
        }
        if ($minutes = intdiv($remaining, self::NANOS_PER_MINUTE)) {
            $units['minute'] = $minutes;
            $remaining -= $minutes * self::NANOS_PER_MINUTE;
        }
        if ($seconds = intdiv($remaining, self::NANOS_PER_SECOND)) {
            $units['second'] = $seconds;
            $remaining -= $seconds * self::NANOS_PER_SECOND;
        }
        if ($milliseconds = intdiv($remaining, self::NANOS_PER_MILLISECOND)) {
            $units['millisecond'] = $milliseconds;
            $remaining -= $milliseconds * self::NANOS_PER_MILLISECOND;
        }
        if ($microseconds = intdiv($remaining, self::NANOS_PER_MICROSECOND)) {
            $units['microsecond'] = $microseconds;
            $remaining -= $microseconds * self::NANOS_PER_MICROSECOND;
        }
        if ($remaining > 0) {
            $units['nanosecond'] = $remaining;
        }

        if (!$options['show_zero']) {
            $units = array_filter($units, fn($v) => $v > 0);
        }

        if ($options['precision'] > 0) {
            $units = array_slice($units, 0, $options['precision'], true);
        }

        if (empty($units)) {
            return $t['less_than'] . ' 1 ' . $t['units']['millisecond'][0];
        }

        $parts = [];
        foreach ($units as $key => $value) {
            $names = $t['units'][$key];
            $name = $value === 1 ? $names[0] : $names[1];
            if ($options['compact']) {
                $parts[] = $value . ($lang === 'en' ? substr($name, 0, 1) : '');
            } else {
                $parts[] = $value . ' ' . $name;
            }
        }

        if (count($parts) === 1) {
            $result = $parts[0];
        } else {
            $last = array_pop($parts);
            $sep = $options['compact'] ? ' ' : $t['conjunctions'][1];
            $conj = $options['compact'] ? ' ' : ' ' . $t['conjunctions'][0] . ' ';
            $result = implode($sep, $parts) . $conj . $last;
        }

        return ($isNegative ? '-' : '') . $result;
    }

    public function format(string $pattern): string
    {
        $remaining = abs($this->nanoseconds);
        $days = intdiv($remaining, self::NANOS_PER_DAY); $remaining -= $days * self::NANOS_PER_DAY;
        $hours = intdiv($remaining, self::NANOS_PER_HOUR); $remaining -= $hours * self::NANOS_PER_HOUR;
        $minutes = intdiv($remaining, self::NANOS_PER_MINUTE); $remaining -= $minutes * self::NANOS_PER_MINUTE;
        $seconds = intdiv($remaining, self::NANOS_PER_SECOND); $remaining -= $seconds * self::NANOS_PER_SECOND;
        $ms = intdiv($remaining, self::NANOS_PER_MILLISECOND);

        $replacements = [
            '%d' => str_pad((string)$days, 2, '0', STR_PAD_LEFT),
            '%h' => str_pad((string)$hours, 2, '0', STR_PAD_LEFT),
            '%m' => str_pad((string)$minutes, 2, '0', STR_PAD_LEFT),
            '%s' => str_pad((string)$seconds, 2, '0', STR_PAD_LEFT),
            '%ms' => str_pad((string)$ms, 3, '0', STR_PAD_LEFT),
        ];

        $result = strtr($pattern, $replacements);
        return ($this->isNegative() ? '-' : '') . $result;
    }

    // ——————— DÉBOGAGE ———————

    public function __debugInfo(): array
    {
        return [
            'nanoseconds' => $this->nanoseconds,
            'human_fr' => $this->humanize('fr'),
            'iso8601' => $this->toISO8601(),
        ];
    }
}