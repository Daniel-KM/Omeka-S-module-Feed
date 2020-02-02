<?php
namespace Feed\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;

class SiteSettingsFieldset extends Fieldset
{
    /**
     * @var string
     */
    protected $label = 'Feed'; // @translate

    public function init()
    {
        $this
            ->add([
                'name' => 'feed_urls',
                'type' => Element\Textarea::class,
                'options' => [
                    'label' => 'Feed urls', // @translate
                ],
                'attributes' => [
                    'id' => 'feed_urls',
                    'placeholder' => '/s/my-site/page/article-one
/s/my-site/page/article-two
',
                ],
            ])
        ;
    }
}
