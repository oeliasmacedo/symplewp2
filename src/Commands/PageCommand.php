<?php

namespace WPManager\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use WPManager\Services\PageService;

class PageCommand extends Command
{
    protected static $defaultName = 'pages';
    private $pageService;

    public function __construct()
    {
        parent::__construct();
        $this->pageService = new PageService();
    }

    protected function configure()
    {
        $this
            ->setDescription('Gerencia páginas do WordPress')
            ->setHelp('Este comando permite listar, criar, editar e remover páginas')
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
                'ID da página para editar ou remover'
            )
            ->addOption(
                'title',
                't',
                InputOption::VALUE_REQUIRED,
                'Título da página'
            )
            ->addOption(
                'content',
                'c',
                InputOption::VALUE_REQUIRED,
                'Conteúdo da página'
            )
            ->addOption(
                'status',
                's',
                InputOption::VALUE_REQUIRED,
                'Status da página (publish, draft, private)',
                'publish'
            )
            ->addOption(
                'parent',
                'p',
                InputOption::VALUE_REQUIRED,
                'ID da página pai (para criar hierarquia)'
            )
            ->addOption(
                'template',
                null,
                InputOption::VALUE_REQUIRED,
                'Template da página'
            )
            ->addOption(
                'menu-order',
                'm',
                InputOption::VALUE_REQUIRED,
                'Ordem no menu'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');

        switch ($action) {
            case 'list':
                $this->listPages($output);
                break;
            
            case 'create':
                $this->createPage($input, $output);
                break;
            
            case 'edit':
                $this->editPage($input, $output);
                break;
            
            case 'delete':
                $this->deletePage($input, $output);
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

    private function listPages(OutputInterface $output)
    {
        $pages = $this->pageService->listPages();
        
        $table = new Table($output);
        $table->setHeaders(['ID', 'Título', 'Status', 'Template', 'Pai', 'Ordem']);
        
        foreach ($pages as $page) {
            $table->addRow([
                $page['ID'],
                $page['title'],
                $page['status'],
                $page['template'] ?: 'default',
                $page['parent'] ?: '-',
                $page['menu_order']
            ]);
        }
        
        $table->render();
    }

    private function createPage(InputInterface $input, OutputInterface $output)
    {
        $title = $input->getOption('title');
        $content = $input->getOption('content');
        
        if (!$title || !$content) {
            $output->writeln('<error>Título e conteúdo são obrigatórios</error>');
            return Command::FAILURE;
        }

        try {
            $page = [
                'title' => $title,
                'content' => $content,
                'status' => $input->getOption('status'),
                'parent' => $input->getOption('parent'),
                'template' => $input->getOption('template'),
                'menu_order' => $input->getOption('menu-order')
            ];

            $id = $this->pageService->createPage($page);
            $output->writeln(sprintf('<info>Página criada com sucesso! ID: %d</info>', $id));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao criar página: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function editPage(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getOption('id');
        if (!$id) {
            $output->writeln('<error>ID da página é obrigatório</error>');
            return Command::FAILURE;
        }

        try {
            $page = [
                'title' => $input->getOption('title'),
                'content' => $input->getOption('content'),
                'status' => $input->getOption('status'),
                'parent' => $input->getOption('parent'),
                'template' => $input->getOption('template'),
                'menu_order' => $input->getOption('menu-order')
            ];

            $this->pageService->editPage($id, array_filter($page));
            $output->writeln('<info>Página atualizada com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao editar página: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function deletePage(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getOption('id');
        if (!$id) {
            $output->writeln('<error>ID da página é obrigatório</error>');
            return Command::FAILURE;
        }

        try {
            $this->pageService->deletePage($id);
            $output->writeln('<info>Página removida com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao remover página: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function changeStatus(InputInterface $input, OutputInterface $output, string $status)
    {
        $id = $input->getOption('id');
        if (!$id) {
            $output->writeln('<error>ID da página é obrigatório</error>');
            return Command::FAILURE;
        }

        try {
            $this->pageService->changeStatus($id, $status);
            $output->writeln(sprintf('<info>Status da página alterado para %s com sucesso!</info>', $status));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao alterar status da página: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }
} 