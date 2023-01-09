<?php declare(strict_types=1);

namespace Feed\Form;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Omeka\Form\Element as OmekaElement;

class SiteSettingsFieldset extends Fieldset
{
    /**
     * @var string
     */
    protected $label = 'Feed (rss/atom)'; // @translate

    protected $elementGroups = [
        'feed' => 'Feed (rss/atom)', // @translate
    ];

    public function init(): void
    {
        $this
            ->setAttribute('id', 'feed')
            ->setOption('element_groups', $this->elementGroups)
            ->add([
                'name' => 'feed_logo',
                'type' => OmekaElement\Asset::class,
                'options' => [
                    'element_group' => 'feed',
                    'label' => 'Specific Image or logo for the channel (default to site thumbnail)', // @translate
                ],
                'attributes' => [
                    'id' => 'feed_logo',
                ],
            ])
            ->add([
                'name' => 'feed_entry_length',
                'type' => Element\Number::class,
                'options' => [
                    'element_group' => 'feed',
                    'label' => 'Max number of characters of an entry', // @translate
                    'info' => '0 means all text for pages and resource descriptions.', // @translate
                ],
                'attributes' => [
                    'id' => 'feed_entry_length',
                    'min' => 0,
                    'required' => false,
                ],
            ])
            ->add([
                'name' => 'feed_entries',
                'type' => OmekaElement\ArrayTextarea::class,
                'options' => [
                    'element_group' => 'feed',
                    'label' => 'Entries for the static feed', // @translate
                    'as_key_value' => false,
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
                'name' => 'feed_media_type',
                'type' => Element\Radio::class,
                'options' => [
                    'element_group' => 'feed',
                    'label' => 'Media type', // @translate
                    'value_options' => [
                        'standard' => 'application/atom+xml and application/rss+xml (standard)', // @translate
                        'xml' => 'application/xml (most compatible)', // @translate
                    ],
                ],
                'attributes' => [
                    'id' => 'feed_media_type',
                    'value' => 'standard',
                ],
            ])
            ->add([
                'name' => 'feed_disposition',
                'type' => Element\Radio::class,
                'options' => [
                    'element_group' => 'feed',
                    'label' => 'Content disposition', // @translate
                    'value_options' => [
                        'attachment' => 'Attachment to download', // @translate
                        'inline' => 'Inline to display', // @translate
                        'undefined' => 'Undefined', // @translate
                    ],
                ],
                'attributes' => [
                    'id' => 'feed_disposition',
                    'value' => 'attachment',
                ],
            ])
        ;
    }
}
