<?php

namespace Fuguevit\Storage\Plugins;

use League\Flysystem\Config;
use League\Flysystem\Plugin\AbstractPlugin;

class PutFile extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return 'putFile';
    }

    /**
     * @param $path
     * @param $filePath
     * @param array $options
     *
     * @return bool
     */
    public function handle($path, $filePath, array $options = [])
    {
        $config = new Config($options);
        if (method_exists($this->filesystem, 'getConfig')) {
            $config->setFallback($this->filesystem->getConfig());
        }

        return (bool) $this->filesystem->getAdapter()->writeFile($path, $filePath, $config);
    }
}
