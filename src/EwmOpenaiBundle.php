<?php

namespace EwmOpenaiBundle;

use EwmOpenaiBundle\DependencyInjection\OpenaiExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class EwmOpenaiBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?Extension
    {
        return new OpenaiExtension();
    }
}
