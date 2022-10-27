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
        $default->setEmptyString('Select a country...');

        $fields->removeByName(['UseEmptyString', 'EmptyString']);
        $fields->addFieldsToTab(
            'Root.Main',
            [
                FieldGroup::create(
                    'Empty value',
                    CheckboxField::create('UseEmptyString', 'Display custom text when no country has been selected')
                ),
                $emptyString = TextField::create('EmptyString', 'Empty value text')
                    ->setDescription('Shown when no country has been selected')
            ],
            'Advanced'
        );
        $emptyString->displayIf('UseEmptyString')->isChecked();
    }
}
