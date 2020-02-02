<?php
namespace Feed\Controller;

use Zend\Feed\Writer\Feed;
use Omeka\Api\Exception\NotFoundException;
use Omeka\Stdlib\Message;
use Zend\Mvc\Controller\AbstractActionController;

class FeedController extends AbstractActionController
{
    /**
     * @var string
     */
    protected $moduleVersion;

    public function __construct($moduleVersion)
    {
        $this->moduleVersion = $moduleVersion;
    }

    public function indexAction()
    {
        $type = 'atom';

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
            ->setGenerator('Omeka S module Feed', $this->moduleVersion, 'https://github.com/Daniel-KM/Omeka-S-module-Feed')
            ->setDateModified(time())
        ;

        $description = $site->summary();
        if ($description) {
            $feed
                ->setDescription($description);
        }

        $locale = $siteSettings->get('locale');
        if ($locale) {
            $feed
                ->setLanguage($locale);
        }

        $this->appendEntries($feed);

        $content = $feed->export($type);

        $response = $this->getResponse();
        $response->setContent($content);
        /** @var \Zend\Http\Headers $headers */
        $response->getHeaders()
            // TODO Manage content type requests (atom/rss).
            // Note: normally, application/rss+xml is the good one, but text/xml
            // may be more compatible.
            // ->addHeaderLine('Content-type: ' . 'text/xml')
            ->addHeaderLine('Content-type: ' . 'application/' . $type . '+xml')
            ->addHeaderLine('Content-length: ' . strlen($content))
            ->addHeaderLine('Pragma: public');
        return $response;
    }

    /**
     * Fill each entry according to the site setting.
     *
     * @param Feed $feed
     */
    protected function appendEntries(Feed $feed)
    {
        $api = $this->api();
        $pageMetadata = $this->viewHelpers()->has('pageMetadata') ? $this->viewHelpers()->get('pageMetadata') : null;

        $logUnavailableEntry = function($url) {
            $this->logger()->warn(new Message(
                'The page "%s" is no longer available and cannot be listed in rss feed.', // @translate
                $url
            ));
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

        /** @var \Omeka\Api\Representation\SiteRepresentation $currentSite */
        $currentSite = $this->currentSite();
        $currentSiteSlug = $currentSite->slug();

        $urls = $this->siteSettings()->get('feed_entries', []);
        $matches = [];
        foreach ($urls as $url) {
            /**
             * @var \Omeka\Api\Representation\SitePageRepresentation $page
             * @var \Omeka\Api\Representation\AbstractResourceEntityRepresentation $resource
             */
            $page = null;
            $resource = null;

            // This is a resource.
            if (is_numeric($url)) {
                try {
                    $resource = $api->read('resources', ['id' => $url])->getContent();
                } catch (NotFoundException $e) {
                    $logUnavailableEntry($url);
                    continue;
                }
            } else {
                $result = preg_match('~(?:/?s/([^/]+)/)?(page|item|item-set|media|annotation)/([^;\?\#]+)~', $url, $matches);
                if (!$result) {
                    $part = mb_strpos($url, '/') === 0 ? mb_substr($url, 1) : $url;
                    $matches = [
                        '/s/' . $currentSiteSlug . '/page/' . $part,
                        $currentSiteSlug,
                        'page',
                        $part,
                    ];
                }
                switch ($matches[2]) {
                    case 'page':
                        if ($matches[1] === $currentSiteSlug) {
                            $site = $currentSite;
                        } elseif (!$matches[1]) {
                            $logUnavailableEntry($url);
                            continue 2;
                        } else {
                            $site = $this->searchOne('sites', ['slug' => $matches[1]])->getContent();
                            if (!$site) {
                                $logUnavailableEntry($url);
                                continue 2;
                            }
                        }
                        // SearchOne cannot be used for pages.
                        try {
                            $page = $api->read('site_pages', ['site' => $site->id(), 'slug' => $matches[3]])->getContent();
                        } catch (NotFoundException $e) {
                            $logUnavailableEntry($url);
                            continue 2;
                        }
                        break;

                    // Ressources.
                    default:
                        try {
                            $resource = $api->read($resourceNames[$matches[2]], ['id' => $matches[3]])->getContent();
                        } catch (NotFoundException $e) {
                            $logUnavailableEntry($url);
                            continue 2;
                        }
                        break;
                }
            }

            /** @var \Omeka\Api\Representation\AbstractEntityRepresentation $record */
            if ($page) {
                $record = $page;
                $resourceName = 'site_pages';
            } elseif( $resource) {
                $record = $resource;
                $resourceName = $record->resourceName();
            } else {
                continue;
            }

            $entry = $feed->createEntry();
            $id = $controllerNames[$resourceName] . '-' . $record->id();
            $entry
                ->setId($id)
                ->setLink($record->siteUrl($site->slug(), true))
                ->setDateCreated($record->created())
                ->setDateModified($record->modified())
            ;

            // Specific data of page.
            if ($page) {
                // $content;
                $entry->setTitle($page->title());
                $content = '';
                if ($content) {
                    $content->setContent(strip_tags($content, $allowedTags));
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
}
