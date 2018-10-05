<?php
namespace Numerique1\Components\Restresources\Service;

use Numerique1\Kernel;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;


class ResourceFileProvider
{

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $kernel = $container->get('kernel');
        $this->kernelRootDir = $kernel->getRootDir();
        $this->cacheDir = $kernel->getCacheDir();
        $this->logger = $logger;
    }

    public function __call($method, $args)
    {
        return array_key_exists($method, self::get($args[0])) ? self::get($args[0])[$method] : null;
    }

    public function getFromResource($resource, $cache = false){
        return $this->get($resource, 'resource', $cache);
    }

    public function get($needle, $from = 'class', $cache = false)
    {
        if ($cache)
        {
            if (is_file($cacheFile = $this->cacheDir . "/Restresources_{$from}_Cache.php"))
            {
                $cacheContent = require($cacheFile);
                if (isset($cacheContent[$from]))
                {
                    return $cacheContent[$from];
                }
                else
                {
                    $this->logger->info('Resource "' . $from . '" couldn\'t be found in cache.');
                }
            }
            else
            {
                $this->logger->info('Resources couldn\'t be loaded from cache.');
            }
        }

        $dir = $this->kernelRootDir . '/*/Resources/*';

        $parser = new Parser();
        $finder = new Finder();
        $finder->ignoreUnreadableDirs()
            ->in($dir)
            ->depth('== 0')
            ->files()
            ->name('*.resource.yml');

        foreach ($finder as $file)
        {
            $content = $parser->parse(file_get_contents($file->getRealPath()));
            if ($needle === $content[$from])
            {
                return $content;
            }
        }

        throw new \Exception(sprintf("No file found for class : %s", $needle));
    }


}