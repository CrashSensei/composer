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

namespace Composer\IO;

use Composer\Config;
use Composer\Util\ProcessExecutor;

abstract class BaseIO implements IOInterface
{
    protected $authentications = array();

    /**
     * {@inheritDoc}
     */
    public function getAuthentications()
    {
        return $this->authentications;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAuthentication($repositoryName)
    {
        return isset($this->authentications[$repositoryName]);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthentication($repositoryName)
    {
        if (isset($this->authentications[$repositoryName])) {
            return $this->authentications[$repositoryName];
        }

        return array('username' => null, 'password' => null);
    }

    /**
     * {@inheritDoc}
     */
    public function setAuthentication($repositoryName, $username, $password = null)
    {
        $this->authentications[$repositoryName] = array('username' => $username, 'password' => $password);
    }

    /**
     * {@inheritDoc}
     */
    public function loadConfiguration(Config $config)
    {
        $githubOauth = $config->get('github-oauth') ?: array();
        $gitlabOauth = $config->get('gitlab-oauth') ?: array();
        $httpBasic = $config->get('http-basic') ?: array();

        // reload oauth token from config if available
        foreach ($githubOauth as $domain => $token) {
            if (!preg_match('{^[a-z0-9]+$}', $token)) {
                throw new \UnexpectedValueException('Your github oauth token for '.$domain.' contains invalid characters: "'.$token.'"');
            }
            $this->setAuthentication($domain, $token, 'x-oauth-basic');
        }

        foreach ($gitlabOauth as $domain => $token) {
            $this->setAuthentication($domain, $token, 'oauth2');
        }

        // reload http basic credentials from config if available
        foreach ($httpBasic as $domain => $cred) {
            $this->setAuthentication($domain, $cred['username'], $cred['password']);
        }

        // setup process timeout
        ProcessExecutor::setTimeout((int) $config->get('process-timeout'));
    }
}
