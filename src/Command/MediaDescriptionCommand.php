<?php

declare(strict_types=1);

namespace EwmOpenaiBundle\Command;

use EwmOpenaiBundle\Service\LoggerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MediaDescriptionCommand extends Command
{
    protected static $defaultName = 'media:create-description';

    public function __construct(private readonly LoggerService $loggerService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Creates descriptions for media using open ai.')
            ->setHelp('This command creates descriptions for media using open ai.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->writeln('<info>Execution of the command from bundle.</info>');
            $this->loggerService::logMessage('Execution of the command from bundle.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>An error occurred: ' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }
    }
}
