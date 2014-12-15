<?php
/**
 *
 * @package Are You A Human PlayThru
 * @version 2.0.0
 * @copyright (c) AreYouAHuman
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, v2
 */
namespace areyouahuman\playthru\captcha\plugins;

use areyouahuman\playthru\integration\AyahIntegration;
use phpbb\captcha\plugins\captcha_abstract;

class playthru extends captcha_abstract
{
    /** The default AreYouAHuman server to use if one isn't provided in the Admin Control Panel */
    const DEFAULT_SERVER = 'ws.areyouahuman.com';

    /** @var  AyahIntegration */
    public $ayahIntegration;

	/**
	* Constructor
	*/
	public function __construct()
	{
        global $user;

        // Load language strings
        $user->add_lang_ext('areyouahuman/playthru', 'captcha_areyouahuman');

        // Initialize our integration class
        $this->reset();
    }

    /**
     * Restore the captcha to its starting state.
     **/
    public function reset()
    {
        global $config;

        // Reset game status
        $this->solved = false;

        // If no AYAH server has been configured, use the default
        if(empty($config["areyouahuman_server"])) {
            $config->set("areyouahuman_server", self::DEFAULT_SERVER);
        }

        // Initialize AYAH integration with values from the config
        $this->ayahIntegration = new AyahIntegration(array(
            "ayah_web_service_host" => $config['areyouahuman_server'],
            "ayah_publisher_key" => $config['areyouahuman_publisher_key'],
            "ayah_scoring_key" => $config['areyouahuman_scoring_key'],
        ));

        // We don't need call the parent::reset() method here
    }

    /**
     * Is this CAPTCHA plugin available for use?
     *
     * @return bool TRUE if plugin is available for use or FALSE if it isn't configured correctly
     */
    public function is_available()
    {
        global $config;

        // We expect the following config fields to be set
        $required_fields = array(
            'areyouahuman_server',
            'areyouahuman_publisher_key',
            'areyouahuman_scoring_key'
        );

        // Return false if any one of the required fields is empty
        foreach ($required_fields as $field) {
            if (empty($config[$field])) {
                return false;
            }
        }

        // The plugin is available for use
        return true;
    }


    /**
     * Was the PlayThru completed successfully?
     *
     * @return bool TRUE if PlayThru was completed or FALSE if it was not.
     */
    public function is_solved()
    {
        return (bool) $this->solved;
    }

    /**
     * Determines whether or not to display the acp config page. AreYouAHuman requires it because the Admin
     * needs to provide publisher and scoring keys.
     **/
    public function has_config()
    {
        return true;
    }

    /**
     * Returns the name of this plugin.
     *
     * @return string Name of the CAPTCHA plugin.
     */
    static function get_name()
	{
        global $user;

        // Load language file
        $user->add_lang_ext('areyouahuman/playthru', 'captcha_areyouahuman');

        return 'CAPTCHA_AREYOUAHUMAN';
	}

    /**
     * Sets template variables for displaying the AYAH Admin Control Panel page.
     *
     * @param $id
     * @param $module
     */
    public function acp_page($id, &$module)
	{
        global $template, $config, $user, $request;

        // Form fields and their labels
        $captcha_vars = array(
            'areyouahuman_publisher_key' => 'AREYOUAHUMAN_PUBLISHER_KEY',
            'areyouahuman_scoring_key' => 'AREYOUAHUMAN_SCORING_KEY',
            'areyouahuman_server' => 'AREYOUAHUMAN_SERVER'
        );

        // Use our custom template
        $module->tpl_name = '@areyouahuman_playthru/captcha_areyouahuman_acp';
        // Page title (from the core language files)
        $module->page_title = 'ACP_VC_SETTINGS';

        // Set the form key
		$form_key = 'acp_captcha';
		add_form_key($form_key);

        // Check whether the admin submitted the form via either Preview or Submit
        $submit = $request->variable('submit', '');
        $preview = $request->variable('preview', '');

        // If the admin submitted the form via Preview or Submit and if the form key matches, save the changes.

        // We don't distinguish between previewing and submitting here because the PlayThru does not have
        // any settings to preview, aside from the keys and server (in which case it either works or does not)
        if(($preview || $submit) && check_form_key($form_key))
        {
            // Grab a list of form fields we are expecting to see
            $captcha_vars = array_keys($captcha_vars);

            // Save each field's value in the configuration
            foreach ($captcha_vars as $captcha_var)
            {
                $value = $request->variable($captcha_var, '');
                $config->set($captcha_var, $value);
            }

            // If the admin used the Submit button, log the event and show success message.
            if($submit)
            {
                // We knowingly use the deprecated add_log() instead of (global)$phpbb_log->add() because
                // add_log() takes care of setting things like the user ID, user IP, etc. for us.
                // In order to stop using this deprecated function, we'd basically need to re-implement a large chunk
                // of it in this class, which is pointless.
                add_log('admin', 'LOG_CONFIG_VISUAL');

                // Show confirmation
                trigger_error($user->lang['CONFIG_UPDATED'] .
                    adm_back_link($module->u_action));
            }
        }
        // If admin submitted the form without the correct form key, show an error.
        else if ($submit || $preview)
        {
            trigger_error($user->lang['FORM_INVALID'] . adm_back_link($module->u_action));
        }
        // Otherwise pre-fill any existing configuration values
        else
        {
            foreach ($captcha_vars as $captcha_var => $template_var)
            {
                $var = $request->variable($captcha_var, '');
                if (!$var)
                {
                    $var = ((isset($config[$captcha_var])) ? $config[$captcha_var] : '');
                }
                $template->assign_var($template_var, $var);
            }
        }

        // Set template variables for display
        $template->assign_vars( array(
            'AREYOUAHUMAN_PUBLISHER_KEY' 	=> $config["areyouahuman_publisher_key"],
            'AREYOUAHUMAN_SCORING_KEY' 	=> $config["areyouahuman_scoring_key"],
            'AREYOUAHUMAN_SERVER'  		=> $config["areyouahuman_server"],
            'CAPTCHA_PREVIEW' 		=> $this->get_demo_template($id),
            'CAPTCHA_NAME'		=> $this->get_service_name(),
            'U_ACTION'			=> $module->u_action,
        ));
	}

    /**
     * Generates and returns the HTML code we will use to display the PlayThru on the page.
     * This code will be displayed within one of our custom templates on either the front-end or in the ACP.
     *
     * @return string The HTML code used to display the PlayThru
     */
    public function load_code()
    {
        // If PlayThru is already completed, start over
        if ($this->solved)
        {
            $this->reset();
        }
        // Generate and return the code.
        $this->code = $this->ayahIntegration->generateHTML();
        return $this->code;
    }

    /**
     * This method assigns template variables that are required to display the PlayThru and returns the template name
     * to be used for this task. This template will be included in the Demo section of the Captcha admin screen.
     *
     * If the plugin isn't configured correctly (missing keys or server), an error message is displayed in place of
     * the PlayThru.
     *
     * @return string Fully qualified template name for displaying the PlayThru on the front-end.
     */
    public function get_template()
    {
        global $template, $user;

        if(!$this->is_available())
        {
            $code = $user->lang['AREYOUAHUMAN_ERROR_UNAVAILABLE'];
        }
        else
        {
            $code = $this->load_code();
        }

        // Assign template variables for display
        $template->assign_vars( array(
            'CODE'				=> $code,
            'S_CONFIRM_CODE'	=> true, // required for max login attempts
        ));

        // Return the fully-qualified template name for inclusion in the front-end template
        return '@areyouahuman_playthru/captcha_areyouahuman.html';

    }

    /**
     * This method assigns template variables that are required to display the PlayThru and returns the template name
     * to be used for this task. This template will then be included in the front-end template where needed.
     *
     * If the plugin isn't configured correctly (missing keys or server), an error message is displayed in place of
     * the PlayThru.
     *
     * @param $id
     * @return string Fully qualified template name for displaying the PlayThru on the front-end.
     */
	function get_demo_template($id)
	{
        global $template, $user, $config;

        // If the plugin is not available for use due to incomplete config,
        // display a warning here instead of showing them a blank demo.
        // This also prevents an unnecessary call to AYAH services.
        if(!$this->is_available())
        {
            $demo = $user->lang['AREYOUAHUMAN_NO_KEY'];
        }
        else
        {
            $demo = $this->load_code();
        }

        // Assign the code (or the error message) to the template for display
        $template->assign_vars( array(
            'DEMO'  => $demo,
        ));

        // Return the fully-qualified template name for inclusion in the 'demo' section of the ACP.
        return '@areyouahuman_playthru/captcha_areyouahuman_acp_demo.html';
    }

    /**
     * Scores the PlayThru and returns FALSE if successful. Otherwise returns an error message.
     *
     * @return bool|string FALSE if PlayThru was completed successfully or an error string if the PlayThru wasn't
     *                     completed successfully.
     */
    function validate()
    {
        global $user;

        // If PlayThru has already been solved, return FALSE (which indicates success in this method)
        if ($this->solved)
        {
            return false;
        }

        // Score the PlayThru. Did the user pass?
        if ($this->ayahIntegration->scoreResult()) {

            // If successful, mark as solved and return FALSE.
            $this->solved = true;
            return false;
        } else {
            // Otherwise we'll implore the user to try again.
            return $user->lang['AREYOUAHUMAN_NOT_A_HUMAN'];
        }
    }

    /**
     * The below methods are not needed for PlayThru functionality; they are overrided here so we don't inherit
     * unnecessary functionality from captcha_abstract
     */
    public function new_attempt() {}
    public function execute_demo() {}
    public function execute() {}
    public function generate_code() {}
    public function regenerate_code() {}
    public function check_code() {}
    public function garbage_collect($type) {}
    public function init($type) {}
    public function uninstall() {}
    public function install() {}
    public function get_hidden_fields()
    {
        return array();
    }

    /**
     * This method is implemented because required by the parent class but isn't used here.
     */
    public function get_generator_class()
    {
        throw new \Exception('No generator class given.');
    }
}

