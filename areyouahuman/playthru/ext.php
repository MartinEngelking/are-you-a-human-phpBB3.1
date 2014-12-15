<?php
/**
 *
 * @package Are You A Human PlayThru
 * @version 2.0.0
 * @copyright (c) AreYouAHuman
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, v2
 */
namespace areyouahuman\playthru;

use phpbb\extension\base;

class ext extends base
{
    public function disable_step($old_state)
    {
        global $config;

        // If the AYAH captcha plugin is active, deactivate it to prevent errors
        if($config['captcha_plugin'] === 'areyouahuman.playthru.captcha')
        {
            // Change to the 'nogd' plugin -- it is our safest bet
            $config->set('captcha_plugin', 'core.captcha.plugins.nogd');
            // Disable Captcha on registration
            $config->set('enable_confirm', 0);
            // Disable Captcha on guest posts
            $config->set('enable_post_confirm', 0);

        }
        // Run parent enable step method
        return false;
    }
}