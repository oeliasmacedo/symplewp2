<?php

namespace WPManager\Services;

use Exception;

class PageService
{
    private $wpCliPath;

    public function __construct()
    {
        $this->wpCliPath = 'wp';
    }

    public function listPages(): array
    {
        $command = sprintf('%s post list --post_type=page --format=json', $this->wpCliPath);
        $output = [];
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception('Erro ao listar páginas');
        }

        $pages = json_decode(implode('', $output), true);
        if (!is_array($pages)) {
            throw new Exception('Erro ao decodificar lista de páginas');
        }

        return array_map(function($page) {
            return [
                'ID' => $page['ID'],
                'title' => $page['post_title'],
                'status' => $page['post_status'],
                'template' => $page['page_template'] ?? null,
                'parent' => $page['post_parent'],
                'menu_order' => $page['menu_order']
            ];
        }, $pages);
    }

    public function createPage(array $page): int
    {
        $command = sprintf(
            '%s post create --post_type=page --porcelain --post_title=%s --post_content=%s --post_status=%s',
            $this->wpCliPath,
            escapeshellarg($page['title']),
            escapeshellarg($page['content']),
            escapeshellarg($page['status'])
        );

        if (!empty($page['parent'])) {
            $command .= sprintf(' --post_parent=%d', (int)$page['parent']);
        }

        if (!empty($page['template'])) {
            $command .= sprintf(' --page_template=%s', escapeshellarg($page['template']));
        }

        if (!empty($page['menu_order'])) {
            $command .= sprintf(' --menu_order=%d', (int)$page['menu_order']);
        }

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception('Erro ao criar página');
        }

        return (int) $output[0];
    }

    public function editPage(int $id, array $page): void
    {
        $command = sprintf('%s post update %d --post_type=page', $this->wpCliPath, $id);

        if (isset($page['title'])) {
            $command .= sprintf(' --post_title=%s', escapeshellarg($page['title']));
        }

        if (isset($page['content'])) {
            $command .= sprintf(' --post_content=%s', escapeshellarg($page['content']));
        }

        if (isset($page['status'])) {
            $command .= sprintf(' --post_status=%s', escapeshellarg($page['status']));
        }

        if (isset($page['parent'])) {
            $command .= sprintf(' --post_parent=%d', (int)$page['parent']);
        }

        if (isset($page['template'])) {
            $command .= sprintf(' --page_template=%s', escapeshellarg($page['template']));
        }

        if (isset($page['menu_order'])) {
            $command .= sprintf(' --menu_order=%d', (int)$page['menu_order']);
        }

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception(sprintf('Erro ao atualizar página %d', $id));
        }
    }

    public function deletePage(int $id): void
    {
        $command = sprintf('%s post delete %d --force', $this->wpCliPath, $id);
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception(sprintf('Erro ao remover página %d', $id));
        }
    }

    public function changeStatus(int $id, string $status): void
    {
        $command = sprintf(
            '%s post update %d --post_status=%s',
            $this->wpCliPath,
            $id,
            escapeshellarg($status)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception(sprintf('Erro ao alterar status da página %d', $id));
        }
    }
} 