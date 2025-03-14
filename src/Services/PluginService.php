<?php

namespace WPManager\Services;

use Exception;

class PluginService
{
    private $wpCliPath;

    public function __construct()
    {
        $this->wpCliPath = 'wp';
    }

    public function listPlugins(): array
    {
        $command = sprintf('%s plugin list --format=json', $this->wpCliPath);
        $output = [];
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception('Erro ao listar plugins');
        }

        $plugins = json_decode(implode('', $output), true);
        if (!is_array($plugins)) {
            throw new Exception('Erro ao decodificar lista de plugins');
        }

        return array_map(function($plugin) {
            return [
                'name' => $plugin['name'],
                'version' => $plugin['version'],
                'status' => $plugin['status'],
                'update_available' => $plugin['update'] === 'available'
            ];
        }, $plugins);
    }

    public function installPlugin(string $pluginName): void
    {
        $command = sprintf('%s plugin install %s --activate', 
            $this->wpCliPath,
            escapeshellarg($pluginName)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception(sprintf('Erro ao instalar plugin %s', $pluginName));
        }
    }

    public function updatePlugin(string $pluginName): void
    {
        $command = sprintf('%s plugin update %s', 
            $this->wpCliPath,
            escapeshellarg($pluginName)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception(sprintf('Erro ao atualizar plugin %s', $pluginName));
        }
    }

    public function updateAllPlugins(): void
    {
        $command = sprintf('%s plugin update --all', $this->wpCliPath);
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception('Erro ao atualizar todos os plugins');
        }
    }

    public function removePlugin(string $pluginName): void
    {
        $command = sprintf('%s plugin deactivate %s && %s plugin uninstall %s', 
            $this->wpCliPath,
            escapeshellarg($pluginName),
            $this->wpCliPath,
            escapeshellarg($pluginName)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception(sprintf('Erro ao remover plugin %s', $pluginName));
        }
    }
} 