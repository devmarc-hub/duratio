# 🕒 duratio

Une classe PHP **immuable**, **ultra-précise** et **multilingue** pour représenter, manipuler et formater des durées.  
Inspirée des implémentations de **Go** (`time.Duration`) et **Java** (`java.time.Duration`).

> ✨ **Zéro dépendance • Précision nanoseconde • Thread-safe • ISO 8601 • 5 langues natives**
---
> 🇬🇧 [English version available here](README-en.md)
---

## 📖 Documentation Interactive
[![Documentation](https://img.shields.io/badge/docs-live-brightgreen)](https://devmarc-hub.github.io/duratio/)

La documentation complète est incluse dans le projet sous forme de :
- 📚 **Guide d’utilisation détaillé**
- 🔍 **Référence API exhaustive**
- 💡 **Exemples concrets et réalistes**
- 🌍 **Support multilingue intégré** (FR, EN, ES, DE, IT)
- ⚡ **Démonstrations interactives** via les tests

> 👉 Voir `tests/DurationTest.php` pour une exploration visuelle et colorée en CLI.

---

## ✨ Fonctionnalités

| Catégorie | Fonctionnalité |
|----------|----------------|
| ⚡ **Précision** | Stockage interne en **nanosecondes** (entier signé 64 bits) |
| 🌍 **Internationalisation** | Formatage humain en **français, anglais, espagnol, allemand, italien** |
| 🔒 **Immutabilité** | Totalement **thread-safe** — aucune modification d’état après création |
| 📐 **Standards** | Support complet du format **ISO 8601** (`PT1H30M`) + format simplifié (`1h30m`) |
| ➕ **Opérations** | Addition, soustraction, multiplication, division, négation, valeur absolue |
| 🎨 **Formatage** | `humanize()`, `toShortString()`, `toISO8601()`, `format()` personnalisé |
| 🔄 **Interopérabilité** | Conversion bidirectionnelle avec `\DateInterval` |
| 🧪 **Robustesse** | Validation stricte, exceptions explicites, parsing sécurisé |
| 📦 **Légèreté** | Une seule classe, **aucune dépendance**, compatible PHP 8.0+ |

---

## 🚀 Installation

### Via Composer (recommandé)

    composer require devmarc-hub/duratio:dev-main

---

### Manuellement

    Téléchargez Utils/Duration.php
    Incluez-la dans votre projet :
        require_once 'path/to/Utils/Duration.php';
        use Utils\Duration;

---

## 📦 Usage Rapide
    
    require_once 'vendor/autoload.php';
    use Utils\Duration;

    // Création
    $duree1 = Duration::hours(2)->add(Duration::minutes(30)); // 2h30
    $duree2 = Duration::parse('1h45m');                      // 1h45

    // Formatage multilingue
    echo $duree1->humanize('fr');  // "2 heures et 30 minutes"
    echo $duree1->humanize('en');  // "2 hours and 30 minutes"
    echo $duree1->toShortString(); // "2h30m"

    // Opérations arithmétiques
    $total = $duree1->add($duree2);    // 4h15
    $moitie = $duree1->divide(2);      // 1h15

    // Comparaisons
    if ($duree1->greaterThan($duree2)) {
        echo "La première durée est plus longue.";
    }

    // Conversions numériques
    echo $duree1->totalMinutes(); // 150.0 (float)
    echo $duree1->inSeconds();    // 9000 (int arrondi)

---

## 🎯 Exemples Avancés

### 🔧 Formatage personnalisé

    $duree = Duration::parse('2d5h30m15s250ms');

    echo $duree->format('%d jours %h:%m:%s');               // "2 jours 05:30:15"
    echo $duree->format('%h:%m:%s.%ms');                    // "53:30:15.250"
    echo $duree->humanize('fr', ['compact' => true]);       // "2j 5h"
    echo $duree->toISO8601();                               // "P2DT5H30M15.25S"

---

## ⏱️ Gestion de timeout robuste

    class APIClient {
        private Duration $timeout;
        private float $startTime;

        public function __construct(string $timeout) {
            $this->timeout = Duration::parse($timeout);
            $this->startTime = microtime(true);
        }

        public function tempsRestant(): string {
            $ecoule = Duration::microseconds((int)((microtime(true) - $this->startTime) * 1_000_000));
            $reste = $this->timeout->subtract($ecoule);

            return $reste->isPositive()
                ? $reste->humanize('fr', ['precision' => 2, 'compact' => true])
                : '❌ Timeout dépassé';
        }
    }

---

## 🌐 Interface multilingue dynamique
    class Application {
        private string $lang;

        public function __construct(string $lang = 'fr') {
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

## 📁 Structure du Projet

    duratio/
    ├── Utils/
    │   └── Duration.php                   # Classe principale
    ├── tests/
    │   └── DurationTest.php               # Tests unitaires (version française )
    │   └── DurationTest-en.php            # Tests unitaires (version anglaise)
    ├── docs
    │   └── index.html                     # Documentation (version française)
    │   └── index-en.html                  # Documentation (version anglaise)
    ├── composer.json                      # Configuration Composer
    ├── LICENSE                            # Licence MIT
    ├── README.md                          # Readme (version française)
    └── README-en.md                       # Readme (version anglaise)

---

## 📊 Performance & Compatibilité

    PHP ≥ 8.0 requis (utilisation de declare(strict_types=1))
    Stockage : entier 64 bits → supporte ±292 ans en nanosecondes
    Opérations : calculs directs, sans allocation superflue
    Parsing : regex optimisée, validation stricte
    Mémoire : objet léger (~80 octets par instance)

    ✅ Testé sur des millions d’opérations/secondes sans ralentissement.

---

## ⚠️ Bonnes Pratiques & Pièges à Éviter

### Terminologie

    Cette classe gère des intervalles de temps, pas la « duration » financière (Macaulay).
    ✅ Recommandé        
        $duree = Duration::hours(2);    
        $delai = Duration::minutes(30);
        $intervalle = Duration::days(1);
    
    ❌ À éviter    
        $duration = Duration::years(5); (ambigu)
        Mélanger avec des concepts financiers

### Limites connues

        Les DateInterval contenant des mois ou années sont refusés (durée variable → non convertible de façon exacte).
        La précision nanoseconde est stockée en entier, mais les opérations flottantes (multiply, divide) peuvent introduire un arrondi.    	

---

## ❓ FAQ

    Q : Pourquoi refuser les mois/années dans fromDateInterval() ?
    R : Parce qu’un mois peut durer 28 à 31 jours — ce n’est pas une durée fixe. Cette classe privilégie la précision déterministe.

    Q : Puis-je étendre les langues ?
    R : Pas directement (la classe est final), mais vous pouvez créer un wrapper ou contribuer via une PR.

    Q : Est-ce compatible avec Carbon ou DateTime ?
    R : Oui ! Utilisez toDateInterval() pour l’intégrer à DateTime::add() ou Carbon::add().

    Q : Pourquoi pas de méthode sleep() ?
    R : Pour rester pure (pas d’effet de bord). Mais vous pouvez faire :  usleep($duration->inMicroseconds());

---

## 📄 Licence

    MIT License 
    © 2026 devmarc-hub

    Vous êtes libre d’utiliser, copier, modifier, fusionner, publier, distribuer...etc
    sous réserve des conditions cités dans les termes de la licence.
    
    Voir le fichier LICENSE pour les termes complets.

---

## 🙏 Remerciements

    🌀 Go Team – pour time.Duration
    ☕ Java Time API – pour son élégance et sa rigueur
    🐘 Communauté PHP – pour les standards PSR et les bonnes pratiques
    🧪 PHPUnit & TDD – pour l’inspiration des tests robustes