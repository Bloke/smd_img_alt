<?php
// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'smd_img_alt';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1.0';
$plugin['author'] = 'Stef Dawson';
$plugin['author_uri'] = 'https://www.stefdawson.com/';
$plugin['description'] = 'Faster image alt templating';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public              : only on the public side of the website (default)
// 1 = public+admin        : on both the public and admin side
// 2 = library             : only when include_plugin() or require_plugin() is called
// 3 = admin               : only on the admin side (no AJAX)
// 4 = admin+ajax          : only on the admin side (AJAX supported)
// 5 = public+admin+ajax   : on both the public and admin side (AJAX supported)
$plugin['type'] = '4';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = 0;

// Plugin 'textpack' is optional. It provides i18n strings to be used in conjunction with gTxt().
// Syntax:
// ## arbitrary comment
// #@event
// #@language ISO-LANGUAGE-CODE
// abc_string_name => Localized String

$plugin['textpack'] = <<<EOT
#@owner smd_img_alt
#@language en, en-gb, en-us
#@image

EOT;

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
if (txpinterface === 'admin') {
    global $step;

    if ($step === 'image_edit') {
        new smd_img_alt();
    }
}

/**
 * 
 */
class smd_img_alt
{
    protected $img_id = 0;
    protected $artFlds = array();

    public function __construct()
    {
        $this->img_id = (int)gps('id');
        $this->artFlds = safe_row('*', 'textpattern', 'find_IN_set('.$this->img_id.', Image)');

        register_callback(array($this, 'alt_dropdown'), 'image_ui', 'inputlabel.image_alt_text');
    }

    public function alt_dropdown($evt, $stp, $data)
    {
        // Modify the recordset so the keys are wrapped in {}.
        foreach ($this->artFlds as $key => $value) {
            $newkey = '{' . $key . '}';
            $this->artFlds[$newkey] = $value;
            unset($this->artFlds[$key]);
        }

        $skin = get_pref('skin_editing', 'default');
        $form = fetch_form('smd_img_alt', $skin);

        if (!$form) {
            return $data; 
        }

        $form = strtr($form, $this->artFlds);
        $opts = explode(n, $form);

        $dropdown = selectInput('smd_img_alt', $opts, '', true, '', 'smd_img_alt');

        return $data.n.$dropdown . script_js(<<<EOJS
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('smd_img_alt').addEventListener('change', function() {
        let smd_img_alt_target = document.getElementById('image_alt_text');
console.log(this);
        smd_img_alt_target.value = this.options[this.selectedIndex].text;
    });
}, false);
EOJS
        );
    }
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---

h1. smd_img_alt

Adds a dropdown of potted alt tags for rapid selection of alternate text. The alt tags are defined in a form and can have replacements applied to them from the article that the image is used in.

h2. Usage

After installation, create a form (of any type) called @smd_img_alt@. In this form, write your boilerplate alt text strings, one per line.

When you visit the Image Edit panel, the dropdown appears below the alt text box. Choosing an entry from the select list will immidiately replace the contents in the alt text box with the selected entry for you to amend if you wish, before saving. Note the changes are not committed to the database until you Save them.

h2. Replacement strings

If you assign an image to an article, you can also use replacement variables in the alt text. The replacements are surrounded by curly braces, and take the names of article fields in the database. For example, if you had a serial number in custom field number 1 and product code in custom field 2, you could add a row to your form like this:

bc. Product: {custom_2} close-up (Serial number {custom_1})

Bear in mind:

# The replacements are made immediately, as the panel loads, so if there is a lot of text in your replacement strings, it can make the dropdown entries very long!
# If the image is associated with more than one article, only the first one will be used to grab replacements.

# --- END PLUGIN HELP ---
-->
<?php
}
?>