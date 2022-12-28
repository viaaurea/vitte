# Vitte: Latte-Vite most

> 💿 `composer require viaaurea/vitte`

Vite most pre Latte (Nette).

>
> [🇬🇧 English readme](readme.md)
> 

Latte sablona:
```html
  {vite main.js}
  <div id="app" />
```

Podporuje Vite _dev server_ aj produkcny _bundle_ vygenerovany pomocou Vite.


## Integracia s Nette

Najjednoduchsou cestou je dekoracia `Latte\Engine` pomocou `ViteLatteInstaller` triedy:

```yaml
# any.neon (Nette)
services:
    vite:
        class:          VA\Vitte\ViteNetteBridge
        arguments:
            path:       assets/vite-bundle              # Relativna cesta od www k manifestu
            manifest:   manifest.json                   # Nazov manifest suboru
            tempFile:   vite.php                        # Pre kazdy Vite bundle musi byt vlastny cache subor v temp adresari.
            devUrl:     %system.vite.url%               # Default je 'http://localhost:5173'
            strict:     yes                             # Striktny rezim bude pre vas mozno vhodly len pri vyvoji
            basePath:   @http.paths::getBasePath()
            wwwDir:     %wwwDir%
            tempDir:    %tempDir%

decorator:
  Latte\Engine:
    setup:
      - VA\Vitte\ViteLatteInstaller()::bundle(
          @vite::makePassiveEntryLocator(
            %system.vite.development%                   # Pri zapnutom dev rezime produkuje linky na Vite dev-server
          )
        )::install(@self)
```

V sablone bude po uspesnej instalacii dostupne makro `{vite}`:
```html
  {vite src/main.js}
  <div id="app" />
```

> Nazov makra je nastavitelny.

V zavislosti od `%system.vite.development%` premennej (mozete nahradit za vlastnu),
makro produkuje tagy pre produkcne alebo vyvojove prostredie:
```html
<!-- PRODUCTION -->
<script type="module" src="/placeholder/assets/main.cf1f50e2.js"></script>
<script type="module" src="/placeholder/assets/vendor.5f8262d6.js"></script>
<link rel="stylesheet" href="/placeholder/assets/main.c9fc69a7.css" />

<!-- DEVELOPMENT -->
<script type="module" src="http://localhost:5173/@vite/client"></script>
<script type="module" src="http://localhost:5173/src/main.js"></script>
```


## Konfiguracia Vite

Pre spravnu funkcnost je potrebne nakonfigurovat Vite (`vite.config.js`):

- `build.manifest` musi byt `true`
- `build.rollupOptions.input` ma ukazovat na `main.js` (alebo iny vstupny bod JS aplikacie)

**Vysvetlenie** a viac info najdete tu:
- 👉 [nastavenie nastroja PHP-Vite (Peat)](https://github.com/dakujem/peat#vite)
- [oficialna Vite dokumentacia](https://vitejs.dev/guide/backend-integration.html).

> 💡
>
> Vhodne je tiez nastavit `build.outDir`, aby smeroval do subadresara pod "document root",
> aby nebolo nutne presuvat subory rucne.


## Kompatibilita

Kompatibilne s Vite verziami `2` a vyssie.

Berte na vedomie, prosim, ze default port vyvojoveho serveru sa zmenil vo Vite `v3` z `3000` na `5173`.


## Cache Warmup

Skript spustit pocas build stepu:
```php
<?php

declare(strict_types=1);

use VA\Vitte\ViteNetteBridge;

/**
 * Tento skript predgeneruje PHP cache pre Vite integraciu.
 * Umoznuje marginalne zrychlenie v produkcnom prostredi, pretoze nie je nutne parsovat JSON manifest.
 */
(function () {
    $root = __DIR__ . '/../';

    /* @var $container DI\Container */
    $container = require_once $root . 'app/bootstrap.php';

    echo "Vite cache warmup: "; // echo az po vytvoreni kontajneru

    /** @var ViteNetteBridge $vite */
    $vite = $container->get('vite');
    $vite->populateCache();

    echo "ok\n";
})();
```


## Viacero bundlov

V pripade, ze pouzivate viacero Vite bundlov (napr. Vue a React, alebo bundlujete viacero widgetov samostatne),
je mozne zaregistrovat viacero bundlov, vid `ViteLatteInstaller::bundle()` metodu.

Pouzitie je potom nasledovne:
```html
  {vite src/main.js vue-bundle}
  {vite src/main.js react-bundle}
```

> Pozor, `ViteLatteInstaller::bundle` je potrebne volat pre kazdy bundle, vratane nazvu bundlu:\
> `ViteLatteInstaller::bundle(..., 'vue-bundle')::install(@self)`

