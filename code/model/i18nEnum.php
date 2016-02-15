<?php
/**
 * Enable i18n translation for Enum.
 * See http://www.balbuss.com/translating-an-enum-dropdown/
 *
 * @package pageimages
 * @subpackage model
 * @author      [SYBEHA] (http://sybeha.de)
 * @copyright   [SYBEHA]
 * @license     http://www.gnu.org/licenses/gpl-3.0.html
 */
class i18nEnum extends Enum
{

    /*
     * returns Enum values as a simple array
     */
    function getEnum()
    {
        return $this->enum;
    }

    /*
     * Override the enumValues method and return a translated array
     * The translations always default to the original Enum values
     *
     * $namespace: the namespace to use for the translations. Defaults
     * to 'Enum', but you should use the ClassName of the class
     * that owns the Enum.
     *
     * SomeObject->enumValues($this->ClassName, true);
     */
    function enumValues($namespace = 'Enum', $hasEmpty = false)
    {
        $translatedOptions = array();

        $options = ($hasEmpty) ? array_merge(array(
            '' => ''
        ), $this->enum) : $this->enum;

        if (! empty($options)) {
            foreach ($options as $value) {
                $translatedOptions[$value] = _t("$namespace.$value", $value);
            }
        }

        return $translatedOptions;
    }
}
// EOF
