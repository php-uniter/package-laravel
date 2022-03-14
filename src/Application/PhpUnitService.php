<?php

namespace PhpUniter\PackageLaravel\Application;

use PhpUniter\PackageLaravel\Application\File\Entity\ClassFile;
use PhpUniter\PackageLaravel\Application\File\Entity\LocalFile;
use PhpUniter\PackageLaravel\Application\File\Exception\DirectoryPathWrong;
use PhpUniter\PackageLaravel\Application\File\Exception\FileNotAccessed;
use PhpUniter\PackageLaravel\Application\File\Exception\RequestFail;
use PhpUniter\PackageLaravel\Application\Obfuscator\Obfuscator;
use PhpUniter\PackageLaravel\Application\Obfuscator\ObfuscatorFabric;
use PhpUniter\PackageLaravel\Application\PhpUniter\Entity\PhpUnitTest;
use PhpUniter\PackageLaravel\Infrastructure\Integrations\PhpUniterIntegration;

class PhpUnitService
{
    private Placer $testPlacer;
    private PhpUniterIntegration $phpUniterIntegration;

    public function __construct(PhpUniterIntegration $phpUniterIntegration, Placer $testPlacer)
    {
        $this->phpUniterIntegration = $phpUniterIntegration;
        $this->testPlacer = $testPlacer;
    }

    /**
     * @throws DirectoryPathWrong
     * @throws FileNotAccessed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws RequestFail
     */
    public function process(LocalFile $file, array $options): PhpUnitTest
    {
        $obfuscateble = ClassFile::get($file);
        $obfuscator = ObfuscatorFabric::getObfuscated($obfuscateble);
        $uploadFile = $obfuscator->getObfuscated();
        $phpUnitTest = $this->phpUniterIntegration->generatePhpUnitTest($uploadFile, $options);
        $testText = $phpUnitTest->getUnitTest();
        $phpUnitTest->setUnitTest($obfuscator->deObfuscate($testText));
        $this->testPlacer->place($phpUnitTest);

        return $phpUnitTest;
    }
}
