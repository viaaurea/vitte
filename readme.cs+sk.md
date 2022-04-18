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
            devUrl:     %system.vite.vue.url%           # Default je 'http://localhost:3000'
            strict:     yes                             # Striktny rezim bude pre vas mozno vhodly len pri vyvoji
            basePath:   @http.paths::getBasePath()
            wwwDir:     %wwwDir%
            tempDir:    %tempDir%

decorator:
  Latte\Engine:
    setup:
      - VA\Vitte\ViteLatteInstaller()::bundle(
          @vite::makePassiveEntryLocator(
            %system.vite.vue.development%               # Pri zapnutom dev rezime produkuje linky na Vite dev-server
          )
        )::install(@self)
```

V sablone bude po uspesnej instalacii dostupne makro `{vite}`:
```html
  {vite main.js}
  <div id="app" />
```

Nazov makra je nastavitelny.


### Viacero bundlov 

V pripade, ze pouzivate viacero Vite bundlov (napr. Vue a React, alebo bundlujete viacero widgetov samostatne),
je mozne zaregistrovat viacero bundlov, vid `ViteLatteInstaller::bundle()` metodu.

Pouzitie je potom nasledovne:
```html
  {vite src/main.js vue-bundle}
  {vite src/main.js react-bundle}
```

> Pozor, `ViteLatteInstaller::bundle` je potrebne volat pre kazdy bundle, vratane nazvu bundlu:\
> `ViteLatteInstaller::bundle(..., 'vue-bundle')::install(@self)`


## Konfiguracia Vite

Pre spravnu funkcnost je potrebne nakonfigurovat Vite (`vite.config.js`):

- `build.manifest` musi byt `true`
- `build.rollupOptions.input` ma ukazovat na `main.js` (alebo iny vstupny bod JS aplikacie)

Viac info a vysvetlenie najdete tu:
- [nastavenie nastroja PHP-Vite (Peat)](https://github.com/dakujem/peat#vite)
- [oficialna Vite dokumentacia](https://vitejs.dev/guide/backend-integration.html).

> 💡
>
> Vhodne je tiez nastavit `build.outDir`, aby smeroval do subadresara pod "document root",
> aby nebolo nutne presuvat subory rucne.


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

