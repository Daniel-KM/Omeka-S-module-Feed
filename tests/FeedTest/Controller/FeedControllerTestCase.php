<?php declare(strict_types=1);

namespace FeedTest\Controller;

use CommonTest\AbstractHttpControllerTestCase;

abstract class FeedControllerTestCase extends AbstractHttpControllerTestCase
{
    protected $site;

    public function setUp(): void
    {
        // Feed controller needs a valid HTTP host for canonical URLs.
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_SCHEME'] = 'http';

        parent::setUp();

        $this->login('admin@example.com', 'root');

        $response = $this->api()->create('sites', [
            'o:title' => 'Test site',
            'o:slug' => 'test',
            'o:theme' => 'default',
        ]);
        $this->site = $response->getContent();

        $siteSettings = $this->getServiceLocator()
            ->get('Omeka\Settings\Site');
        $siteSettings->setTargetId($this->site->id());

        $settings = $this->getServiceLocator()->get('Omeka\Settings');
        $settings->set('default_site', $this->site->id());

        $this->resetApplication();
        $this->login('admin@example.com', 'root');
    }

    public function tearDown(): void
    {
        $this->login('admin@example.com', 'root');

        $settings = $this->getServiceLocator()->get('Omeka\Settings');
        $settings->delete('default_site');

        if ($this->site) {
            try {
                $this->api()->delete('sites', $this->site->id());
            } catch (\Exception $e) {
            }
            $this->site = null;
        }

        parent::tearDown();
    }

    /**
     * Override dispatch to handle re-authentication after reset
     * and set server name for canonical URLs.
     */
    public function dispatch($url, $method = null, $params = [], $isXmlHttpRequest = false)
    {
        $this->reset();
        $this->getApplication();

        if ($this->requiresLogin) {
            $this->login('admin@example.com', 'root');
        }

        \Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase::dispatch($url, $method, $params, $isXmlHttpRequest);
    }

    protected function getServiceLocator()
    {
        return $this->getApplicationServiceLocator();
    }

    protected function resetApplication(): void
    {
        $this->reset();
    }

    /**
     * Login using the authentication adapter.
     */
    protected function login(string $email, string $password): void
    {
        $services = $this->getApplicationServiceLocator();
        $auth = $services->get('Omeka\AuthenticationService');
        $adapter = $auth->getAdapter();
        $adapter->setIdentity($email);
        $adapter->setCredential($password);
        $auth->authenticate();
    }

    /**
     * Check if AdvancedSearch module is active.
     */
    protected function hasAdvancedSearch(): bool
    {
        $services = $this->getServiceLocator();
        $moduleManager = $services->get('Omeka\ModuleManager');
        $module = $moduleManager->getModule('AdvancedSearch');
        return $module
            && $module->getState() === \Omeka\Module\Manager::STATE_ACTIVE;
    }
}
