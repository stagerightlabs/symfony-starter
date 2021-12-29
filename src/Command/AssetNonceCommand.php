<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'asset:nonce',
    description: 'Generate a new asset nonce for browser cache busting',
)]
class AssetNonceCommand extends Command
{
    /**
     * Configure the command.
     *
     * @return void
     */
    public function configure(): void
    {
        $this->addArgument('key', InputArgument::OPTIONAL, 'The env variable name.', 'ASSET_NONCE');
    }

    /**
     * Run the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $key = $input->getArgument('key');
        $files = ['.env.prod.local', '.env.prod', '.env.local', '.env'];

        // Find the appropriate .env file
        $path = null;
        foreach ($files as $filename) {
            if (file_exists($filename)) {
                $path = $filename;
                break;
            }
        }

        // Do we have a valid .env file?
        if (!$path) {
            $io->error("A valid .env file could not be found.");
            return 1;
        }

        // Read the contents of the .env file
        $content = file_get_contents($path);

        // Ensure the variable key is present in the .env file
        if (!strpos($content, $key)) {
            $io->error("The '{$key}' key is not present in the {$path} file.");
            return 1;
        }

        // Generate a new nonce
        $nonce = uniqid();

        // Write the new asset version value to the .env file
        file_put_contents($path, preg_replace(
            $this->keyReplacementPattern($key, $_ENV[$key]),
            $key . '=' . $nonce,
            $content
        ));

        $io->success("Asset nonce updated in {$path}: '{$nonce}'");

        return 0;
    }

    /**
     * Generate a search needle for preg_replace.
     *
     * @param string $key
     * @param string $current
     * @return string
     */
    protected function keyReplacementPattern($key, $current): string
    {
        $escaped =  preg_quote('=' . $current);

        return "/^{$key}{$escaped}/m";
    }
}
