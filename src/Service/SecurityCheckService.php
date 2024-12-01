<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SecurityCheckService
{

    private array $securityCheckData;

    public function __construct(
        #[Autowire('%kernel.project_dir%/vendor/bin')]
        private readonly string $vendorPath
    ) {}

    public function getSecurityCheck(string $projectPath): void
    {
        $cmd = "{$this->vendorPath}/symfony security:check --format=json --dir=$projectPath";
        $output = shell_exec($cmd);
        $this->parseSecurityCheckOutput($output);
    }

    public function getSecurityCheckCount(): int
    {
        return count($this->securityCheckData);
    }

    private function parseSecurityCheckOutput(string $output)
    {
        $data = json_decode($output, true);
        $this->securityCheckData = $data;
    }
}
