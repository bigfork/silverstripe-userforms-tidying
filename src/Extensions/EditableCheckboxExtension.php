<?php

namespace Bigfork\SilverstripeUserFormsTidying\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;

class EditableCheckboxExtension extends Extension
{
    public function updateCMSFields(FieldList $fields): void
    {
        $fields->replaceField(
            'CheckedDefault',
            FieldGroup::create(
                'Default value',
                CheckboxField::create('CheckedDefault', 'This checkbox should be checked by default')
            )
        );
    }
}
