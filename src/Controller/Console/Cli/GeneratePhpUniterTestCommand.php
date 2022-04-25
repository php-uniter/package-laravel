<?php

namespace PhpUniter\PackageLaravel\Controller\Console\Cli;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use PhpUniter\PackageLaravel\Application\File\Exception\FileNameNotString;
use PhpUniter\PackageLaravel\Application\File\Exception\FileNotAccessed;
use PhpUniter\PackageLaravel\Application\File\LocalFileFabric;
use PhpUniter\PackageLaravel\Application\PhpUniter\Entity\PhpUnitTest;
use PhpUniter\PackageLaravel\Application\PhpUnitService;
use Throwable;

class GeneratePhpUniterTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'php-uniter:generate {filePath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate phpunit test';

    /**
     * Execute the console command.
     */
    public function handle(PhpUnitService $phpUnitService, LocalFileFabric $localFileFabric): ?int
    {
        try {
            $filePath = $this->argument('filePath');

            if (!is_string($filePath)) {
                throw new FileNameNotString('File path argument is not a string');
            }

            if (!is_readable($filePath)) {
                throw new FileNotAccessed("File $filePath was not found");
            }

            try {
                $file = $localFileFabric::createFile($filePath);
                /** @var PhpUnitTest $phpUnitTest */
                $phpUnitTest = $phpUnitService->process($file);
                $this->info('Generated test was written to '.$phpUnitTest->getPathToTest());
            } catch (GuzzleException $e) {
                $this->error($e->getMessage());

                return 1;
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return 1;
        }

        return 0;
    }
}
