<?php

namespace WPManager\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use WPManager\Services\PostService;

class PostCommand extends Command
{
    protected static $defaultName = 'posts';
    private $postService;

    public function __construct()
    {
        parent::__construct();
        $this->postService = new PostService();
    }

    protected function configure()
    {
        $this
            ->setDescription('Gerencia posts do WordPress')
            ->setHelp('Este comando permite listar, criar, editar e remover posts')
            ->addArgument(
                'action',
                InputArgument::OPTIONAL,
                'Ação a ser executada (list, create, edit, delete, publish, draft)',
                'list'
            )
            ->addOption(
                'id',
                'i',
                InputOption::VALUE_REQUIRED,
                'ID do post para editar ou remover'
            )
            ->addOption(
                'title',
                't',
                InputOption::VALUE_REQUIRED,
                'Título do post'
            )
            ->addOption(
                'content',
                'c',
                InputOption::VALUE_REQUIRED,
                'Conteúdo do post'
            )
            ->addOption(
                'status',
                's',
                InputOption::VALUE_REQUIRED,
                'Status do post (publish, draft, pending, private)',
                'publish'
            )
            ->addOption(
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'Categoria do post'
            )
            ->addOption(
                'tags',
                null,
                InputOption::VALUE_REQUIRED,
                'Tags do post (separadas por vírgula)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');

        switch ($action) {
            case 'list':
                $this->listPosts($output);
                break;
            
            case 'create':
                $this->createPost($input, $output);
                break;
            
            case 'edit':
                $this->editPost($input, $output);
                break;
            
            case 'delete':
                $this->deletePost($input, $output);
                break;
            
            case 'publish':
                $this->changeStatus($input, $output, 'publish');
                break;
            
            case 'draft':
                $this->changeStatus($input, $output, 'draft');
                break;
            
            default:
                $output->writeln('<error>Ação inválida</error>');
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function listPosts(OutputInterface $output)
    {
        $posts = $this->postService->listPosts();
        
        $table = new Table($output);
        $table->setHeaders(['ID', 'Título', 'Status', 'Data', 'Autor']);
        
        foreach ($posts as $post) {
            $table->addRow([
                $post['ID'],
                $post['title'],
                $post['status'],
                $post['date'],
                $post['author']
            ]);
        }
        
        $table->render();
    }

    private function createPost(InputInterface $input, OutputInterface $output)
    {
        $title = $input->getOption('title');
        $content = $input->getOption('content');
        
        if (!$title || !$content) {
            $output->writeln('<error>Título e conteúdo são obrigatórios</error>');
            return Command::FAILURE;
        }

        try {
            $post = [
                'title' => $title,
                'content' => $content,
                'status' => $input->getOption('status'),
                'category' => $input->getOption('category'),
                'tags' => $input->getOption('tags')
            ];

            $id = $this->postService->createPost($post);
            $output->writeln(sprintf('<info>Post criado com sucesso! ID: %d</info>', $id));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao criar post: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function editPost(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getOption('id');
        if (!$id) {
            $output->writeln('<error>ID do post é obrigatório</error>');
            return Command::FAILURE;
        }

        try {
            $post = [
                'title' => $input->getOption('title'),
                'content' => $input->getOption('content'),
                'status' => $input->getOption('status'),
                'category' => $input->getOption('category'),
                'tags' => $input->getOption('tags')
            ];

            $this->postService->editPost($id, array_filter($post));
            $output->writeln('<info>Post atualizado com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao editar post: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function deletePost(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getOption('id');
        if (!$id) {
            $output->writeln('<error>ID do post é obrigatório</error>');
            return Command::FAILURE;
        }

        try {
            $this->postService->deletePost($id);
            $output->writeln('<info>Post removido com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao remover post: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function changeStatus(InputInterface $input, OutputInterface $output, string $status)
    {
        $id = $input->getOption('id');
        if (!$id) {
            $output->writeln('<error>ID do post é obrigatório</error>');
            return Command::FAILURE;
        }

        try {
            $this->postService->changeStatus($id, $status);
            $output->writeln(sprintf('<info>Status do post alterado para %s com sucesso!</info>', $status));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao alterar status do post: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }
} 