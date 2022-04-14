# Vitte: Latte-Vite bridge

> 💿 `composer require viaaurea/vitte`

Vite most pre Latte (Nette).

Latte sablona:
```html
  {vite src/main.js}
  <div id="app" />
```

Podporuje Vite dev server aj produkcny bundle vygenerovany pomocou Vite.


## Integration in Nette

Najjednoduchsou cestou je dekoracia `Latte\Engine` pomocou `ViteLatteInstaller` triedy:

```yaml
# any.neon (Nette)
services:
    vite.bridge:
        class:          VA\Vitte\ViteNetteBridge
        arguments:
            path:       assets                          # Relativna cesta od document_root k manifestu
            manifest:   manifest.json                   # Nazov manifest suboru
            tempFile:   vite.php                        # Pre kazdy Vite bundle musi byt vlastny cache subor v temp adresari.
            dev:        %system.vite.vue.development%   # Pri zapnutom dev rezime produkuje linky na Vite dev-server
            devUrl:     %system.vite.vue.url%           # Default je 'http://localhost:3000'
            strict:     %system.development%            # Striktny rezim zapneme len pri vyvoji
            basePath:   @http.paths::getBasePath()
            wwwDir:     %wwwDir%
            tempDir:    %tempDir%
    vite.locator:
      factory:          @vite.bridge::makePassiveEntryLocator()

decorator:
  Latte\Engine:
    setup:
      - VA\Vitte\ViteLatteInstaller()::bundle(...)::install(@self)
```

V sablone bude po uspesnej instalacii dostupne makro `{vite}`:
```html
  {vite src/main.js}
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


## Vite configuration

Pre spravnu funkcnost je potrebne [nakonfigurovat Vite](https://vitejs.dev/guide/backend-integration.html).

Vid [tipy na Vite konfiguraciu](https://github.com/dakujem/peat#vite-configuration).


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
    $vite = $container->get('vite.bridge');
    $vite->cacheWarmup();

    echo "ok\n";
})();
```

