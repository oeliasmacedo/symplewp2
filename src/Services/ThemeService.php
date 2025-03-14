<?php

namespace WPManager\Services;

use Exception;

class ThemeService
{
    private $wpCliPath;

    public function __construct()
    {
        $this->wpCliPath = 'wp';
    }

    public function listThemes(): array
    {
        $command = sprintf('%s theme list --format=json', $this->wpCliPath);
        $output = [];
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception('Erro ao listar temas');
        }

        $themes = json_decode(implode('', $output), true);
        if (!is_array($themes)) {
            throw new Exception('Erro ao decodificar lista de temas');
        }

        return array_map(function($theme) {
            return [
                'name' => $theme['name'],
                'version' => $theme['version'],
                'status' => $theme['status'],
                'update_available' => $theme['update'] === 'available'
            ];
        }, $themes);
    }

    public function installTheme(string $themeName): void
    {
        $command = sprintf('%s theme install %s', 
            $this->wpCliPath,
            escapeshellarg($themeName)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception(sprintf('Erro ao instalar tema %s', $themeName));
        }
    }

    public function updateTheme(string $themeName): void
    {
        $command = sprintf('%s theme update %s', 
            $this->wpCliPath,
            escapeshellarg($themeName)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception(sprintf('Erro ao atualizar tema %s', $themeName));
        }
    }

    public function updateAllThemes(): void
    {
        $command = sprintf('%s theme update --all', $this->wpCliPath);
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception('Erro ao atualizar todos os temas');
        }
    }

    public function removeTheme(string $themeName): void
    {
        $command = sprintf('%s theme delete %s', 
            $this->wpCliPath,
            escapeshellarg($themeName)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception(sprintf('Erro ao remover tema %s', $themeName));
        }
    }

    public function activateTheme(string $themeName): void
    {
        $command = sprintf('%s theme activate %s', 
            $this->wpCliPath,
            escapeshellarg($themeName)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception(sprintf('Erro ao ativar tema %s', $themeName));
        }
    }
} 