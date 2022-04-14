<?php

declare(strict_types=1);

namespace VA\Vitte;

use Dakujem\Peat\ViteBridge;
use Dakujem\Peat\ViteBuildLocator;
use Dakujem\Peat\ViteLocatorContract;

/**
 * ViteNetteBridge
 *
 * @copyright Via Aurea, s.r.o.
 */
class ViteNetteBridge
{
    protected ?ViteLocatorContract $passiveLocator = null;
    protected string $path;
    protected string $wwwDir;
    protected bool $dev;
    protected ?string $tempDir;
    protected string $basePath;
    protected string $manifest;
    protected string $tempFile;
    protected string $devUrl;
    protected bool $strict;

    public function __construct(
        string $path,
        string $wwwDir,
        bool $dev = false,
        ?string $tempDir = null,
        string $basePath = '/',
        string $manifest = 'manifest.json',
        string $tempFile = 'vite.php',
        string $devUrl = 'http://localhost:3000',
        bool $strict = false
    ) {
        $this->path = $path;
        $this->wwwDir = $wwwDir;
        $this->basePath = $basePath;
        $this->manifest = $manifest;
        $this->tempDir = $tempDir;
        $this->tempFile = $tempFile;
        $this->dev = $dev;
        $this->devUrl = $devUrl;
        $this->strict = $strict;
    }

    public function makePassiveEntryLocator(): ViteLocatorContract
    {
        $this->passiveLocator ??= ViteBridge::makePassiveEntryLocator(
            "{$this->wwwDir}/{$this->path}/{$this->manifest}",
            "{$this->tempDir}/{$this->tempFile}",
            "{$this->basePath}/{$this->path}",
            $this->dev ? $this->devUrl : null,
            $this->strict,
        );
        return $this->passiveLocator;
    }

    public function cacheWarmup(): void
    {
        $populator = new ViteBuildLocator(
            "{$this->wwwDir}/{$this->path}/{$this->manifest}",
            "{$this->tempDir}/{$this->tempFile}",
            "{$this->basePath}/{$this->path}",
            $this->strict,
        );
        $populator->populateCache();
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
