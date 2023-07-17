<?php

namespace Bigfork\SilverstripeUserFormsTidying\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

class EditableCountryDropdownFieldExtension extends Extension
{
    public function updateCMSFields(FieldList $fields): void
    {
        /** @var DropdownField $default */
        $default = $fields->dataFieldByName('Default');
        $default->setEmptyString(_t(__CLASS__.'.CHOOSECOUNTRY', 'Select a country...'));

        $fields->removeByName(['UseEmptyString', 'EmptyString']);
        $fields->addFieldsToTab(
            'Root.Main',
            [
                FieldGroup::create(
                    'Empty value',
                    CheckboxField::create(
                        'UseEmptyString',
                        _t(
                            __CLASS__.'.EMPTYSTRING_CHECKBOX_DESCRIPTION',
                            'Display custom text when no country has been selected'
                        )
                    )
                )->setTitle(_t(__CLASS__.'.EMPTYSTRING_CHECKBOX_LABEL', 'Empty value')),
                $emptyString = TextField::create('EmptyString', _t(__CLASS__.'.EMPTYSTRING_LABEL', 'Empty value text'))
                    ->setDescription(
                        _t(__CLASS__.'.EMPTYSTRING_DESCRIPTION', 'Shown when no country has been selected')
                    )
            ],
            'Advanced'
        );
        $emptyString->displayIf('UseEmptyString')->isChecked();
    }
}
