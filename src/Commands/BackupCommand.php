<?php

namespace WPManager\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use WPManager\Services\BackupService;

class BackupCommand extends Command
{
    protected static $defaultName = 'backup';

    protected function configure()
    {
        $this
            ->setDescription('Realiza backup do WordPress')
            ->setHelp('Este comando faz backup do banco de dados e arquivos do WordPress')
            ->addOption(
                'files-only',
                'f',
                InputOption::VALUE_NONE,
                'Fazer backup apenas dos arquivos'
            )
            ->addOption(
                'database-only',
                'd',
                InputOption::VALUE_NONE,
                'Fazer backup apenas do banco de dados'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Iniciando backup do WordPress...</info>');

        $backupService = new BackupService();

        if (!$input->getOption('database-only')) {
            $output->writeln('Fazendo backup dos arquivos...');
            $backupService->backupFiles();
        }

        if (!$input->getOption('files-only')) {
            $output->writeln('Fazendo backup do banco de dados...');
            $backupService->backupDatabase();
        }

        $output->writeln('<info>Backup concluído com sucesso!</info>');

        return Command::SUCCESS;
    }
} 