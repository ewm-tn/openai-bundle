<?php

declare(strict_types=1);

namespace EwmOpenaiBundle\Command;

use EwmOpenaiBundle\Service\LoggerService;
use EwmOpenaiBundle\Service\OpenAIMediaDescriptionGenerator;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MediaDescriptionCommand extends Command
{
    protected static $defaultName = 'media:create-description';
    public const SUPPORTED_LANGUAGES = [
        'en', 'fr', 'de', 'es', 'it',
    ];
    private OpenAIMediaDescriptionGenerator $generator;
    private LoggerService $loggerService;
    private MediaRepositoryInterface $mediaRepository;
    private MediaManagerInterface $mediaManager;
    private WebspaceManagerInterface $webspaceManager;
    public function __construct(
        OpenAIMediaDescriptionGenerator $generator,
        LoggerService $loggerService,
        MediaRepositoryInterface $mediaRepository,
        MediaManagerInterface $mediaManager,
        WebspaceManagerInterface $webspaceManager
    ) {
        parent::__construct();
        $this->generator = $generator;
        $this->loggerService = $loggerService;
        $this->mediaRepository = $mediaRepository;
        $this->mediaManager = $mediaManager;
        $this->webspaceManager = $webspaceManager;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Creates descriptions for media in Sulu project using open ai.')
            ->setHelp('This command creates descriptions for media in Sulu project using open ai.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $hostname = $_ENV['HOSTNAME'];
            if (!$hostname) {
                $output->writeln('<error>HOSTNAME not found on .env</error>');
                $this->loggerService::logMessage('HOSTNAME not found on .env');

                return Command::FAILURE;
            }
            $apiKey = $_ENV['OPEN_API_KEY'];
            if (!$apiKey) {
                $output->writeln('<error>OPEN_API_KEY not found on .env</error>');
                $this->loggerService::logMessage('OPEN_API_KEY not found on .env');

                return Command::FAILURE;
            }
            $locales = $this->webspaceManager->getAllLocales();
            // Test if a locale is not among supported languages
            foreach ($locales as $locale) {
                if (!\in_array($locale, self::SUPPORTED_LANGUAGES, true)) {
                    $output->writeln("<error>Locale '{$locale}' is not supported.</error>");
                    $this->loggerService::logMessage("Locale '{$locale}' is not supported.");
                }
            }
            // Take into account only locales that are supported
            $langues = \array_intersect($locales, self::SUPPORTED_LANGUAGES);
            foreach ($langues as $langue) {
                $media = $this->mediaRepository->findAll();
                $totalMedia = \count($media);
                $progressBar = new ProgressBar($output, $totalMedia);
                $progressBar->setFormat(
                    '%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% %message%',
                );
                $progressBar->setMessage("Processing language: {$langue}");
                $progressBar->start();

                $processedCount = 0;
                foreach ($media as $m) {
                    $mediaLangue = null;
                    if (\str_contains($m->getFiles()->first()->getFileVersions()->first()->getMimeType(), 'image')) {
                        $mediaId = $m->getId();
                        $mediaLangue = $this->mediaManager->getById($mediaId, $langue);
                        if (empty($mediaLangue->getDescription())) {
                            $filename = $m->getFiles()->first()->getFileVersions()->first()->getName();
                            $title = $mediaLangue->getTitle() ?? \pathinfo($filename, \PATHINFO_FILENAME);
                            $publicUrl = $this->mediaManager->getUrl($mediaId, $filename, true);
                            $publicUrl = $hostname . $publicUrl . '&inline=1';
                            $generated = $this->generator->generateDescription($publicUrl, $langue);
                            if ($generated) {
                                $generated = \str_replace(array('“', '"'), '', $generated);
                                $this->mediaManager->save(null, [
                                    'id' => $mediaId,
                                    'locale' => $langue,
                                    'title' => $title,
                                    'description' => $generated,
                                ], null);
                                $this->loggerService::logMessage("{$mediaId} processed for language {$langue}");
                                ++$processedCount;
                            }
                            $generated = null;
                            $progressBar->advance();
                        }
                    }
                }

                $progressBar->setMessage('Completed');
                $progressBar->finish();
                $output->writeln("\n<info>Processed {$processedCount} media items out of {$totalMedia}</info>");
                $this->loggerService::logMessage("Finished. Processed {$processedCount} media items.");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            if (isset($progressBar)) {
                $progressBar->clear();
            }
            $output->writeln('<error>An error occurred: ' . $e->getMessage() . '</error>');
            $this->loggerService::logMessage('Command failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
