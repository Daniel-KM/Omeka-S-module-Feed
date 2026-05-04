<?php declare(strict_types=1);

namespace FeedTest\Controller;

require_once __DIR__ . '/FeedControllerTestCase.php';

/**
 * @group controller
 */
class FeedControllerTest extends FeedControllerTestCase
{
    protected $item;

    public function setUp(): void
    {
        parent::setUp();

        $response = $this->api()->create('items', [
            'dcterms:title' => [
                [
                    'type' => 'literal',
                    'property_id' => 1,
                    '@value' => 'Test Item for Feed',
                ],
            ],
        ]);
        $this->item = $response->getContent();

        // Set feed entries for static feed.
        $siteSettings = $this->getServiceLocator()
            ->get('Omeka\Settings\Site');
        $siteSettings->setTargetId($this->site->id());
        $siteSettings->set('feed_entries', [
            (string) $this->item->id(),
        ]);
        $siteSettings->set('feed_disposition', 'inline');
    }

    public function tearDown(): void
    {
        $this->login('admin@example.com', 'root');

        if ($this->item) {
            try {
                $this->api()->delete('items', $this->item->id());
            } catch (\Exception $e) {
            }
            $this->item = null;
        }

        parent::tearDown();
    }

    public function testStaticFeedRss(): void
    {
        $this->dispatch('/s/test/feed');
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderContains(
            'Content-type',
            'application/rss+xml; charset=UTF-8'
        );
    }

    public function testStaticFeedAtom(): void
    {
        $this->dispatch('/s/test/feed/atom');
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderContains(
            'Content-type',
            'application/atom+xml; charset=UTF-8'
        );
    }

    public function testStaticFeedContainsItem(): void
    {
        $this->dispatch('/s/test/feed');
        $body = $this->getResponse()->getContent();
        $this->assertStringContainsString(
            'Test Item for Feed',
            $body
        );
    }

    public function testDynamicFeedRss(): void
    {
        $this->dispatch('/s/test/rss');
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderContains(
            'Content-type',
            'application/rss+xml; charset=UTF-8'
        );
    }

    public function testDynamicFeedAtom(): void
    {
        $this->dispatch('/s/test/atom');
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderContains(
            'Content-type',
            'application/atom+xml; charset=UTF-8'
        );
    }

    public function testDynamicFeedContainsItem(): void
    {
        $this->dispatch('/s/test/rss');
        $body = $this->getResponse()->getContent();
        $this->assertStringContainsString(
            'Test Item for Feed',
            $body
        );
    }

    public function testDynamicFeedItemSet(): void
    {
        $this->dispatch('/s/test/rss/item-set');
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderContains(
            'Content-type',
            'application/rss+xml; charset=UTF-8'
        );
    }

    public function testStaticFeedXmlMediaType(): void
    {
        $siteSettings = $this->getServiceLocator()
            ->get('Omeka\Settings\Site');
        $siteSettings->setTargetId($this->site->id());
        $siteSettings->set('feed_media_type', 'xml');

        $this->dispatch('/s/test/feed');
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderContains(
            'Content-type',
            'text/xml; charset=UTF-8'
        );
    }

    public function testFeedIsValidXml(): void
    {
        $this->dispatch('/s/test/feed');
        $body = $this->getResponse()->getContent();
        $xml = @simplexml_load_string($body);
        $this->assertNotFalse($xml, 'Feed output should be valid XML');
    }

    public function testFeedWithoutAuth(): void
    {
        // Feed should be accessible without authentication.
        $this->dispatchUnauthenticated('/s/test/feed');
        $this->assertResponseStatusCode(200);
    }
}
