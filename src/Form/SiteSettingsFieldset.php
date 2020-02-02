<?php
namespace Feed\Form;

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
        ;
    }
}
