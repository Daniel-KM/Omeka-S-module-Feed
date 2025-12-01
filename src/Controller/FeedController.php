<?php declare(strict_types=1);

namespace Feed\Controller;

use Laminas\Feed\Writer\Feed;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Exception\NotFoundException;
use Omeka\Stdlib\Message;

class FeedController extends AbstractActionController
{
    /**
     * @var PhpRenderer
     */
    protected $viewRenderer;

    /**
     * @var string
     */
    protected $moduleVersion;

    /**
     * @param PhpRenderer $viewRenderer
     * @param string $moduleVersion
     */
    public function __construct(PhpRenderer $viewRenderer, $moduleVersion)
    {
        $this->viewRenderer = $viewRenderer;
        $this->moduleVersion = $moduleVersion;
    }

    public function indexAction()
    {
        return $this->rss('static');
    }

    /**
     * Get rss from search results.
     *
     * Adapted in module AdvancedSearch.
     * @see \AdvancedSearch\Controller\SearchController::rss()
     */
    public function rssAction()
    {
        return $this->rss('dynamic');
    }

    protected function rss(string $mode)
    {
        $type = $this->params()->fromRoute('feed', 'rss');

        /** @var \Omeka\Api\Representation\SiteRepresentation $site */
        $site = $this->currentSite();
        $siteSettings = $this->siteSettings();
        $urlHelper = $this->viewHelpers()->get('url');

        $feed = new Feed;
        $feed
            ->setType($type)
            ->setTitle($site->title())
            ->setLink($site->siteUrl($site->slug(), true))
            // Use rdf because Omeka is Semantic, but "atom" is required when
            // the type is "atom".
            ->setFeedLink($urlHelper('site/feed', ['site-slug' => $site->slug()], ['force_canonical' => true]), $type === 'atom' ? 'atom' : 'rdf')
            ->setGenerator('Omeka S module Feed', $this->moduleVersion, 'https://gitlab.com/Daniel-KM/Omeka-S-module-Feed')
            ->setDateModified(time())
        ;

        $description = $site->summary();
        if ($description) {
            $feed
                ->setDescription($description);
        }
        // The type "rss" requires a description.
        elseif ($type === 'rss') {
            $feed
                ->setDescription($site->title());
        }

        $locale = $siteSettings->get('locale');
        if ($locale) {
            $feed
                ->setLanguage($locale);
        }

        /** @var \Omeka\Api\Representation\AssetRepresentation $asset */
        $asset = $siteSettings->get('feed_logo');
        if (is_numeric($asset)) {
            try {
                $asset = $this->api()->read('assets', ['id' => $asset])->getContent();
            } catch (\Exception $e) {
                $asset = null;
            }
        }
        if (!$asset) {
            $asset = $site->thumbnail();
        }
        if ($asset) {
            $image = [
                'uri' => $asset->assetUrl(),
                'link' => $site->siteUrl(null, true),
                'title' => $this->translate('Logo'),
                // Optional for "rss".
                // 'description' => '',
                // 'height' => '',
                // 'width' => '',
            ];
            $feed->setImage($image);
        }

        $mode === 'static'
            ? $this->appendEntriesStatic($feed)
            : $this->appendEntriesDynamic($feed);

        $content = $feed->export($type);

        $response = $this->getResponse();
        $response->setContent($content);

        /** @var \Laminas\Http\Headers $headers */
        $headers = $response->getHeaders();
        $headers
            ->addHeaderLine('Content-length: ' . strlen($content))
            ->addHeaderLine('Pragma: public');
        // TODO Manage content type requests (atom/rss).
        // Note: normally, application/rss+xml is the standard one, but text/xml
        // may be more compatible.
        if ($siteSettings->get('feed_media_type', 'standard') === 'xml') {
            $headers
                ->addHeaderLine('Content-type: ' . 'text/xml; charset=UTF-8');
        } else {
            $headers
                ->addHeaderLine('Content-type: ' . 'application/' . $type . '+xml; charset=UTF-8');
        }

        $contentDisposition = $siteSettings->get('feed_disposition', 'attachment');
        switch ($contentDisposition) {
            case 'undefined':
                break;
            case 'inline':
                $headers
                    ->addHeaderLine('Content-Disposition', 'inline');
                break;
            case 'attachment':
            default:
                $filename = 'feed-' . (new \DateTime('now'))->format('Y-m-d') . '.' . $type . '.xml';
                $headers
                    ->addHeaderLine('Content-Disposition', $contentDisposition . '; filename="' . $filename . '"');
                break;
        }

        return $response;
    }

    /**
     * Fill each entry according to the site setting.
     */
    protected function appendEntriesStatic(Feed $feed): void
    {
        $api = $this->api();
        $pageMetadata = $this->viewHelpers()->has('pageMetadata') ? $this->viewHelpers()->get('pageMetadata') : null;

        $logUnavailableEntry = function ($url): void {
            $this->logger()->warn(
                'The page "{page_url}" is no longer available and cannot be listed in rss feed.', // @translate
                ['page_url' => $url]
            );
        };

        // Controller names to resource names.
        $resourceNames = [
            'page' => 'site_pages',
            'item' => 'items',
            'item-set' => 'item_sets',
            'media' => 'media',
            'annotation' => 'annotations',
        ];

        // Resource name to controller name.
        $controllerNames = [
            'site_pages' => 'page',
            'items' => 'item',
            'item_sets' => 'item-set',
            'media' => 'media',
            'annotations' => 'annotation',
        ];

        $allowedTags = '<p><a><i><b><em><strong><br>';

        $siteSettings = $this->siteSettings();
        $maxContentLength = (int) $siteSettings->get('feed_entry_length', 0);

        /** @var \Omeka\Api\Representation\SiteRepresentation $currentSite */
        $currentSite = $this->currentSite();
        $currentSiteSlug = $currentSite->slug();

        $urls = $siteSettings->get('feed_entries', []);
        $matches = [];
        foreach ($urls as $url) {
            /**
             * @var \Omeka\Api\Representation\SitePageRepresentation $page
             * @var \Omeka\Api\Representation\AbstractResourceEntityRepresentation $resource
             */
            $page = null;
            $resource = null;

            // See module BlockPlus, block Showcase.
            // This is a resource.
            if (is_numeric($url)) {
                try {
                    $resource = $api->read('resources', ['id' => $url])->getContent();
                } catch (NotFoundException $e) {
                    $logUnavailableEntry($url);
                    continue;
                }
            } else {
                $result = preg_match('~(?:/?s/(?<site>[^/]+)/)?(?<resource_type>page|item-set|item|media|annotation)/(?<resource_id>[^;\?\#]+)~', $url, $matches);
                if (!$result) {
                    $part = mb_strpos($url, '/') === 0 ? mb_substr($url, 1) : $url;
                    $matches = [
                        0 => '/s/' . $currentSiteSlug . '/page/' . $part,
                        'site' => $currentSiteSlug,
                        1 => $currentSiteSlug,
                        'resource_type' => 'page',
                        2 => 'page',
                        'resource_id' => $part,
                        3 => $part,
                    ];
                }
                switch ($matches['resource_type']) {
                    case 'page':
                        if (!$matches['site']) {
                            $site = $currentSite;
                        } elseif ($matches['site'] === $currentSiteSlug) {
                            $site = $currentSite;
                        } else {
                            try {
                                $site = $api->read('sites', ['slug' => $matches['site']])->getContent();
                            } catch (NotFoundException $e) {
                                $logUnavailableEntry($url);
                                continue 2;
                            }
                        }
                        try {
                            $page = $api->read('site_pages', ['site' => $site->id(), 'slug' => $matches['resource_id']])->getContent();
                        } catch (NotFoundException $e) {
                            $logUnavailableEntry($url);
                            continue 2;
                        }
                        break;

                    // Ressources.
                    default:
                        try {
                            $resource = $api->read($resourceNames[$matches['resource_type']], ['id' => $matches['resource_id']])->getContent();
                        } catch (NotFoundException $e) {
                            $logUnavailableEntry($url);
                            continue 2;
                        }
                        break;
                }
            }

            if ($page) {
                /** @var \Omeka\Api\Representation\AbstractEntityRepresentation $record */
                $record = $page;
                $resourceName = 'site_pages';
                $siteSlug = $page->site()->slug();
            } elseif ($resource) {
                $record = $resource;
                $resourceName = $record->resourceName();
                $siteSlug = $currentSiteSlug;
            } else {
                continue;
            }

            $entry = $feed->createEntry();
            $id = $controllerNames[$resourceName] . '-' . $record->id();
            $entry
                ->setId($id)
                ->setLink($record->siteUrl($siteSlug, true))
                ->setDateCreated($record->created())
                ->setDateModified($record->modified())
            ;

            // Specific data of page.
            if ($page) {
                $entry->setTitle($page->title());
                // The full text is not used, because text is not clean with
                // some blocks, and it removes all tags.
                $pageView = new \Laminas\View\Model\ViewModel;
                $pageView
                    ->setVariable('site', $site)
                    ->setVariable('page', $page)
                    ->setVariable('displayNavigation', false)
                    ->setTerminal(true)
                    ->setTemplate('feed/page-show');
                $contentView = clone $pageView;
                $contentView
                    ->setTemplate('feed/page-content')
                    ->setVariable('pageViewModel', $pageView);
                $pageView->addChild($contentView, 'content');
                $content = $this->viewRenderer->render($contentView);

                if ($content) {
                    if ($maxContentLength) {
                        $clean = trim(str_replace('  ', ' ', strip_tags($content)));
                        $content = mb_substr($clean, 0, $maxContentLength) . '…';
                    } else {
                        $content = trim(strip_tags($content, $allowedTags));
                    }
                    $entry->setContent($content);
                }
                if ($pageMetadata) {
                    $summary = $pageMetadata('summary', $page);
                    if ($summary) {
                        $entry->setDescription($summary);
                    }
                }
            }
            // Specific data of resource.
            else {
                $entry->setTitle((string) $resource->displayTitle($id));
                $content = strip_tags($resource->displayDescription(), $allowedTags);
                if ($content) {
                    if ($maxContentLength) {
                        $clean = trim(str_replace('  ', ' ', strip_tags($content)));
                        $content = mb_substr($clean, 0, $maxContentLength) . '…';
                    } else {
                        $content = trim(strip_tags($content, $allowedTags));
                    }
                    $entry->setContent($content);
                }
                $shortDescription = $resource->value('bibo:shortDescription');
                if ($shortDescription) {
                    $entry->setDescription(strip_tags($shortDescription, $allowedTags));
                }
            }

            $feed->addEntry($entry);
        }
    }

    /**
     * Fill each entry according to the search query.
     */
    protected function appendEntriesDynamic(Feed $feed): void
    {
        $controllersToApi = [
            'item' => 'items',
            'resource' => 'resources',
            'item-set' => 'item_sets',
            'media' => 'media',
            'annotation' => 'annotations',
        ];

        // Resource name to controller name.
        $controllerNames = [
            'site_pages' => 'page',
            'items' => 'item',
            'item_sets' => 'item-set',
            'media' => 'media',
            'annotations' => 'annotation',
        ];

        $allowedTags = '<p><a><i><b><em><strong><br>';

        $maxLength = (int) $this->siteSettings()->get('feed_entry_length', 0);

        /** @var \Omeka\Api\Representation\SiteRepresentation $currentSite */
        $api = $this->api();
        $currentSite = $this->currentSite();
        $currentSiteSlug = $currentSite->slug();

        $controller = $this->params()->fromRoute('resource-type', 'item');
        $mainResourceName = $controllersToApi[$controller] ?? 'items';

        // Set most recent first by default and manage pagination.
        $this->setBrowseDefaults('created');
        $query = $this->params()->fromQuery();

        $resources = $api->search($mainResourceName, $query)->getContent();
        foreach ($resources as $resource) {
            // Manage the case where the main resource is "resource".
            $resourceName = $resource->resourceName();

            $entry = $feed->createEntry();
            $id = $controllerNames[$resourceName] . '-' . $resource->id();

            $entry
                ->setId($id)
                ->setLink($resource->siteUrl($currentSiteSlug, true))
                ->setDateCreated($resource->created())
                ->setDateModified($resource->modified())
                ->setTitle((string) $resource->displayTitle($id));

            $content = strip_tags($resource->displayDescription(), $allowedTags);
            if ($content) {
                if ($maxLength) {
                    $clean = trim(str_replace('  ', ' ', strip_tags($content)));
                    $content = mb_substr($clean, 0, $maxLength) . '…';
                } else {
                    $content = trim(strip_tags($content, $allowedTags));
                }
                $entry->setContent($content);
            }
            $shortDescription = $resource->value('bibo:shortDescription');
            if ($shortDescription) {
                $entry->setDescription(strip_tags($shortDescription, $allowedTags));
            }

            $feed->addEntry($entry);
        }
    }
}
