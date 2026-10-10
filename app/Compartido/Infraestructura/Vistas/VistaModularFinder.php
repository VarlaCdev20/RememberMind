<?php

namespace App\Compartido\Infraestructura\Vistas;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;

/** Preserve logical Blade names while each implementation lives with its owner. */
final class VistaModularFinder extends FileViewFinder
{
    public function __construct(
        Filesystem $files,
        array $paths,
        private readonly array $aliases,
        private readonly string $basePath,
    ) {
        parent::__construct($files, $paths);
    }

    public function find($name): string
    {
        $name = trim($name);
        if (! isset($this->aliases[$name])) {
            return parent::find($name);
        }

        $path = $this->basePath.'/'.$this->aliases[$name];
        if (! $this->files->exists($path)) {
            throw new InvalidArgumentException("View [{$name}] not found.");
        }

        return $path;
    }
}
