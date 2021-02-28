<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Settings_Page Class
 *
 * @class Settings_Page
 * @version 1.0.1
 */
// phpcs:disable
class Settings_Page extends \WC_Settings_Page
{
    public $settings = [];

    /**
     * Constructor.
     *
     * @param array $settings
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->id = 'products_wizard';
        $this->label = L10N::r('Products Wizard');

        parent::__construct();

        add_action('woocommerce_sections_' . $this->id, array($this, 'reset'));
    }

    /**
     * Get sections.
     *
     * @return array
     */
    public function get_sections()
    {
        $sections = array(
            '' => L10N::r('General', 'woocommerce'),
            'custom_styles' => L10N::r('Custom styles')
        );

        return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
    }

    /**
     * Get settings array.
     *
     * @param string $current_section Current section.
     *
     * @return array
     */
    public function get_settings($current_section = '')
    {
        $settings = [];

        foreach ($this->settings as $key => $setting) {
            if (isset($setting['section']) && $setting['section'] == $current_section) {
                $settings[$key] = $setting;
            }
        }

        return apply_filters('woocommerce_get_settings_' . $this->id, $settings);
    }

    /**
     * Output the settings.
     */
    public function output()
    {
        global $current_section;

        $sections = $this->get_sections();
        $settings = $this->get_settings($current_section);

        woocommerce_admin_fields([
            'section_title' => [
                'name' => isset($sections[$current_section]) ? $sections[$current_section] : 'No title',
                'type' => 'title',
                'desc' => '',
                'id' => 'wcpw_settings_section_title'
            ]
        ]);

        woocommerce_admin_fields($settings);

        if ($current_section == 'custom_styles') {
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label><?php L10N::e('Reset to defaults'); ?></label>
                </th>
                <td class="forminp">
                    <button class="button button-secondary"
                        name="woocommerce_products_wizard_settings_reset"
                        data-component="wcpw-settings-reset"><?php L10N::e('Reset'); ?></button>
                </td>
            </tr>
            <?php
        }

        woocommerce_admin_fields([
            'section_end' => [
                'type' => 'sectionend',
                'id' => 'wcpw_settings_section_end'
            ]
        ]);
    }

    /**
     * Reset settings.
     */
    public function reset()
    {
        if (!isset($_REQUEST['woocommerce_products_wizard_settings_reset'])) {
            return;
        }

        global $current_section;

        foreach ($this->get_settings($current_section) as $setting) {
            delete_option($setting['key']);
        }
    }

    /**
     * Save settings.
     */
    public function save()
    {
        global $current_section;

        woocommerce_update_options($this->get_settings($current_section));

        if ($current_section == 'custom_styles') {
            try {
                Core::compileCustomStylesFile();
                update_option('woocommerce_products_wizard_styles_compiled_time', time());
            } catch (\Exception $exception) {
                exit(L10N::r('SCSS error') . ': ' . $exception->getMessage());
            }
        }
    }
}
// phpcs:enable
