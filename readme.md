# Vitte: Latte-Vite bridge

> 💿 `composer require viaaurea/vitte`

Vite bridge for Latte templates (Nette).

>
> [🇸🇰/🇨🇿 Slovenská / Česká verzia readme](readme.md)
>

Usage in Latte:
```html
  {vite main.js}
  <div id="app" />
```

Supports both Vite's _development server_ and Vite-generated _bundles_.


## Integration with Nette

Decorate `Latte\Engine` using `ViteLatteInstaller`:

```yaml
# any.neon (Nette)
services:
    vite:
        class:          VA\Vitte\ViteNetteBridge
        arguments:
            path:       assets/vite-bundle              # Relative path from www dir to the manifest file
            manifest:   manifest.json                   # manifest file name
            tempFile:   vite.php                        # Each vite bundle must have a dedicated cache file.
            devUrl:     %system.vite.vue.url%           # Default is 'http://localhost:3000'
            strict:     yes                             # You may want to turn strict mode on in development only
            basePath:   @http.paths::getBasePath()
            wwwDir:     %wwwDir%
            tempDir:    %tempDir%

decorator:
  Latte\Engine:
    setup:
      - VA\Vitte\ViteLatteInstaller()::bundle(
          @vite::makePassiveEntryLocator(
            %system.vite.vue.development%               # When on, serves links to Vite dev-server only
          )
        )::install(@self)
```

The `{vite}` macro is then available in the templates:
```html
  {vite main.js}
  <div id="app" />
```

The name of the macro is configurable.


### Viacero bundlov 

Vitte supports multiple Vite bundles (e.g. combining React and Vue bundles),
see the `ViteLatteInstaller::bundle()` method.

Usage:
```html
  {vite src/main.js vue-bundle}
  {vite src/main.js react-bundle}
```

> Note that you need to call `ViteLatteInstaller::bundle` for each bundle, like this:\
> `ViteLatteInstaller::bundle(..., 'vue-bundle')::install(@self)`


## Vite configuration

Vite (`vite.config.js`) must be configured for correct integration:

- `build.manifest` must be set to `true`
- `build.rollupOptions.input` should point to the `main.js` (or other JS entrypoint)

Explanation and more information can be found here:
- [PHP-Vite bridge building tool (Peat)](https://github.com/dakujem/peat#vite)
- [official Vite documentation](https://vitejs.dev/guide/backend-integration.html).

> 💡
>
> You may also want to set `build.outDir` to point to a sub folder in the backend's public dir,
> so that you don't have to move the build files manually after each build.


## Cache Warmup

Run this as a build step:
```php
<?php

declare(strict_types=1);

use VA\Vitte\ViteNetteBridge;

/**
 * This script pre-generates a cache file for Vite integration.
 * Improves performance by including a PHP file instead of parsing the JSON manifest. Useful in production environments.
 */
(function () {
    $root = __DIR__ . '/../';

    /* @var $container DI\Container */
    $container = require_once $root . 'app/bootstrap.php';

    echo "Vite cache warmup: "; // echo after the container has been populated

    /** @var ViteNetteBridge $vite */
    $vite = $container->get('vite');
    $vite->populateCache();

    echo "ok\n";
})();
```

