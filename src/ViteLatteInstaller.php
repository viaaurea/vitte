<?php

declare(strict_types=1);

namespace VA\Vitte;

use Dakujem\Peat\ViteLocatorContract;
use Latte\Compiler;
use Latte\Engine;
use Latte\Macros\MacroSet;
use LogicException;

/**
 * Makro sa sklada z 2 casti:
 * - makro {vite}
 * - filter `vite`
 * Makro interne vola filter so zvolenym nazvom (default je `vite`).
 * Filter je mozne upravit podla potrieb, nie je ho nutne instalovat cez tento instalator.
 *
 * V pripade, ze na projekte mate niekolko Vite bundlov s roznou konfiguraciou (Vue, React, atd),
 * je to mozne nastavit cez metodu bundle() (ale nezabudnite pouzit parameter $bundleName).
 *
 * @copyright Via Aurea, s.r.o.
 */
final class ViteLatteInstaller
{
    public const DEFAULT = 'vite';

    /** @var ViteLocatorContract[] */
    private array $bundles;

    public function __construct(array $bundles = [])
    {
        $this->bundles = $bundles;
    }

    public function bundle(ViteLocatorContract $vite, ?string $bundleName = null): self
    {
        $this->bundles[$bundleName ?? self::DEFAULT] = $vite;
        return $this;
    }

    public function install(
        Engine $latte,
        string $macroName = self::DEFAULT,
        string $filterName = self::DEFAULT
    ): self {
        $this->installFilter($latte, $filterName);
        $latte->onCompile[] = function () use ($latte, $macroName, $filterName) {
            $this->installMacro($latte->getCompiler(), $macroName, $filterName);
        };
        return $this;
    }

    public function installMacro(
        Compiler $compiler,
        string $macroName = self::DEFAULT,
        string $filterName = self::DEFAULT
    ): self {
        $macro = new MacroSet($compiler);
        $macro->addMacro($macroName, 'echo ($this->filters->' . $filterName . ')(%node.word, ...%node.array)');
        $compiler->addMacro($macroName, $macro);
        return $this;
    }

    public function installFilter(Engine $latte, string $filterName = self::DEFAULT): self
    {
        $latte->addFilter($filterName, fn(
            ?string $entry,
            ?string $bundle = null
        ) => $this->selectBundle($bundle)->entry($entry));
        return $this;
    }

    private function selectBundle(?string $bundleName = null): ViteLocatorContract
    {
        if (!($this->bundles[$bundleName ?? self::DEFAULT] ?? null) instanceof ViteLocatorContract) {
            throw new LogicException(sprintf(
                'No vite bundle registered under the name "%s". ' .
                'A bundle can be registered by calling %s::bundle method. Available bundles are: %s.',
                $bundleName,
                self::class,
                implode(', ', array_map(fn($n) => "\"$n\"", array_keys($this->bundles)))
            ));
        }
        return $this->bundles[$bundleName ?? self::DEFAULT];
    }
}
