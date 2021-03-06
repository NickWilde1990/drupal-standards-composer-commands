<?php

namespace NickWilde1990\DrupalStandardsCommands\Command;

use Composer\IO\IOInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a phpcs command for use in drupal projects.
 */
class DrupalPhpcsRunner extends BaseRunner
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('drupal-phpcs');
        $this->setAliases(['cs-php-scan']);
        $this->setDescription('Scan for PHP Standard errors.');
        $this->bin = 'vendor/bin/phpcs';
        $this->name= 'PHPCS';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getIO();
        $package = $this->getComposer()->getPackage();
        $extra = $package->getExtra();
        $command = $this->bin;

        $result = 0;
        $output = [];
        exec('test -e phpcs.xml*', $output, $result);

        // If phpcs.xml/phpcs.xml.dist exists use that otherwise set defaults.
        if (!$result) {
            $io->write('Using existing PHPCS config file.');
        } else {
            if (isset($extra['drupal-standards']['phpcs'])) {
                $standard = $extra['drupal-standards']['phpcs'];
            } else {
                $standard = "Drupal";
            }
            $io->write("No PHPCS config file found. Using standard: {$standard}.");
            $command .= " --standard={$standard}";
            $command .= ' --extensions=php,module,inc,install,test,profile,theme,info,txt,md';

            if ($paths = $this->getExtra('ignore-paths', null, ['core'])) {
                $paths = ',' . implode(',', $paths);
            }
            $command .= " --ignore=vendor,*/node_modules/*,*/bower_components/*{$paths}";
        }

        $io->write("Running {$this->name}");
        $io->write("Command: {$command}", true, IOInterface::VERY_VERBOSE);
        exec($command . ' .', $output, $result);
        if ($result) {
            $io->write('PHPCS found errors:');
            $io->writeError($output);
            exit(1);
        }
        $io->write($output);
        $io->write("{$this->name} completed successfully.");
    }
}
