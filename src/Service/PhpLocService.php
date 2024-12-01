<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PhpLocService
{

    private array $phpLocData = [];

    public function __construct(
        #[Autowire('%kernel.project_dir%/vendor/bin')]
        private readonly string $vendorPath
    ) {}

    public function getPhpLoc(string $srcPath): void
    {
        $cmd = "{$this->vendorPath}/phploc $srcPath";
        $output = shell_exec($cmd);
        $this->phpLocData = $this->parsePhpLocOutput($output);
    }

    public function getData(string $string): float
    {
        return $this->phpLocData[strtolower($string)]??0;
    }

    private function parsePhpLocOutput(string $output): array
    {
        $lines = explode("\n", $output);
        $result = [];
        foreach ($lines as $line) {
            $line = trim($line);
            $line = preg_replace('/\(.*\)/', "", $line);
            $line = preg_replace('/\s{2,}/', "\t", $line);
            if (empty($line)) {
                continue;
            }
            $parts = explode("\t", $line);
            $result[strtolower($parts[0])] = (float)($parts[1]??null);
        }
        return $result;
    }

}
