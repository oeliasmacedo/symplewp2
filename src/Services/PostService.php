<?php

namespace WPManager\Services;

use Exception;

class PostService
{
    private $wpCliPath;

    public function __construct()
    {
        $this->wpCliPath = 'wp';
    }

    public function listPosts(): array
    {
        $command = sprintf('%s post list --format=json', $this->wpCliPath);
        $output = [];
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception('Erro ao listar posts');
        }

        $posts = json_decode(implode('', $output), true);
        if (!is_array($posts)) {
            throw new Exception('Erro ao decodificar lista de posts');
        }

        return array_map(function($post) {
            return [
                'ID' => $post['ID'],
                'title' => $post['post_title'],
                'status' => $post['post_status'],
                'date' => $post['post_date'],
                'author' => $post['post_author']
            ];
        }, $posts);
    }

    public function createPost(array $post): int
    {
        $command = sprintf(
            '%s post create --porcelain --title=%s --content=%s --post_status=%s',
            $this->wpCliPath,
            escapeshellarg($post['title']),
            escapeshellarg($post['content']),
            escapeshellarg($post['status'])
        );

        if (!empty($post['category'])) {
            $command .= sprintf(' --post_category=%s', escapeshellarg($post['category']));
        }

        if (!empty($post['tags'])) {
            $command .= sprintf(' --tags_input=%s', escapeshellarg($post['tags']));
        }

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception('Erro ao criar post');
        }

        return (int) $output[0];
    }

    public function editPost(int $id, array $post): void
    {
        $command = sprintf('%s post update %d', $this->wpCliPath, $id);

        if (isset($post['title'])) {
            $command .= sprintf(' --post_title=%s', escapeshellarg($post['title']));
        }

        if (isset($post['content'])) {
            $command .= sprintf(' --post_content=%s', escapeshellarg($post['content']));
        }

        if (isset($post['status'])) {
            $command .= sprintf(' --post_status=%s', escapeshellarg($post['status']));
        }

        if (isset($post['category'])) {
            $command .= sprintf(' --post_category=%s', escapeshellarg($post['category']));
        }

        if (isset($post['tags'])) {
            $command .= sprintf(' --tags_input=%s', escapeshellarg($post['tags']));
        }

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception(sprintf('Erro ao atualizar post %d', $id));
        }
    }

    public function deletePost(int $id): void
    {
        $command = sprintf('%s post delete %d --force', $this->wpCliPath, $id);
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception(sprintf('Erro ao remover post %d', $id));
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
            throw new Exception(sprintf('Erro ao alterar status do post %d', $id));
        }
    }
} 