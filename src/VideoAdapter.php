<?php
/**
 * Video adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace spacecatninja\imagerxvideoadapter;

use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;

use spacecatninja\imagerxvideoadapter\adapters\Video;
use spacecatninja\imagerxvideoadapter\models\Settings;
use spacecatninja\imagerxvideoadapter\services\VideoLoaderService;
use spacecatninja\imagerxvideoadapter\variables\VideoAdapterVariable;

use yii\base\Event;

/**
 * @author    SpaceCatNinja
 * @package   ImagerXVideoAdapter
 * @since     1.0.0
 *
 * @property  VideoLoaderService $loader
 *
 */
class VideoAdapter extends Plugin
{
    public function init(): void
    {
        parent::init();

        // Register services
        $this->setComponents([
            'loader' => VideoLoaderService::class,
        ]);

        // Register our variables
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT,
            static function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('videoadapter', VideoAdapterVariable::class);
            }
        );
        
        // Register adapter in Imager X
        Event::on(\spacecatninja\imagerx\ImagerX::class,
            \spacecatninja\imagerx\ImagerX::EVENT_REGISTER_ADAPTERS,
            function (\spacecatninja\imagerx\events\RegisterAdaptersEvent $event) {
                /* @var Settings $settings */
                $settings = $this->getSettings();
                
                foreach ($settings->addFileFormats as $format) {
                    $event->adapters[$format] = Video::class;
                }
            }
        );
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Settings
    {
        return new Settings();
    }

}
