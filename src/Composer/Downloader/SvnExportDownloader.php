<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Downloader;

use Composer\Package\PackageInterface;
use Composer\Util\Svn as SvnUtil;
use Composer\Repository\VcsRepository;

/**
 * @author Jason Abraham <jason@linearsoft.com>
 */
class SvnExportDownloader extends SvnDownloader
{
    protected $cacheCredentials = true;

    /**
     * {@inheritDoc}
     */
    public function doDownload(PackageInterface $package, $path, $url)
    {
        SvnUtil::cleanEnv();
        $ref = $package->getSourceReference();

        $repo = $package->getRepository();
        if ($repo instanceof VcsRepository) {
            $repoConfig = $repo->getRepoConfig();
            if (array_key_exists('svn-cache-credentials', $repoConfig)) {
                $this->cacheCredentials = (bool) $repoConfig['svn-cache-credentials'];
            }
        }

        $this->io->writeError("    Exporting ".$package->getSourceReference());
        $this->execute($url, "svn export --force", sprintf("%s/%s", $url, $ref), null, $path);
    }

    /**
     * {@inheritDoc}
     */
    public function doUpdate(PackageInterface $initial, PackageInterface $target, $path, $url)
    {
        if (!$this->filesystem->removeDirectory($path)) {
            throw new \RuntimeException('Could not completely delete '.$path.', aborting.');
        }
        //Choo
        $this->doDownload($target,$path,$url);
    }

    /**
     * {@inheritDoc}
     */
    public function getLocalChanges(PackageInterface $package, $path)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    protected function cleanChanges(PackageInterface $package, $path, $update)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    protected function getCommitLogs($fromReference, $toReference, $path)
    {
        return null;
    }

    protected function discardChanges($path)
    {
        return;
    }
}
