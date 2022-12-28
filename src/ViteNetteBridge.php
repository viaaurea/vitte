<?php

declare(strict_types=1);

namespace VA\Vitte;

use Dakujem\Peat\ViteBridge;
use Dakujem\Peat\ViteLocatorContract;

/**
 * ViteNetteBridge
 *
 * @copyright Via Aurea, s.r.o.
 */
class ViteNetteBridge
{
    protected ViteBridge $viteBridge;

    public function __construct(
        string $path,
        string $wwwDir,
        ?string $tempDir = null,
        string $basePath = '/',
        string $manifest = 'manifest.json',
        string $tempFile = 'vite.php',
        string $devUrl = 'http://localhost:5173',
        bool $strict = true
    ) {
        $this->viteBridge = new ViteBridge(
            "{$wwwDir}/{$path}/{$manifest}",
            "{$tempDir}/{$tempFile}",
            "{$basePath}/{$path}",
            $devUrl,
            $strict,
        );
    }

    public function makePassiveEntryLocator(bool $dev = false): ViteLocatorContract
    {
        return $this->viteBridge->makePassiveEntryLocator($dev);
    }

    public function populateCache(): void
    {
        $this->viteBridge->populateCache();
    }

    /**
     * Konkatenuje dva retazce.
     * Poznamka: Tato metoda tu je kvoli NEON konfiguracii, kde syntax nedovoluje konkatenaciu retazcov.
     */
    public static function concat($prefix, $path): string
    {
        return $prefix . $path;
    }

    /**
     * Podmienka / ternarny operator pre pouzitie v NEON konfiguracii.
     * Poznamka: NEON syntax nepodporuje ternarny operator.
     */
    public static function ternary($predicate, $value, $default = null) //: mixed
    {
        return $predicate ? $value : $default;
    }
}
