<?php

declare(strict_types=1);

namespace EwmOpenaiBundle\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class LoggerService
{
    private static string $log;
    private ParameterBagInterface $parameterBag;
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        self::$log = $this->parameterBag->get('log_dir');
    }

    public static function logMessage(string $error): void
    {
        $file = self::$log . '/openai.log';
        if (!\file_exists($file)) {
            \touch($file);
            \chmod($file, 0777);
        }
        \ini_set('error_log', $file);
        \error_log($error);
    }
}
