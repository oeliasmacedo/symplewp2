<?php

namespace WPManager\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use WPManager\Services\ThemeService;

class ThemeCommand extends Command
{
    protected static $defaultName = 'themes';
    private $themeService;

    public function __construct()
    {
        parent::__construct();
        $this->themeService = new ThemeService();
    }

    protected function configure()
    {
        $this
            ->setDescription('Gerencia temas do WordPress')
            ->setHelp('Este comando permite listar, instalar, atualizar e remover temas')
            ->addArgument(
                'action',
                InputArgument::OPTIONAL,
                'Ação a ser executada (list, install, update, remove, activate)',
                'list'
            )
            ->addOption(
                'theme',
                't',
                InputOption::VALUE_REQUIRED,
                'Nome do tema para instalar, atualizar, remover ou ativar'
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Atualizar todos os temas'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');
        $themeName = $input->getOption('theme');
        $updateAll = $input->getOption('all');

        switch ($action) {
            case 'list':
                $this->listThemes($output);
                break;
            
            case 'install':
                if (!$themeName) {
                    $output->writeln('<error>É necessário especificar o nome do tema para instalar</error>');
                    return Command::FAILURE;
                }
                $this->installTheme($themeName, $output);
                break;
            
            case 'update':
                if ($updateAll) {
                    $this->updateAllThemes($output);
                } elseif ($themeName) {
                    $this->updateTheme($themeName, $output);
                } else {
                    $output->writeln('<error>Especifique um tema ou use --all para atualizar todos</error>');
                    return Command::FAILURE;
                }
                break;
            
            case 'remove':
                if (!$themeName) {
                    $output->writeln('<error>É necessário especificar o nome do tema para remover</error>');
                    return Command::FAILURE;
                }
                $this->removeTheme($themeName, $output);
                break;

            case 'activate':
                if (!$themeName) {
                    $output->writeln('<error>É necessário especificar o nome do tema para ativar</error>');
                    return Command::FAILURE;
                }
                $this->activateTheme($themeName, $output);
                break;
            
            default:
                $output->writeln('<error>Ação inválida</error>');
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function listThemes(OutputInterface $output)
    {
        $themes = $this->themeService->listThemes();
        
        $table = new Table($output);
        $table->setHeaders(['Nome', 'Versão', 'Status', 'Atualizações']);
        
        foreach ($themes as $theme) {
            $table->addRow([
                $theme['name'],
                $theme['version'],
                $theme['status'],
                $theme['update_available'] ? 'Disponível' : 'Atualizado'
            ]);
        }
        
        $table->render();
    }

    private function installTheme(string $themeName, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Instalando tema %s...</info>', $themeName));
        
        try {
            $this->themeService->installTheme($themeName);
            $output->writeln('<info>Tema instalado com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao instalar tema: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function updateTheme(string $themeName, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Atualizando tema %s...</info>', $themeName));
        
        try {
            $this->themeService->updateTheme($themeName);
            $output->writeln('<info>Tema atualizado com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao atualizar tema: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function updateAllThemes(OutputInterface $output)
    {
        $output->writeln('<info>Atualizando todos os temas...</info>');
        
        try {
            $this->themeService->updateAllThemes();
            $output->writeln('<info>Todos os temas foram atualizados com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao atualizar temas: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function removeTheme(string $themeName, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Removendo tema %s...</info>', $themeName));
        
        try {
            $this->themeService->removeTheme($themeName);
            $output->writeln('<info>Tema removido com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao remover tema: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function activateTheme(string $themeName, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Ativando tema %s...</info>', $themeName));
        
        try {
            $this->themeService->activateTheme($themeName);
            $output->writeln('<info>Tema ativado com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao ativar tema: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }
} 