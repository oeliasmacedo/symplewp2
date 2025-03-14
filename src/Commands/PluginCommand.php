<?php

namespace WPManager\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use WPManager\Services\PluginService;

class PluginCommand extends Command
{
    protected static $defaultName = 'plugins';
    private $pluginService;

    public function __construct()
    {
        parent::__construct();
        $this->pluginService = new PluginService();
    }

    protected function configure()
    {
        $this
            ->setDescription('Gerencia plugins do WordPress')
            ->setHelp('Este comando permite listar, instalar, atualizar e remover plugins')
            ->addArgument(
                'action',
                InputArgument::OPTIONAL,
                'Ação a ser executada (list, install, update, remove)',
                'list'
            )
            ->addOption(
                'plugin',
                'p',
                InputOption::VALUE_REQUIRED,
                'Nome do plugin para instalar, atualizar ou remover'
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Atualizar todos os plugins'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');
        $pluginName = $input->getOption('plugin');
        $updateAll = $input->getOption('all');

        switch ($action) {
            case 'list':
                $this->listPlugins($output);
                break;
            
            case 'install':
                if (!$pluginName) {
                    $output->writeln('<error>É necessário especificar o nome do plugin para instalar</error>');
                    return Command::FAILURE;
                }
                $this->installPlugin($pluginName, $output);
                break;
            
            case 'update':
                if ($updateAll) {
                    $this->updateAllPlugins($output);
                } elseif ($pluginName) {
                    $this->updatePlugin($pluginName, $output);
                } else {
                    $output->writeln('<error>Especifique um plugin ou use --all para atualizar todos</error>');
                    return Command::FAILURE;
                }
                break;
            
            case 'remove':
                if (!$pluginName) {
                    $output->writeln('<error>É necessário especificar o nome do plugin para remover</error>');
                    return Command::FAILURE;
                }
                $this->removePlugin($pluginName, $output);
                break;
            
            default:
                $output->writeln('<error>Ação inválida</error>');
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function listPlugins(OutputInterface $output)
    {
        $plugins = $this->pluginService->listPlugins();
        
        $table = new Table($output);
        $table->setHeaders(['Nome', 'Versão', 'Status', 'Atualizações']);
        
        foreach ($plugins as $plugin) {
            $table->addRow([
                $plugin['name'],
                $plugin['version'],
                $plugin['status'],
                $plugin['update_available'] ? 'Disponível' : 'Atualizado'
            ]);
        }
        
        $table->render();
    }

    private function installPlugin(string $pluginName, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Instalando plugin %s...</info>', $pluginName));
        
        try {
            $this->pluginService->installPlugin($pluginName);
            $output->writeln('<info>Plugin instalado com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao instalar plugin: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function updatePlugin(string $pluginName, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Atualizando plugin %s...</info>', $pluginName));
        
        try {
            $this->pluginService->updatePlugin($pluginName);
            $output->writeln('<info>Plugin atualizado com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao atualizar plugin: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function updateAllPlugins(OutputInterface $output)
    {
        $output->writeln('<info>Atualizando todos os plugins...</info>');
        
        try {
            $this->pluginService->updateAllPlugins();
            $output->writeln('<info>Todos os plugins foram atualizados com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao atualizar plugins: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function removePlugin(string $pluginName, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Removendo plugin %s...</info>', $pluginName));
        
        try {
            $this->pluginService->removePlugin($pluginName);
            $output->writeln('<info>Plugin removido com sucesso!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Erro ao remover plugin: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }
} 