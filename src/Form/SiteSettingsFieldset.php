<?php
namespace Feed\Form;

use Omeka\Form\Element\Asset;
use Zend\Form\Element;
use Zend\Form\Fieldset;

class SiteSettingsFieldset extends Fieldset
{
    /**
     * @var string
     */
    protected $label = 'Feed (rss/atom)'; // @translate

    public function init()
    {
        $this
            ->add([
                'name' => 'feed_logo',
                'type' => Asset::class,
                'options' => [
                    'label' => 'Image or logo for the channel', // @translate
                ],
                'attributes' => [
                    'id' => 'feed_logo',
                ],
            ])
            ->add([
                'name' => 'feed_entries',
                'type' => Element\Textarea::class,
                'options' => [
                    'label' => 'Feed entries', // @translate
                ],
                'attributes' => [
                    'id' => 'feed_entries',
                    'rows' => 12,
                    'placeholder' => 'article-three
/s/my-site/page/article-one
2
item/4
page/article-two',
                ],
            ])
            ->add([
                'name' => 'feed_entry_length',
                'type' => Element\Number::class,
                'options' => [
                    'label' => 'Max entry length', // @translate
                    'info' => '0 means all text for pages and resource descriptions.' // @translate
                ],
                'attributes' => [
                    'id' => 'feed_entry_length',
                    'min' => 0,
                    'required' => false,
                ],
            ])
            ->add([
                'name' => 'feed_media_type',
                'type' => Element\Radio::class,
                'options' => [
                    'label' => 'Media type', // @translate
                    'value_options' => [
                        'xml' => 'application/xml (most compatible)', // @translate
                        'standard' => 'application/atom+xml and application/rss+xml (standard)', // @translate
                    ],
                ],
                'attributes' => [
                    'id' => 'feed_media_type',
                    'value' => 'xml',
                ],
            ])
        ;
    }
}
