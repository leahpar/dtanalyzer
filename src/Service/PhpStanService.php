<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PhpStanService
{
    private int $phpStanErrors;

    public function __construct(
        #[Autowire('%kernel.project_dir%/vendor/bin')]
        private readonly string $vendorPath
    ) {}

    public function getPhpStan(string $srcPath): void
    {
        $cmd = "{$this->vendorPath}/phpstan analyse -c phpstan.neon --no-progress -l 6 $srcPath";
        $output = shell_exec($cmd);
        $this->phpStanErrors = $this->parsePhpStanOutput($output);
    }

    public function getErrors(): int
    {
        return $this->phpStanErrors;
    }

    private function parsePhpStanOutput(string $output): int
    {
        // [OK] No errors      <=== 0
        // [ERROR] Found 7 errors <=== 7
        // [ERROR] ... <=== throw exception

        if (preg_match('/\[ERROR\] Found (\d+) errors/', $output, $matches)) {
            return (int)$matches[1];
        }

        if (preg_match('/\[ERROR\] (.*)\n/', $output, $matches)) {
            throw new \Exception($matches[1]);
        }

        if (str_contains($output, '[OK] No errors')) {
            return 0;
        }

    }

}
